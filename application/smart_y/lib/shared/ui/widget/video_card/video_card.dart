import 'package:flutter/material.dart';
import 'package:smarty/audio_player/service/audio_payer_service.dart';

class VideoCardWidget extends StatefulWidget {
  final Color color;
  final Color backgroundColor;
  final String image;
  final bool isPaid;
  final String text;
  final String track;

  VideoCardWidget(
      {@required this.color,
      @required this.backgroundColor,
      @required this.image,
      @required this.isPaid,
      @required this.text,
      @required this.track});

  @override
  State<StatefulWidget> createState() => _VideoCardWidgetState(
        this.color,
        this.backgroundColor,
        this.image,
        this.isPaid,
        this.text,
        this.track,
      );
}

class _VideoCardWidgetState extends State<VideoCardWidget> {
  final Color color;
  final Color backgroundColor;
  final String image;
  final bool isPaid;
  final String text;
  final String track;

  bool playing = false;
  AudioPlayerService audioService = AudioPlayerService();

  _VideoCardWidgetState(this.color, this.backgroundColor, this.image,
      this.isPaid, this.text, this.track);

  @override
  Widget build(BuildContext context) {
    AudioPlayerService.playerEventStream.listen((event) {
      print('Got Audio Event: ' + event);
      if (event == widget.track) {
        playing = true;
      } else {
        playing = false;
      }
      setState(() {});
    });

    return Container(
      height: 100,
      child: Card(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(0),
        ),
        color: backgroundColor,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: <Widget>[
            Image(
              height: 50.0,
              width: 50.0,
              image: AssetImage(image),
            ),
            SizedBox(
              width: 10.0,
            ),
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                Text(
                  text,
                  style: TextStyle(color: color),
                ),
                Text(
                  text,
                  style: TextStyle(color: color),
                ),
              ],
            ),
            GestureDetector(
              child: Container(
                height: 32,
                width: 32,
                decoration: BoxDecoration(
                    color: Color(0x8fB9F6CA),
                    borderRadius: BorderRadius.all(Radius.circular(32))),
                child: playing ? Icon(Icons.pause) : Icon(Icons.play_arrow),
              ),
              onTap: () {
                if (playing) {
                  print('Video Player Pausing!');
                  audioService.pause();
                } else {
                  print("Video Player Playing");
                  audioService.play(MediaItem(widget.text, widget.track));
                }
              },
            )
          ],
        ),
      ),
    );
  }
}
