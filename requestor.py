# adapted from https://github.com/golemfactory/yapapi/blob/master/examples/blender/blender.py
from datetime import datetime, timedelta
import sys
from typing import List
import os
import shutil

from yapapi import (Golem, Task, WorkContext)
import yapapi
from yapapi.engine import NoPaymentAccountError
from yapapi.payload import vm
from yapapi.rest.activity import BatchTimeoutError
from yapapi.log import enable_default_logger, log_event_repr, log_summary

import tempfile
import subprocess

import asyncio

TEMPDIR = tempfile.mkdtemp()

# old hash=497f08b035b71f9afbdca1f9430dd4c044b4e6cfe8dfa185e203de58

async def main(slices: List[str], auto_editor_args:str,subnet_tag="devnet-beta.2",payment_driver="zksync",payment_network="rinkeby"):
    package = await vm.repo(
        image_hash="e0c9fc00d3a786ab849908cc75f091fe5026853591f80122e027d123",
        min_mem_gib=4.0,
        min_storage_gib=2.0,
        min_cpu_threads=4
    )
    async def worker(ctx: WorkContext, tasks):
        async for task in tasks:
            basename = os.path.basename(task.data)
            input_dest = f"/golem/input/{basename}"
            output_dest = f"/golem/output/{basename}"

            ctx.send_file(task.data,input_dest)
            ctx.run(f"auto-editor",input_dest,auto_editor_args,"--debug","--no-open","--no_progress","--ouptut_file",output_dest)
            ctx.download_file(output_dest,os.path.join(TEMPDIR,"output",basename))
            
            try:
                yield ctx.commit(timeout=timedelta(minutes=10))
                task.accept_result(result=output_dest)
            except BatchTimeoutError:
                print(f"Task {task} timed out on {ctx.provider_name}, time: {task.running_time}",file=sys.stderr)
                raise 
            


    init_overhead = 3
    min_timeout, max_timeout = 6, 30
    timeout = timedelta(minutes=max(min(init_overhead + len(slices) * 2, max_timeout), min_timeout))

    async with Golem(
        budget=10.0,
        subnet_tag=subnet_tag,
        driver=payment_driver,
        network=payment_network,
        event_consumer=log_summary(log_event_repr)
    ) as golem:
        num_tasks = 0
        start_time = datetime.now()

        completed_tasks = golem.execute_tasks(
            worker,
            [Task(data=s) for s in slices],
            payload=package,
            max_workers=min(len(slices),10),
            timeout=timeout
        )
        async for task in completed_tasks:
            num_tasks += 1
            print(f"Task computed: {task}, result: {task.result}, time: {task.running_time}")

        print(f"{num_tasks} tasks computed, total time: {datetime.now() - start_time}")
            

def die():
    # at the end
    # delete tempdir
    shutil.rmtree(TEMPDIR)
    exit(0)

if __name__ == '__main__':

    enable_default_logger(
        log_file="/home/golem/output.log"
    )

    # split file into slices
    input_path = sys.argv[1]
    name = os.path.basename(input_path)
    name_no_ext = ".".join(name.split(".")[:-1])
    ext = name.split('.')[-1]

    # get length
    length = float(subprocess.run(f"ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {input_path}".split(" "),stdout=subprocess.PIPE).stdout.decode())

    # slice_length in seconds
    slice_length = 60

    slices = [input_path]


    if length > slice_length:
        temp_input_dir = os.path.join(TEMPDIR,"input")  
        # split into slices
        os.mkdir(temp_input_dir)

        slice_dest = os.path.join(TEMPDIR,"input",f"{name_no_ext}_%05d.{ext}")
        subprocess.run(f"ffmpeg -v error -i {input_path} -c copy -map 0 -segment_time {slice_length} -f segment -reset_timestamps 1 {slice_dest}".split(" "),stdout=sys.stdout)
        print(TEMPDIR)
        slices = [os.path.join(temp_input_dir,f) for f in os.listdir(temp_input_dir)]
    
    print(slices)
    os.mkdir(os.path.join(TEMPDIR,"output"))

    auto_editor_args = " ".join(sys.argv[2:] + ["--no-open"])

    loop = asyncio.get_event_loop()
    task = loop.create_task(
        main(
            slices=slices,
            auto_editor_args=auto_editor_args
        )
    )
    try:
        loop.run_until_complete(task)
    except NoPaymentAccountError as e:
        print(f"No payment account initialized for driver `{e.required_driver}` "
            f"and network `{e.required_network}`.\n\n")
        die()
    except KeyboardInterrupt:
        print("Shutting down gracefully, please wait a short while "
            "or press Ctrl+C to exit immediately...")
        task.cancel()
        try:
            loop.run_until_complete(task)
            print("Shutdown complete")
        except (asyncio.CancelledError, KeyboardInterrupt):
            pass
        die()


    # recombine finished video
    cat_list = os.path.join(TEMPDIR,"cat.txt")
    with open(cat_list,'w') as fh:
        fh.write("\n".join(f for f in os.listdir(os.path.join(TEMPDIR,"output"))))
    output_dest = os.path.join(os.path.dirname(input_path),f"{name_no_ext}_ALTERED.{ext}")
    subprocess.run(f"ffmpeg -f concat -safe 0 -i {cat_list} -c copy {output_dest}")
        
   