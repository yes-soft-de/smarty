import 'package:smarty/authorization/consts/api_credentials/api_credentials.dart';

class LoginRequest {
  String username;
  String password;
  String clientId = ApiCredentials.API_CLIENT_ID;
  String state = ApiCredentials.API_SECRET_STATE;

  LoginRequest({this.username, this.password});

  LoginRequest.fromJson(Map<String, dynamic> json) {
    username = json['username'];
    password = json['password'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['username'] = this.username.split('@')[0];
    data['password'] = this.password;
    data['client_id'] = this.clientId;
    data['state'] = this.state;
    return data;
  }
}
