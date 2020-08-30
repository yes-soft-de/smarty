

import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/programs/ui/widget/card_audio_name/card_audio_name.dart';

@provide
class AudiosTabPage extends StatefulWidget {
  @override
  _AudiosTabPageState createState() => _AudiosTabPageState();
}

class _AudiosTabPageState extends State<AudiosTabPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
     body:SafeArea(
       child: SingleChildScrollView(
         child: Column(
           children: <Widget>[

             CardAudioName(name: "name",commentNumber: "7Comment",loveNumber: "10Likes",),
             CardAudioName(name: "name",commentNumber: "7Comment",loveNumber: "10Likes",),
             CardAudioName(name: "name",commentNumber: "7Comment",loveNumber: "10Likes",),
             CardAudioName(name: "name",commentNumber: "7Comment",loveNumber: "10Likes",),

           ],
         ),
       ),
     ) ,
    );
  }
}
