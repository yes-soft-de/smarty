import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/rxdart.dart';
import 'package:smarty/module_chat/model/chat/chat_model.dart';

import '../../service/chat/char_service.dart';

@provide
class ChatPageBloc {
  static const STATUS_CODE_INIT = 1588;
  static const STATUS_CODE_EMPTY_LIST = 1589;
  static const STATUS_CODE_GOT_DATA = 1590;
  static const STATUS_CODE_BUILDING_UI = 1591;

  bool listening = false;

  final ChatService _chatService;

  ChatPageBloc(this._chatService);

  final PublishSubject<Pair<int, List<ChatModel>>> _chatBlocSubject =
      new PublishSubject();

  Stream<Pair<int, List<ChatModel>>> get chatBlocStream =>
      _chatBlocSubject.stream;

  // We Should get the UUID of the ChatRoom, as such we should request the data here
  void getMessages(String chatRoomID) {
    if (!listening) listening = true;
    _chatService.chatMessagesStream.listen((event) {
      _chatBlocSubject.add(Pair(STATUS_CODE_GOT_DATA, event));
    });
    _chatService.requestMessages(chatRoomID);
  }

  void sendMessage(String chatRoomID, String chat) {
    _chatService.sendMessage(chatRoomID, chat);
  }

  void dispose() {
    _chatBlocSubject.close();
  }
}
