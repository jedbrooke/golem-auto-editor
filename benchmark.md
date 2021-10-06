How fast is it?

equivalent auto-editor commands for each test:

normal test:

```auto-editor example_long.mp4 --has-vfr no```

speedup test:

```auto-editor example_long.mp4 --has-vfr no --video_speed 1.25``` 


`example_long.mp4` is the `example.mp4` file from the auto-editor repo looped 100 times, ~1hr long. it's mostly silent portions so it's on the faster side compared to real world videos.


| System | normal test | speed up test|  
| --- | --- | -- | 
| local system| 3m27.897s | 6m1.555s |
| golem testnet | 5m44.056s | 5m35.058s |
| golem mainnet | | |

A more real world example.

3 hr interview, vp9, 1368 kbit/s, 1080p, output in h264
```auto-editor example_long.mp4 --has-vfr no --video_codec libx264 [--video_speed 1.25]```
| System | normal test | speed up test |
| -- | --- | -- |
| local system | 104m2.400s | |
| golem testnet | 44m25.005s | 39m46.655s |
| golem mainnet | | |

The local system is a typical home desktop, with 4 core i6 4590 @ 3.5Ghz. My upload speed is around 10mbit, and most of the time for golem is spent uploading the file, so if you have faster internet your gains could be even better
