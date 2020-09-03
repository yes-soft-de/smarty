import 'package:flutter/material.dart';


class NotificationRow extends StatelessWidget {
  final String senderImage;
  final String sender;
  final String content;
  final TimeOfDay time;

  NotificationRow({this.content,this.sender,this.senderImage,this.time});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
      Row(
      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
      children: [
        Container(
          child: Image(
            width: 50,
            height: 50,
            image: AssetImage(senderImage),
          ),
        ),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              sender,
              style: TextStyle(
                fontSize: 13
              ),
            ),
            Text(
                content,
              style: TextStyle(
                  fontSize: 11
              ),
            ),
          ],
        ),
        Column(
          mainAxisAlignment: MainAxisAlignment.start,
          children: [
            Text(
              '${time.hour}:${time.minute}',
            ),
            Text(
                ''
            ),
          ],
        ),
      ],
    ),
        Divider(),
      ],
    );
  }
}
