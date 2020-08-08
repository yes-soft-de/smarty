class LoginResponse {
  bool success;
  LoginPayload data;

  LoginResponse({this.success, this.data});

  LoginResponse.fromJson(Map<String, dynamic> json) {
    success = json['success'];
    data = json['data'] != null ? new LoginPayload.fromJson(json['data']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['success'] = this.success;
    if (this.data != null) {
      data['data'] = this.data.toJson();
    }
    return data;
  }
}

class LoginPayload {
  String jwt;

  LoginPayload({this.jwt});

  LoginPayload.fromJson(Map<String, dynamic> json) {
    jwt = json['jwt'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['jwt'] = this.jwt;
    return data;
  }
}
