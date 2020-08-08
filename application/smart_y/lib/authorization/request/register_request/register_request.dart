class RegisterRequest {
  String username;
  String firstName;
  String email;
  String passw1;
  String passw2;
  String action;

  RegisterRequest(
      {this.username,
        this.firstName,
        this.email,
        this.passw1,
        this.passw2,
        this.action});

  RegisterRequest.fromJson(Map<String, dynamic> json) {
    username = json['username'];
    firstName = json['first_name'];
    email = json['email'];
    passw1 = json['passw1'];
    passw2 = json['passw2'];
    action = json['action'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['username'] = this.username;
    data['first_name'] = this.firstName;
    data['email'] = this.email;
    data['passw1'] = this.passw1;
    data['passw2'] = this.passw2;
    data['action'] = this.action;
    return data;
  }
}
