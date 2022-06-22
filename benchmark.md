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
| golem testnet (10mbit upload) | 5m44.056s | 5m35.058s |
| golem testnet (1gbit upload) | 3m58.282s | 4m13.386s |
| golem mainnet | | |

A more real world example.

3 hr interview, vp9, 1368 kbit/s, 1080p, output in h264
```auto-editor example_long.mp4 --has-vfr no --video_codec libx264 [--video_speed 1.25]```
| System | normal test | speed up test |
| -- | --- | -- |
| local system | 104m2.400s | 103m28.684s |
| golem testnet (10mbit upload) | 44m25.005s | 39m46.655s |
| golem testnet (1gbit updload) | | 24m21.278s |
| golem mainnet | | |

The local system is a typical home desktop, with 4 core i6 4590 @ 3.5Ghz. Most of the time is spent uploading the file, so results will depend on your inernet speed
