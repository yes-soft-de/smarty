import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/programs/model/program_details_model/program_details_model.dart';
import 'package:smarty/programs/ui/widget/card_videos_name/card_videos_name.dart';

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
      body: Container(
        padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 10),
        child: ListView.builder(
            itemCount: widget.videos.length,
            itemBuilder: (BuildContext context, int index) {
              return Container(
                margin: EdgeInsetsDirectional.fromSTEB(0, 0, 0, 25),
                child: CardVideosName(
                  videoUrl: widget.videos[index].videoUrl,
                  name: widget.videos[index].name,
                  avatar: widget.videos[index].instructorAvatar,
                  instructorName: widget.videos[index].instructorName,
                  commentNumber: "7 Comment",
                  loveNumber: "10 Likes",
                ),
              );
            }),
      ),
    );
  }
}
