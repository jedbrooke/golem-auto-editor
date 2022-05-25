#!/usr/bin/python3
import os
import time
import subprocess
import json
import threading
import shlex
import hashlib
import time
import sys

class JOB_STATUS():
    WAITING = "waiting"
    STARTED = "started"
    FINISHED = "finished"
    FAILED = "failed"

PYTHON3 = "/usr/bin/python3"
REQUESTOR = "/var/www/backend/requestor.py"
OUTPUT_PATH = "/var/www/backend/finished"
APPKEY_PATH = "/var/www/backend/appkey.txt"
APPKEY=""
REQUESTOR_ENV=os.environ.copy()



JOBS_DIR = "/var/www/backend/queue/jobs"
JOBS = []
MAX_CONCURRENT_JOBS = 4

def json_loadp(path):
    with open(path,"r") as fp:
        return json.load(fp)

def write_job(job):
    with open(job["job_path"],"w") as fp:
        json.dump(job,fp)

def run_job(job):
    output_path = os.path.join(OUTPUT_PATH,f"{job['token']}.{job['video_ext']}")
    cmd = f"{PYTHON3} {REQUESTOR} --auto-editor-args \"{job['args']}\" --output {output_path} -y {job['video_path']}"
    subprocess.run(shlex.split(cmd),stdout=sys.stdout,stderr=sys.stderr,env=REQUESTOR_ENV)
    job = json_loadp(job["job_path"])
    job["finished_time"] = int(time.time())
    job["status"] = JOB_STATUS.FINISHED
    job["output_path"] = output_path
    write_job(job)


def load_appkey():
    global APPKEY
    with open(APPKEY_PATH,"r") as fh:
        APPKEY = fh.read().strip()


def init():
    global REQUESTOR_ENV
    load_appkey()
    REQUESTOR_ENV["YAGNA_APPKEY"] = APPKEY


def main():
    init()
    running = True
    global JOBS
    while running:
        if len(JOBS) < MAX_CONCURRENT_JOBS:
            # scan jobs dir
            jobs_list = [json_loadp(j.path) for j in os.scandir(JOBS_DIR)]
            for job in jobs_list:
                if job["status"] == JOB_STATUS.WAITING:
                    job["status"] = JOB_STATUS.STARTED
                    write_job(job)
                    threading.Thread(target=run_job,args=(job,)).start()
                    JOBS.append(job)

        JOBS = [job for job in JOBS if job["status"] in [JOB_STATUS.WAITING, JOB_STATUS.STARTED]]
        time.sleep(1)
        # running = False


if __name__ == '__main__':
    main()