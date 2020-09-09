

import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:smarty/audio_player/service/audio_payer_service.dart';
import 'package:smarty/shared/ui/widget/circle_image/circle_iamge.dart';

class CardAudioName extends StatefulWidget {
  String name;
  String avatar;
  String commentNumber;
  String loveNumber;
  String track;
  final AudioPlayerService playerService;

  CardAudioName(
      { this.name,
        this.avatar,
        this.track,
        this.commentNumber,
        this.loveNumber,
        this.playerService
      });

  @override
  _CardAudioNameState createState() => _CardAudioNameState();
}

class _CardAudioNameState extends State<CardAudioName> {
  @override
  Widget build(BuildContext context) {
    return Container(
      child: Card(
        color: Colors.white,
        clipBehavior: Clip.hardEdge,
        elevation: 4,
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10)
        ),
        child: Padding(
          padding: const EdgeInsets.all(8.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: <Widget>[
              SizedBox(height: 5,),
              Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: MyCircularImage(50,50,linkImg: widget.avatar,),
                  ),
                  SizedBox(width: 4,),
                  Expanded(child:
                     Text(widget.name,style: TextStyle(color: Colors.black,fontSize: 14 ),),),
                  GestureDetector(
                    child: Container(
                      decoration: BoxDecoration(
                          color: Color(0x8fB9F6CA),
                          borderRadius: BorderRadius.all(Radius.circular(32))),
                      child: Padding(
                        padding: const EdgeInsets.all(8.0),
                        child: AudioPlayerService().isPlaying(MediaItem(widget.name, widget.track))
                            ? Icon(Icons.pause)
                            : Icon(Icons.play_arrow),
                      ),
                    ),
                    onTap: () {
                      AudioPlayerService().isPlaying(MediaItem(widget.name, widget.track))
                          ? widget.playerService.pause()
                          : widget.playerService.play(MediaItem(widget.name, widget.track));
                      setState(() {});
                    },
                    onLongPress: () {
                      widget.playerService.stop();
                      setState(() {});
                    },
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
