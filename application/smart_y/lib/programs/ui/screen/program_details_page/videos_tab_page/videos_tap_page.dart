

import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/programs/ui/widget/card_videos_name/card_videos_name.dart';
import 'package:smarty/shared/project_colors/project_colors.dart';
@provide
class VideosTabPage extends StatefulWidget {
  @override
  _VideosTabPageState createState() => _VideosTabPageState();
}

class _VideosTabPageState extends State<VideosTabPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
     body:SafeArea(
       child: SingleChildScrollView(
         child: Column(
           children: <Widget>[

             Padding(
               padding: const EdgeInsets.symmetric(horizontal: 30),
               child: FlatButton(
                 padding: EdgeInsets.fromLTRB(0, 15, 0, 15),
                 onPressed: (){
//                   Navigator.pushNamed(context, HomeModule.ROUTE_AUDIO);

                 }
                 ,
                 color: ProjectColors.Color1,
                 child: Row(
                   mainAxisAlignment: MainAxisAlignment.center,
                   children: <Widget>[
                     Text(
                       // This needs to translate, but Later, the dependency is installed
                       'Next',
                       style: TextStyle(
                         color: Colors.white,
                       ),
                     ),
                     Icon(
                       Icons.arrow_forward,
                       color: Colors.white,
                     )
                   ],
                 ),
               ),
             ),
             CardVideosName(name: "name",commentNumber: "7Comment",loveNumber: "10Likes",),
             CardVideosName(name: "name",commentNumber: "7Comment",loveNumber: "10Likes",),
             CardVideosName(name: "name",commentNumber: "7Comment",loveNumber: "10Likes",),
             CardVideosName(name: "name",commentNumber: "7Comment",loveNumber: "10Likes",),
           ],
         ),
       ),
     ) ,
    );
  }
}
