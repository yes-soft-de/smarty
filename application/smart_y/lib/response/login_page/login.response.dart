class LoginResponse {
  String token;
  String userDisplayName;
  String userEmail;
  String userNicename;

  LoginResponse(
      {this.token, this.userDisplayName, this.userEmail, this.userNicename});

  LoginResponse.fromJson(Map<String, dynamic> json) {
    token = json['token'];
    userDisplayName = json['user_display_name'];
    userEmail = json['user_email'];
    userNicename = json['user_nicename'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['token'] = this.token;
    data['user_display_name'] = this.userDisplayName;
    data['user_email'] = this.userEmail;
    data['user_nicename'] = this.userNicename;
    return data;
  }
}
