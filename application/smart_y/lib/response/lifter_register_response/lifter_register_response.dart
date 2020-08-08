class LifterRegisterResponse {
  String consumerKey;
  String consumerSecret;
  int id;
  int userId;
  String description;
  String permissions;
  String truncatedKey;
  String lastAccess;

  LifterRegisterResponse(
      {this.consumerKey,
        this.consumerSecret,
        this.id,
        this.userId,
        this.description,
        this.permissions,
        this.truncatedKey,
        this.lastAccess});

  LifterRegisterResponse.fromJson(Map<String, dynamic> json) {
    consumerKey = json['consumer_key'];
    consumerSecret = json['consumer_secret'];
    id = json['id'];
    userId = json['user_id'];
    description = json['description'];
    permissions = json['permissions'];
    truncatedKey = json['truncated_key'];
    lastAccess = json['last_access'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['consumer_key'] = this.consumerKey;
    data['consumer_secret'] = this.consumerSecret;
    data['id'] = this.id;
    data['user_id'] = this.userId;
    data['description'] = this.description;
    data['permissions'] = this.permissions;
    data['truncated_key'] = this.truncatedKey;
    data['last_access'] = this.lastAccess;
    return data;
  }
}
