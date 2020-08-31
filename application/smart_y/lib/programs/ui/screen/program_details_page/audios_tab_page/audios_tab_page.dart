

import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/audio_player/service/audio_payer_service.dart';
import 'package:smarty/programs/model/program_details_model/program_details_model.dart';
import 'package:smarty/programs/ui/widget/card_audio_name/card_audio_name.dart';

@provide
class AudiosTabPage extends StatefulWidget {
  final List<Audio> audios;

  AudiosTabPage(this.audios);
  @override
  _AudiosTabPageState createState() => _AudiosTabPageState();
}

class _AudiosTabPageState extends State<AudiosTabPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
     body: Container(
       padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 10),
       child: ListView.builder(
           itemCount: widget.audios.length,
           itemBuilder: (BuildContext context, int index) {
             return     Container(
               padding: EdgeInsetsDirectional.fromSTEB(0, 0, 0, 20),
               child: CardAudioName(
                 name: widget.audios[index].instructorName,
                 track:widget.audios[index].audioUrl ,
                 avatar:widget.audios[index].instructorAvatar,
                 playerService: new AudioPlayerService() ,
                 commentNumber: "7Comment",
                 loveNumber: "10Likes",),
             ) ;
           }),

     ),


    );
  }
}
