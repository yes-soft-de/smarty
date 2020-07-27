import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'image_icon.dart';

class ArticleCard extends StatelessWidget {
  final Icon icon;
  final String name;
  final Color color;
  final int duration;
  ArticleCard({@required this.icon,@required this.name,@required this.color,@required this.duration})
  :assert(icon != null && name != null && color != null && duration != null);
  @override
  Widget build(BuildContext context) {
    return Container(
      width: MediaQuery.of(context).size.width*0.45,
      child: Card(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(0),
        ),
        color: color,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            SizedBox(height: MediaQuery.of(context).size.height*0.05,),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                icon,
                 Text(
                   name,
                   style: TextStyle(
                     fontSize: 10,
                     color: Colors.white,
                   ),
                 )
              ],
            ),
            Text(
              'What is it ?',
              style: TextStyle(
                fontSize: 12,
                color: Colors.white,
              ),
            ),
            Text(
              '$duration min',
              style: TextStyle(
                fontSize: 12,
                color: Colors.white,
              ),
            ),

            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                IconButton(
                  icon: ImageAsIcon(
                    img:'assets/Play33.png',
                    height: 30.0,
                    width: 30.5,
                  ),
                  onPressed: (){},
                ),
                Text(
                  'Ok',
                  style: TextStyle(
                    color: Colors.white,
                  ),
                )
              ],
            )
          ],
        ),
      ),
    );
  }
}
