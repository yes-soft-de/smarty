import 'package:smarty/home/response/lesson_response/lesson_response.dart';
class SectionResponse {
  int id;
  String dateCreated;
  String dateCreatedGmt;
  String dateUpdated;
  String dateUpdatedGmt;
  int order;
  int parentId;
  String postType;
  Title title;
  List<LessonResponse> lessons;

  SectionResponse(
      {this.id,
        this.dateCreated,
        this.dateCreatedGmt,
        this.dateUpdated,
        this.dateUpdatedGmt,
        this.order,
        this.parentId,
        this.postType,
        this.title});

  SectionResponse.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    dateCreated = json['date_created'];
    dateCreatedGmt = json['date_created_gmt'];
    dateUpdated = json['date_updated'];
    dateUpdatedGmt = json['date_updated_gmt'];
    order = json['order'];
    parentId = json['parent_id'];
    postType = json['post_type'];
    title = json['title'] != null ? new Title.fromJson(json['title']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['date_created'] = this.dateCreated;
    data['date_created_gmt'] = this.dateCreatedGmt;
    data['date_updated'] = this.dateUpdated;
    data['date_updated_gmt'] = this.dateUpdatedGmt;
    data['order'] = this.order;
    data['parent_id'] = this.parentId;
    data['post_type'] = this.postType;
    if (this.title != null) {
      data['title'] = this.title.toJson();
    }
    return data;
  }
}

class Title {
  String rendered;
  String raw;

  Title({this.rendered, this.raw});

  Title.fromJson(Map<String, dynamic> json) {
    rendered = json['rendered'];
    raw = json['raw'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['rendered'] = this.rendered;
    data['raw'] = this.raw;
    return data;
  }
}
