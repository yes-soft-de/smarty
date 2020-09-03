import 'package:inject/inject.dart';
import 'package:rxdart/rxdart.dart';
import 'package:smarty/module_chat/manager/chat/chat_manager.dart';
import 'package:smarty/module_chat/model/chat/chat_model.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';

@provide
class ChatService {
  final ChatManager _chatManager;
  final SharedPreferencesHelper _preferencesHelper;

  ChatService(this._chatManager, this._preferencesHelper);

  // This is Real Time, That is Why I went this way
  final PublishSubject<List<ChatModel>> _chatPublishSubject =
      new PublishSubject();

  Stream<List<ChatModel>> get chatMessagesStream => _chatPublishSubject.stream;

  void requestMessages(String chatRoomID) async {
    _chatManager.getMessages(chatRoomID).listen((event) {
      List<ChatModel> chatMessagesList = [];
      event.docs.forEach((element) {
        chatMessagesList.add(new ChatModel.fromJson(element.data()));
      });

      _chatPublishSubject.add(chatMessagesList);
    });
  }

  void sendMessage(String chatRoomID, String msg) async {
    String username = await _preferencesHelper.getUserName();
    String userId = await _preferencesHelper.getUserId();
    ChatModel model = new ChatModel(
      msg: msg,
      senderId: userId,
      senderName: username,
      sentDate: DateTime.now().toIso8601String(),
    );
    _chatManager.sendMessage(chatRoomID, model);
  }

  void dispose() {
    _chatPublishSubject.close();
  }
}
