import 'package:smarty/authorization/consts/api_credentials/api_credentials.dart';

class RegisterRequest {
  String username;
  String email;
  String password;
  String clientId = ApiCredentials.API_CLIENT_ID;
  String state = ApiCredentials.API_SECRET_STATE;
  String device;
  String platform;

  RegisterRequest(
      {this.username, this.email, this.password, this.device, this.platform});

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['username'] = this.username;
    data['email'] = this.email;
    data['password'] = this.password;
    data['client_id'] = this.clientId;
    data['state'] = this.state;
    data['device'] = this.device;
    data['platform'] = this.platform;
    return data;
  }
}
