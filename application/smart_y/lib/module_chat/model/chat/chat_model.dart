class ChatModel {
  String sender;
  String sentDate;
  String msg;

  ChatModel({this.sentDate, this.sender, this.msg});

  ChatModel.fromJson(Map<String, dynamic> jsonData) {
    sender = jsonData['sender'];
    msg = jsonData['msg'];
    sentDate = jsonData['sentDate'].toString();
  }

  Map<String, dynamic> toJson() {
    Map<String, dynamic> jsonData = {
      'sender': sender,
      'msg': msg,
      'sentDate': sentDate
    };

    return jsonData;
  }
}
