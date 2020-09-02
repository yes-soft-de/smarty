import 'dart:developer';

import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/module_chat/bloc/chat_page/chat_page.bloc.dart';
import 'package:smarty/module_chat/model/chat/chat_model.dart';
import 'package:smarty/module_chat/ui/widget/chat_bubble/chat_bubble.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';

@provide
class ChatPage extends StatefulWidget {
  final ChatPageBloc _chatPageBloc;
  final SharedPreferencesHelper _preferencesHelper;
  ChatPage(this._chatPageBloc, this._preferencesHelper);
  @override
  State<StatefulWidget> createState() => ChatPageState();
}

class ChatPageState extends State<ChatPage> {
  List<ChatModel> _chatMessagesList = [];
  int currentState = ChatPageBloc.STATUS_CODE_INIT;

  final TextEditingController _msgController = TextEditingController();
  List<ChatBubbleWidget> chatsMessagesWidgets = [];

  String chatRoomId;

  @override
  Widget build(BuildContext context) {
    chatRoomId = ModalRoute.of(context).settings.arguments.toString();

    if (currentState == ChatPageBloc.STATUS_CODE_INIT) {
      print('Chat Room: ' + chatRoomId);
      widget._chatPageBloc.getMessages(chatRoomId);
    }

    widget._chatPageBloc.chatBlocStream.listen((event) {
      currentState = event.first;
      print('Got Event');
      if (event.first == ChatPageBloc.STATUS_CODE_GOT_DATA) {
        _chatMessagesList = event.last;
        if (chatsMessagesWidgets.length == _chatMessagesList.length) {
        } else {
          buildMessagesList(_chatMessagesList).then((value) {
            if (this.mounted) setState(() {});
          });
        }
      }
    });

    return Scaffold(
      backgroundColor: Colors.white,
      body: Column(
        // direction: Axis.vertical,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: <Widget>[
          Expanded(
            child: chatsMessagesWidgets != null
                ? ListView(
                    children: chatsMessagesWidgets,
                    reverse: false,
                  )
                : Center(
                    child: Text('Loading'),
                  ),
          ),
          Flex(
            direction: Axis.horizontal,
            children: <Widget>[
              Flexible(
                flex: 5,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
                  child: TextField(
                    decoration: InputDecoration(hintText: 'Start Writing'),
                    controller: _msgController,
                  ),
                ),
              ),
              Flexible(
                flex: 1,
                child: GestureDetector(
                  onTap: () {
                    sendMessage();
                  },
                  child: Container(
                    height: 48,
                    width: 48,
                    decoration: BoxDecoration(
                        color: Color(0xff5E239D),
                        borderRadius: BorderRadius.all(Radius.circular(90))),
                    child: Icon(Icons.send, color: Colors.white),
                  ),
                ),
              )
            ],
          ),
        ],
      ),
    );
  }

  Future<void> buildMessagesList(List<ChatModel> chatList) async {
    var newMessagesList = <ChatBubbleWidget>[];
    String uid = await widget._preferencesHelper.getUserId();
    chatList.forEach((element) {
      newMessagesList.add(ChatBubbleWidget(
        senderName: element.senderName,
        message: element.msg,
        me: element.senderId == uid ? true : false,
        sentDate: DateTime.parse(element.sentDate),
      ));
    });
    chatsMessagesWidgets = newMessagesList;
    return;
  }

  void sendMessage() {
    log('Sending: ' + _msgController.text);
    widget._chatPageBloc.sendMessage(chatRoomId, _msgController.text.trim());
    _msgController.clear();
  }
}
