# adapted from https://github.com/golemfactory/yapapi/blob/master/examples/blender/blender.py
from datetime import datetime, timedelta
import sys
from typing import List
import os
import shutil

from yapapi import (Golem, Task, WorkContext)
from yapapi.engine import NoPaymentAccountError
from yapapi.payload import vm
from yapapi.rest.activity import BatchTimeoutError
from yapapi.log import enable_default_logger, log_event_repr, log_summary

import tempfile
import subprocess
import asyncio
import argparse
import logging as log


TEMPDIR = ""

# old hash=497f08b035b71f9afbdca1f9430dd4c044b4e6cfe8dfa185e203de58
async def golem_main(slices: List[str], auto_editor_args:str,budget=10,subnet_tag="devnet-beta.2",payment_driver="zksync",payment_network="rinkeby"):
    print("##################")
    print(subnet_tag,payment_driver,payment_network)
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
            ctx.run(f"/usr/local/bin/auto-editor",input_dest,auto_editor_args,"--output_file",output_dest)
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
        budget=budget,
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
            
def parse_args():
    parser = argparse.ArgumentParser(description="run auto-editor on golem network")
    # TODO support multiple input files
    # can work on all slices at the same time
    parser.add_argument('input_file', metavar='INPUT_FILE', type=str, help="the file to be processed")
    parser.add_argument('--auto-editor-args',dest="auto_editor_args", type=str, help="args for auto-editor. for more info on auto-editor see https://auto-editor.com/ for best results use the long names for arguments")
    parser.add_argument('--output',dest='output',type=str, help="path to output file")
    parser.add_argument('--tempdir',dest='tempdir',type=str, help=f"location for temporary files. Default is {os.path.dirname(TEMPDIR)}")
    parser.add_argument('-y',action="store_true", help="Answer 'Y' for any interactive prompts")
    parser.add_argument('-v','--verbose',dest="verbose", action="store_true", help="Enable debug level output")
    parser.add_argument('-q','--quiet',dest="quiet", action="store_true", help="No info level output, errors only")

    golem_options = parser.add_argument_group("Golem Options","Settings for the golem network configuration. Default is to run on testnet")
    golem_options.add_argument("--bugdet",default=10,type=float, help="bugdet in GLM or tGML for the task")
    golem_options.add_argument("--mainnet",action="store_true",help="equivalent to --subnet_tag=mainnet --payment_driver=zksync --payment_network=mainnet")
    golem_options.add_argument("--subnet_tag",default="devnet-beta.2",help="golem subnet [default: devnet-beta.2] [possible values: devnet-beta.2, mainnet]")
    golem_options.add_argument("--payment_driver",default="zksync", help="golem payment driver [default: zksync] [possible values: zksync, erc20]")
    golem_options.add_argument("--payment_network",default="rinkeby", help="golem payment network [default: rinkeby] [possible values: mainnet, rinkeby")
    golem_options.set_defaults(budget=10,subnet_tag="devnet-beta.2",payment_driver="zksync",payment_network="rinkeby")

    return parser.parse_args()

def die():
    # at the end
    # delete tempdir
    shutil.rmtree(TEMPDIR)
    exit(0)


def allow_overwrite(output_path, force):
    if force:
        log.info(f"Overwriting '{output_path}'")
        return True
    else:
        return input(f"'{output_path}' already exists, do you wish to overwrite? [y/N]: ").lower() == 'y'

def check_exists(output_path, force):
    if os.path.exists(output_path):
        if not allow_overwrite(output_path, force):
            log.info("Aborting")
            exit(0)


def main():
    args = parse_args()


    input_path = args.input_file
    name = os.path.basename(input_path)
    name_no_ext = ".".join(name.split(".")[:-1])
    ext = name.split('.')[-1]

    output_name = f"{name_no_ext}_ALTERED.{ext}"
    output_path = ""

    loglevel = log.INFO
    if args.verbose:
        loglevel = log.DEBUG
    if args.quiet:
        loglevel = log.ERROR
    
    log.basicConfig(format='[%(levelname)s]:%(message)s',level=loglevel)

    if args.output:
        if os.path.isdir(args.output) or args.output.endswith('/'): # if the path provided is a dir it will make a file in that dir
            output_path = os.path.join(args.output,output_name)
            check_exists(output_path, args.y)
        elif os.path.exists(args.output): #if it exists and is not a dir, ask user if they wish to overwrite
            if allow_overwrite(args.output, args.y):
                output_path = args.output
            else:
                log.info("Aborting")
                exit(0)
        else:
            output_path = args.output
            check_exists(output_path, args.y)
    else:
        output_path = os.path.join(os.path.dirname(input_path),output_name)
        check_exists(output_path, args.y)

    try:
        with open(output_path,'a'):
            os.utime(output_path)
    except FileNotFoundError:
        log.error(f"Output Path '{os.path.dirname(output_path)}' does not exist")
        log.info("Aborting")
        exit(0)
    log.info(f"Writing output to '{output_path}'")

    global TEMPDIR
    if args.tempdir:
        TEMPDIR = args.tempdir
    else:
        TEMPDIR = tempfile.mkdtemp()
    log.debug(f"Using {TEMPDIR} as temp directory")

    # check auto-editor args
    # 
    auto_editor_args = args.auto_editor_args
    for arg in ["--quiet","--no-open","--no_progress"]:
        if not arg in auto_editor_args:
            auto_editor_args += f" {arg}"
    if "--output" in auto_editor_args:
        log.error("Please do not specify an output in auto-editor")
        exit(0)

    # get length
    # TODO: use pyav for better compatibility, not sure if this works on windows
    length = float(subprocess.run(f"ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {input_path}".split(" "),stdout=subprocess.PIPE).stdout.decode())

    # slice_length in seconds
    slice_length = 600

    slices = [input_path]

    if length > slice_length:
        # make equal slices close enough to target length
        num_slices = length // slice_length
        slice_length = int(length // num_slices) + 1
        log.debug(f"Using slice length {slice_length}s")

        temp_input_dir = os.path.join(TEMPDIR,"input")  
        # split into slices
        os.mkdir(temp_input_dir)

        slice_dest = os.path.join(TEMPDIR,"input",f"{name_no_ext}_%05d.{ext}")
        
        # TODO: use pyav for better compatibility, not sure if this works on windows
        subprocess.run(f"ffmpeg -v error -i \"{input_path}\" -c copy -map 0 -segment_time {slice_length} -f segment -reset_timestamps 1 {slice_dest}".split(" "),stdout=sys.stdout)
        print(TEMPDIR)
        slices = [os.path.join(temp_input_dir,f) for f in os.listdir(temp_input_dir)]
    
    print(slices)
    os.mkdir(os.path.join(TEMPDIR,"output"))
    
    enable_default_logger(log_file="/home/golem/output.log")

    loop = asyncio.get_event_loop()
    task = loop.create_task(
        golem_main(
            slices=slices,
            auto_editor_args=auto_editor_args,
            budget=args.budget,
            subnet_tag=args.subnet_tag,
            payment_driver=args.payment_driver,
            payment_network=args.payment_network
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
        fh.write("\n".join(f"file '{f}'" for f in sorted(os.listdir(os.path.join(TEMPDIR,"output")))))
    subprocess.run(f"ffmpeg -f concat -safe 0 -i {cat_list} -c copy \"{output_path}\"".split(" "))

    die()


if __name__ == '__main__':
    main()
   