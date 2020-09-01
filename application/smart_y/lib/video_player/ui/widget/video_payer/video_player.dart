import 'package:flick_video_player/flick_video_player.dart';
import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';

class SmartyVideoPlayer extends StatefulWidget {
  final String videoUrl;

  SmartyVideoPlayer(this.videoUrl);

  @override
  State<StatefulWidget> createState() => _SmartyVideoPlayerState(videoUrl);
}

class _SmartyVideoPlayerState extends State<VideoPlayer> {
  String videoUrl;
  FlickManager flickManager;

  _SmartyVideoPlayerState(this.videoUrl);

  VideoPlayerController _playerController;

  @override
  void initState() {
    super.initState();
    flickManager = FlickManager(
      videoPlayerController: VideoPlayerController.network(videoUrl),
    );
  }

  @override
  Widget build(BuildContext context) {
    return FlickVideoPlayer(flickManager: flickManager);
  }
}
