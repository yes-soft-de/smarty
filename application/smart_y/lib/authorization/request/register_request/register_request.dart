class RegisterRequest {
  String password;
  String email;
  String userLogin;
  String userNicename;
  String userUrl;
  String displayName;
  String nickname;
  String firstName;
  String lastName;
  String description;
  String commentShortcuts;
  String authKey;

  RegisterRequest(
      {this.password,
        this.email,
        this.userLogin,
        this.userNicename,
        this.userUrl,
        this.displayName,
        this.nickname,
        this.firstName,
        this.lastName,
        this.description,
        this.commentShortcuts,
      this.authKey});

  RegisterRequest.fromJson(Map<String, dynamic> json) {
    password = json['password'];
    email = json['email'];
    userLogin = json['user_login'];
    userNicename = json['user_nicename'];
    userUrl = json['user_url'];
    displayName = json['display_name'];
    nickname = json['nickname'];
    firstName = json['first_name'];
    lastName = json['last_name'];
    description = json['description'];
    commentShortcuts = json['comment_shortcuts'];
    authKey = json['AUTH_KEY'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['password'] = this.password;
    data['email'] = this.email;
    data['user_login'] = this.userLogin;
    data['user_nicename'] = this.userNicename;
    data['user_url'] = this.userUrl;
    data['display_name'] = this.displayName;
    data['nickname'] = this.nickname;
    data['first_name'] = this.firstName;
    data['last_name'] = this.lastName;
    data['description'] = this.description;
    data['comment_shortcuts'] = this.commentShortcuts;
    data['AUTH_KEY'] = this.authKey;
    return data;
  }
}
