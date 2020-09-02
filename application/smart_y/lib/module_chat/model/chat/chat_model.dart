class ChatModel {
  String senderId;
  String senderName;
  String sentDate;
  String msg;

  ChatModel({this.sentDate, this.senderId, this.msg, this.senderName});

  ChatModel.fromJson(Map<String, dynamic> jsonData) {
    senderId = jsonData['sender'];
    msg = jsonData['msg'];
    senderName = jsonData['sender_name'];
    sentDate = jsonData['sentDate'].toString();
  }

  Map<String, dynamic> toJson() {
    Map<String, dynamic> jsonData = {
      'sender': senderId,
      'msg': msg,
      'sentDate': sentDate,
      'sender_name': senderName
    };

    return jsonData;
  }
}
