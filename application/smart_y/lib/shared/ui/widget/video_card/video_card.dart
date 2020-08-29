import 'package:flutter/material.dart';
import 'package:smarty/audio_player/service/audio_payer_service.dart';

class VideoCardWidget extends StatefulWidget {
  final Color color;
  final Color backgroundColor;
  final String image;
  final bool isPaid;
  final String text;
  final AudioPlayerService playerService;
  final String track;

  VideoCardWidget(
      {@required this.color,
      @required this.backgroundColor,
      @required this.image,
      @required this.isPaid,
      @required this.playerService,
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

  _VideoCardWidgetState(this.color, this.backgroundColor, this.image,
      this.isPaid, this.text, this.track);

  @override
  Widget build(BuildContext context) {
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
                child: widget.playerService.isPlaying(track)
                    ? Icon(Icons.pause)
                    : Icon(Icons.play_arrow),
              ),
              onTap: () {
                widget.playerService.isPlaying(track)
                    ? widget.playerService.pause()
                    : widget.playerService.play(track);
                setState(() {});
              },
              onLongPress: () {
                widget.playerService.stop();
                setState(() {});
              },
            )
          ],
        ),
      ),
    );
  }
}
