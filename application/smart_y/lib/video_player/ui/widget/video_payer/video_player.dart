import 'package:flick_video_player/flick_video_player.dart';
import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';

class SmartyVideoPlayer extends StatefulWidget {
  final String videoUrl;

  SmartyVideoPlayer(this.videoUrl);

  @override
  State<StatefulWidget> createState() => _SmartyVideoPlayerState(videoUrl);
}

class _SmartyVideoPlayerState extends State<SmartyVideoPlayer> {
  String videoUrl;
  FlickManager flickManager;

  _SmartyVideoPlayerState(this.videoUrl);

  @override
  void initState() {
    super.initState();
    flickManager = FlickManager(
      autoInitialize: true,
      autoPlay: false,
      videoPlayerController: VideoPlayerController.network(videoUrl),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        FlickVideoPlayer(flickManager: flickManager),
        Positioned.fill(
            child: GestureDetector(
              onTap: () {
                flickManager.flickControlManager.pause();
              },
              child: Container(
          color: Color(0x01FFFFFF),
        ),
            ))
      ],
    );
  }
}
