import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../image_icon/image_icon.dart';

class EventCardWidget extends StatelessWidget {
  final String image;
  final Color color;
  final String title;
  final String description;

  EventCardWidget(
      {@required this.image,
      @required this.color,
      @required this.title,
      @required this.description})
      : assert(image != null &&
            color != null &&
            title != null &&
            description != null);

  @override
  Widget build(BuildContext context) {
    return Container(
      child: Card(
        color: color,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(0)),
        ),
        child: Row(
          children: <Widget>[
            Image(
              width: MediaQuery.of(context).size.width * 0.3,
              height: MediaQuery.of(context).size.height * 0.25,
              image: AssetImage(image),
            ),
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                Text(
                  title,
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 10,
                  ),
                ),
                Text(
                  description,
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 8,
                  ),
                ),
              ],
            ),
            IconButton(
              icon: ImageAsIconWidget(
                img: 'assets/Group 14.png',
                height: 20,
                width: 20,
              ),
              onPressed: (){},
            ),
          ],
        ),
      ),
    );
  }
}
