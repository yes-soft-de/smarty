import 'package:flutter/material.dart';
import 'package:inject/inject.dart';

import 'chat_routes.dart';
import 'ui/screens/chat_page/chat_page.dart';

@provide
class ChatModule {
  final ChatPage _chatPage;

  ChatModule(this._chatPage);

  Map<String, WidgetBuilder> getRoutes() {
    return {ChatRoutes.chatRoute: (context) => _chatPage};
  }
}
