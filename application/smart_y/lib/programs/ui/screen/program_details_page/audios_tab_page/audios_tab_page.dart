

import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
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
       padding: EdgeInsetsDirectional.fromSTEB(20, 10, 20, 10),
       child: ListView.builder(
           itemCount: widget.audios.length,
           padding: EdgeInsetsDirectional.fromSTEB(0,50 ,0, 0),
           itemBuilder: (BuildContext context, int index) {
             return     CardAudioName(
               name: widget.audios[index].instructorName,
               avatar:widget.audios[index].instructorAvatar,
               commentNumber: "7Comment",
               loveNumber: "10Likes",) ;
           }),

     ),


    );
  }
}
