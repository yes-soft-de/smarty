

import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/programs/model/program_details_model/program_details_model.dart';
import 'package:smarty/programs/ui/widget/card_videos_name/card_videos_name.dart';
import 'package:smarty/shared/project_colors/project_colors.dart';
@provide
class VideosTabPage extends StatefulWidget {
  final List<Video> videos;
  VideosTabPage(this.videos);

  @override
  _VideosTabPageState createState() => _VideosTabPageState();
}

class _VideosTabPageState extends State<VideosTabPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
     body:  Container(
           padding: EdgeInsetsDirectional.fromSTEB(20, 10, 20, 10),
           child: ListView.builder(
               itemCount: widget.videos.length,
               padding: EdgeInsetsDirectional.fromSTEB(0,50 ,0, 0),
               itemBuilder: (BuildContext context, int index) {
                 return    CardVideosName(name: widget.videos[index].name,commentNumber: "7Comment",loveNumber: "10Likes",);
               }),

         ),


    );
  }
}
