class LifterRegisterRequest {
  int userId;
  String description;
  String permissions;

  LifterRegisterRequest({this.userId, this.description, this.permissions});

  LifterRegisterRequest.fromJson(Map<String, dynamic> json) {
    userId = json['user_id'];
    description = json['description'];
    permissions = json['permissions'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['user_id'] = this.userId;
    data['description'] = this.description;
    data['permissions'] = this.permissions;
    return data;
  }
}