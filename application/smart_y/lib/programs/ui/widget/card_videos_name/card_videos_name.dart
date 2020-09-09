import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:smarty/shared/ui/widget/circle_image/circle_iamge.dart';
import 'package:smarty/video_player/ui/widget/video_payer/video_player.dart';

class CardVideosName extends StatelessWidget {
  final String name;
  final String commentNumber;
  final String loveNumber;
  final String instructorName;
  final String avatar;
  final String videoUrl;

  CardVideosName(
      {@required this.avatar,
      @required this.videoUrl,
      @required this.instructorName,
      @required this.name,
      @required this.commentNumber,
      @required this.loveNumber});

  @override
  Widget build(BuildContext context) {
    return Card(
      color: Colors.white,
      clipBehavior: Clip.hardEdge,
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      child: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            SizedBox(
              height: 10,
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.start,
              children: <Widget>[
                MyCircularImage(
                  50,
                  50,
                  linkImg: avatar,
                ),
                SizedBox(
                  width: 4,
                ),
                Flexible(
                  child: Text(
                    instructorName,
                    style: TextStyle(color: Colors.black, fontSize: 14),
                  ),
                )
              ],
            ),
            SizedBox(
              height: 10,
            ),
            Row(
              children: <Widget>[
                Expanded(
                  child: Container(
                      height: 150,
                      child: Card(
                          clipBehavior: Clip.hardEdge,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(20)),
                          child: SmartyVideoPlayer(videoUrl))),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
