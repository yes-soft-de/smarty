class LessonResponse {
  Title title;
  Title content;
  Title excerpt;
  int id;
  String dateCreated;
  String dateCreatedGmt;
  String dateUpdated;
  String dateUpdatedGmt;
  int menuOrder;
  String slug;
  String permalink;
  String postType;
  String status;
  String password;
  int featuredMedia;
  String commentStatus;
  String pingStatus;
  String audioEmbed;
  String videoEmbed;
  int prerequisite;
  bool public;
  int courseId;
  int parentId;
  int points;
  int order;
  String dripMethod;
  int dripDays;
  String dripDate;
  Quiz quiz;
  Quiz assignment;

  LessonResponse(
      {this.title,
        this.content,
        this.excerpt,
        this.id,
        this.dateCreated,
        this.dateCreatedGmt,
        this.dateUpdated,
        this.dateUpdatedGmt,
        this.menuOrder,
        this.slug,
        this.permalink,
        this.postType,
        this.status,
        this.password,
        this.featuredMedia,
        this.commentStatus,
        this.pingStatus,
        this.audioEmbed,
        this.videoEmbed,
        this.prerequisite,
        this.public,
        this.courseId,
        this.parentId,
        this.points,
        this.order,
        this.dripMethod,
        this.dripDays,
        this.dripDate,
        this.quiz,
        this.assignment});

  LessonResponse.fromJson(Map<String, dynamic> json) {
    title = json['title'] != null ? new Title.fromJson(json['title']) : null;
    content =
    json['content'] != null ? new Title.fromJson(json['content']) : null;
    excerpt =
    json['excerpt'] != null ? new Title.fromJson(json['excerpt']) : null;
    id = json['id'];
    dateCreated = json['date_created'];
    dateCreatedGmt = json['date_created_gmt'];
    dateUpdated = json['date_updated'];
    dateUpdatedGmt = json['date_updated_gmt'];
    menuOrder = json['menu_order'];
    slug = json['slug'];
    permalink = json['permalink'];
    postType = json['post_type'];
    status = json['status'];
    password = json['password'];
    featuredMedia = json['featured_media'];
    commentStatus = json['comment_status'];
    pingStatus = json['ping_status'];
    audioEmbed = json['audio_embed'];
    videoEmbed = json['video_embed'];
    prerequisite = json['prerequisite'];
    public = json['public'];
    courseId = json['course_id'];
    parentId = json['parent_id'];
    points = json['points'];
    order = json['order'];
    dripMethod = json['drip_method'];
    dripDays = json['drip_days'];
    dripDate = json['drip_date'];
    quiz = json['quiz'] != null ? new Quiz.fromJson(json['quiz']) : null;
    assignment = json['assignment'] != null
        ? new Quiz.fromJson(json['assignment'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    if (this.title != null) {
      data['title'] = this.title.toJson();
    }
    if (this.content != null) {
      data['content'] = this.content.toJson();
    }
    if (this.excerpt != null) {
      data['excerpt'] = this.excerpt.toJson();
    }
    data['id'] = this.id;
    data['date_created'] = this.dateCreated;
    data['date_created_gmt'] = this.dateCreatedGmt;
    data['date_updated'] = this.dateUpdated;
    data['date_updated_gmt'] = this.dateUpdatedGmt;
    data['menu_order'] = this.menuOrder;
    data['slug'] = this.slug;
    data['permalink'] = this.permalink;
    data['post_type'] = this.postType;
    data['status'] = this.status;
    data['password'] = this.password;
    data['featured_media'] = this.featuredMedia;
    data['comment_status'] = this.commentStatus;
    data['ping_status'] = this.pingStatus;
    data['audio_embed'] = this.audioEmbed;
    data['video_embed'] = this.videoEmbed;
    data['prerequisite'] = this.prerequisite;
    data['public'] = this.public;
    data['course_id'] = this.courseId;
    data['parent_id'] = this.parentId;
    data['points'] = this.points;
    data['order'] = this.order;
    data['drip_method'] = this.dripMethod;
    data['drip_days'] = this.dripDays;
    data['drip_date'] = this.dripDate;
    if (this.quiz != null) {
      data['quiz'] = this.quiz.toJson();
    }
    if (this.assignment != null) {
      data['assignment'] = this.assignment.toJson();
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

class Quiz {
  bool enabled;
  int id;
  String progression;

  Quiz({this.enabled, this.id, this.progression});

  Quiz.fromJson(Map<String, dynamic> json) {
    enabled = json['enabled'];
    id = json['id'];
    progression = json['progression'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['enabled'] = this.enabled;
    data['id'] = this.id;
    data['progression'] = this.progression;
    return data;
  }
}
