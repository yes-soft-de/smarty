import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:inject/inject.dart';
import 'package:smarty/module_chat/model/chat/chat_model.dart';

@provide
class ChatRepository {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  Stream<QuerySnapshot> requestMessages(String chatRoomID) {
    return _firestore
        .collection('chat_rooms')
        .doc(chatRoomID)
        .collection('messages')
        .orderBy('sentDate', descending: false)
        .snapshots();
  }

  void sendMessage(String chatRoomID, ChatModel chatMessage) {
    _firestore
        .collection('chat_rooms')
        .doc(chatRoomID)
        .collection('messages')
        .add(chatMessage.toJson());
  }
}
