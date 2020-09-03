class LoginResponse {
  bool status;
  String message;
  Token token;
  User user;

  LoginResponse({this.status, this.message, this.token, this.user});

  LoginResponse.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    token = json['token'] != null ? new Token.fromJson(json['token']) : null;
    user = json['user'] != null ? new User.fromJson(json['user']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['message'] = this.message;
    if (this.token != null) {
      data['token'] = this.token.toJson();
    }
    if (this.user != null) {
      data['user'] = this.user.toJson();
    }
    return data;
  }
}

class Token {
  String accessToken;
  String clientId;
  int userId;
  String expires;
  Null scope;

  Token(
      {this.accessToken, this.clientId, this.userId, this.expires, this.scope});

  Token.fromJson(Map<String, dynamic> json) {
    accessToken = json['access_token'];
    clientId = json['client_id'];
    userId = json['user_id'];
    expires = json['expires'];
    scope = json['scope'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['access_token'] = this.accessToken;
    data['client_id'] = this.clientId;
    data['user_id'] = this.userId;
    data['expires'] = this.expires;
    data['scope'] = this.scope;
    return data;
  }
}

class User {
  int id;
  String name;
  String sub;
  String email;
  String avatar;

  User({this.id, this.name, this.sub, this.email, this.avatar});

  User.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    sub = json['sub'];
    email = json['email'];
    avatar = json['avatar'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['sub'] = this.sub;
    data['email'] = this.email;
    data['avatar'] = this.avatar;
    return data;
  }
}
