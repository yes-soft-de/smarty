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

  _SmartyVideoPlayerState(this.videoUrl);

  VideoPlayerController _playerController;

  @override
  void initState() {
    super.initState();
    _playerController = VideoPlayerController.network(videoUrl)
      ..initialize().then((_) {
        setState(() {});
      });
  }

  @override
  Widget build(BuildContext context) {
    return Center(
      child: _playerController.value.initialized
          ? AspectRatio(
              aspectRatio: _playerController.value.aspectRatio,
              child: VideoPlayer(_playerController),
            )
          : Container(),
    );
  }
}
