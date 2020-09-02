import 'package:flutter/material.dart';

class ChatBubbleWidget extends StatefulWidget {
  final bool showImage;
  final String message;
  final String senderName;
  final DateTime sentDate;
  final bool me;

  ChatBubbleWidget({
    Key key,
    @required this.message,
    @required this.sentDate,
    @required this.me,
    @required this.senderName,
    this.showImage,
  });

  @override
  State<StatefulWidget> createState() => ChatBubbleWidgetState();
}

class ChatBubbleWidgetState extends State<ChatBubbleWidget> {
  bool focused = false;

  @override
  Widget build(BuildContext context) {
    return Container(
      alignment: widget.me ? Alignment.centerLeft : Alignment.centerRight,
      child: Padding(
        padding: EdgeInsets.all(8.0),
        child: Container(
          width: 240,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.all(Radius.circular(8)),
            color: widget.me ? Color(0xff5E239D) : Colors.black12,
          ),
          child: Padding(
            padding: const EdgeInsets.all(8.0),
            child: Text(
              widget.message ?? 'Empty Text?!!',
              style: TextStyle(color: widget.me ? Colors.white : Colors.black),
            ),
          ),
        ),
      ),
    );
  }
}
