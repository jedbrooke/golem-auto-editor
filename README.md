# golem-auto-editor
Run Auto-Editor on the Golem Network.

## Running
To run you first need to yave yagna installed and the yagna daemon running. Instructions on how to do that [here](https://handbook.golem.network/requestor-tutorials/flash-tutorial-of-requestor-development/run-first-task-on-golem).

Running the program looks like: 

```python3 requstor.py INPUT_FILE [-l, --length <seconds>] [--auto-editor-args="[ARGS]"] [--output ]```

`INPUT_FILE` is the file you want processed. Although auto-editor supports multiple files as input, this program currently does not. Additionally, any args regarding output to in the `auto-editor-args` field will be ignored, as those will be overwritten by the program.

Be careful when specifying video codecs to auto-editor, as only those that ship by default with ffmpeg on debian will be present. Most common codecs like `h26x` and `vpx` are supported. Full list can be found [here](codecs.txt).

for more info run ```python3 requestor.py --help``` 


## What is Auto-Editor?
[Auto-Editor](https://auto-editor.com/) is a command line tool to automate some simple video editiing tasks, such as cutting out silence and dead-space. Great for students who want to watch lecture videos in less time, or content creators editing a long interview.

## Why do we need Golem?
you can absolutely run auto-editor on your own computer, but the video encoding and other processing that it performs can be very intensive, so distributing it over many computers can help speed it up greatly. Or, it can make processing of long videos feasible on lower spec machines, potentially even a Chromebook. 



## What is the Golem Network?
Golem Network is a decentralized distributed super-computing platform. Read more about it at their website: [https://golem.network](https://golem.network).


