import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

import '../image_icon/image_icon.dart';

class VideoCard extends StatelessWidget {
  final Color color;
  final Color backgroundColor;
  final String image;
  final bool isPaid;
  final String text;
  VideoCard({@required this.color,@required this.backgroundColor,@required this.image,@required this.isPaid,@required this.text})
      :assert(color !=null &&backgroundColor !=null &&image !=null &&isPaid !=null );
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
            SizedBox(width: 10.0,),
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                Text(text,
                  style: TextStyle(
                      color: color
                  ),),
                Text(text,
                  style: TextStyle(
                      color: color
                  ),),
              ],
            ),
            IconButton(
              icon: ImageAsIcon(
                img:'assets/Play.png',
                height: 32.0,
                width: 32.0,
              ),
              onPressed: () {
                // do something
              },
            )

          ],
        ),
      ),
    );
  }
}
