import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

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
      margin: EdgeInsetsDirectional.fromSTEB(10, 10, 10, 10),
      child: Card(
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
            Image(
              height: 40,
              width: 40,
              image: AssetImage('assets/Play.png',
              ),
            )


          ],
        ),
      ),
    );
  }


}
