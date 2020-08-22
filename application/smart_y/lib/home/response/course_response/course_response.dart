class CourseResponse {
  int id;
  String name;
  int dateCreated;
  String status;
  String price;
  String priceHtml;
  int totalStudents;
  String seats;
  int startDate;
  String averageRating;
  String ratingCount;
  String featuredImage;
  List<Categories> categories;
  Instructor instructor;
  int menuOrder;

  CourseResponse(
      {this.id,
        this.name,
        this.dateCreated,
        this.status,
        this.price,
        this.priceHtml,
        this.totalStudents,
        this.seats,
        this.startDate,
        this.averageRating,
        this.ratingCount,
        this.featuredImage,
        this.categories,
        this.instructor,
        this.menuOrder});

  CourseResponse.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    dateCreated = json['date_created'];
    status = json['status'];
    price = json['price'];
    priceHtml = json['price_html'];
    totalStudents = json['total_students'];
    seats = json['seats'];
    startDate = json['start_date'];
    averageRating = json['average_rating'];
    ratingCount = json['rating_count'];
    featuredImage = json['featured_image'];
    if (json['categories'] != null) {
      categories = new List<Categories>();
      json['categories'].forEach((v) {
        categories.add(new Categories.fromJson(v));
      });
    }
    instructor = json['instructor'] != null
        ? new Instructor.fromJson(json['instructor'])
        : null;
    menuOrder = json['menu_order'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['date_created'] = this.dateCreated;
    data['status'] = this.status;
    data['price'] = this.price;
    data['price_html'] = this.priceHtml;
    data['total_students'] = this.totalStudents;
    data['seats'] = this.seats;
    data['start_date'] = this.startDate;
    data['average_rating'] = this.averageRating;
    data['rating_count'] = this.ratingCount;
    data['featured_image'] = this.featuredImage;
    if (this.categories != null) {
      data['categories'] = this.categories.map((v) => v.toJson()).toList();
    }
    if (this.instructor != null) {
      data['instructor'] = this.instructor.toJson();
    }
    data['menu_order'] = this.menuOrder;
    return data;
  }
}

class Categories {
  int id;
  String name;
  String slug;
  String image;

  Categories({this.id, this.name, this.slug, this.image});

  Categories.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    slug = json['slug'];
    image = json['image'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['slug'] = this.slug;
    data['image'] = this.image;
    return data;
  }
}

class Instructor {
  String id;
  String name;
  String avatar;
  String sub;

  Instructor({this.id, this.name, this.avatar, this.sub});

  Instructor.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    avatar = json['avatar'];
    sub = json['sub'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['avatar'] = this.avatar;
    data['sub'] = this.sub;
    return data;
  }
}


//class CourseResponse {
//  Title title;
//  Title content;
//  Title excerpt;
//  Title accessOpensMessage;
//  Title accessClosesMessage;
//  Title enrollmentOpensMessage;
//  Title enrollmentClosesMessage;
//  Title capacityMessage;
//  Title length;
//  Title restrictedMessage;
//  int id;
//  String dateCreated;
//  String dateCreatedGmt;
//  String dateUpdated;
//  String dateUpdatedGmt;
//  int menuOrder;
//  String slug;
//  String permalink;
//  String postType;
//  String status;
//  String password;
//  int featuredMedia;
//  String commentStatus;
//  String pingStatus;
//  String catalogVisibility;
//  List<int> categories;
//  List<int> tags;
//  List<int> difficulties;
//  List<int> tracks;
//  String audioEmbed;
//  String videoEmbed;
//  bool capacityEnabled;
//  int capacityLimit;
//  int prerequisite;
//  int prerequisiteTrack;
//  String accessOpensDate;
//  String accessClosesDate;
//  String enrollmentOpensDate;
//  String enrollmentClosesDate;
//  bool videoTile;
//  List<int> instructors;
//  String salesPageType;
//  int salesPagePageId;
//  String salesPageUrl;
//
//  CourseResponse(
//      {this.title,
//        this.content,
//        this.excerpt,
//        this.accessOpensMessage,
//        this.accessClosesMessage,
//        this.enrollmentOpensMessage,
//        this.enrollmentClosesMessage,
//        this.capacityMessage,
//        this.length,
//        this.restrictedMessage,
//        this.id,
//        this.dateCreated,
//        this.dateCreatedGmt,
//        this.dateUpdated,
//        this.dateUpdatedGmt,
//        this.menuOrder,
//        this.slug,
//        this.permalink,
//        this.postType,
//        this.status,
//        this.password,
//        this.featuredMedia,
//        this.commentStatus,
//        this.pingStatus,
//        this.catalogVisibility,
//        this.categories,
//        this.tags,
//        this.difficulties,
//        this.tracks,
//        this.audioEmbed,
//        this.videoEmbed,
//        this.capacityEnabled,
//        this.capacityLimit,
//        this.prerequisite,
//        this.prerequisiteTrack,
//        this.accessOpensDate,
//        this.accessClosesDate,
//        this.enrollmentOpensDate,
//        this.enrollmentClosesDate,
//        this.videoTile,
//        this.instructors,
//        this.salesPageType,
//        this.salesPagePageId,
//        this.salesPageUrl});
//
//  CourseResponse.fromJson(Map<String, dynamic> json) {
//    title = json['title'] != null ? new Title.fromJson(json['title']) : null;
//    content =
//    json['content'] != null ? new Title.fromJson(json['content']) : null;
//    excerpt =
//    json['excerpt'] != null ? new Title.fromJson(json['excerpt']) : null;
//    accessOpensMessage = json['access_opens_message'] != null
//        ? new Title.fromJson(json['access_opens_message'])
//        : null;
//    accessClosesMessage = json['access_closes_message'] != null
//        ? new Title.fromJson(json['access_closes_message'])
//        : null;
//    enrollmentOpensMessage = json['enrollment_opens_message'] != null
//        ? new Title.fromJson(json['enrollment_opens_message'])
//        : null;
//    enrollmentClosesMessage = json['enrollment_closes_message'] != null
//        ? new Title.fromJson(json['enrollment_closes_message'])
//        : null;
//    capacityMessage = json['capacity_message'] != null
//        ? new Title.fromJson(json['capacity_message'])
//        : null;
//    length = json['length'] != null ? new Title.fromJson(json['length']) : null;
//    restrictedMessage = json['restricted_message'] != null
//        ? new Title.fromJson(json['restricted_message'])
//        : null;
//    id = json['id'];
//    dateCreated = json['date_created'];
//    dateCreatedGmt = json['date_created_gmt'];
//    dateUpdated = json['date_updated'];
//    dateUpdatedGmt = json['date_updated_gmt'];
//    menuOrder = json['menu_order'];
//    slug = json['slug'];
//    permalink = json['permalink'];
//    postType = json['post_type'];
//    status = json['status'];
//    password = json['password'];
//    featuredMedia = json['featured_media'];
//    commentStatus = json['comment_status'];
//    pingStatus = json['ping_status'];
//    catalogVisibility = json['catalog_visibility'];
//    categories = json['categories'].cast<int>();
//    tags = json['tags'].cast<int>();
//    difficulties = json['difficulties'].cast<int>();
//    tracks = json['tracks'].cast<int>();
//    audioEmbed = json['audio_embed'];
//    videoEmbed = json['video_embed'];
//    capacityEnabled = json['capacity_enabled'];
//    capacityLimit = json['capacity_limit'];
//    prerequisite = json['prerequisite'];
//    prerequisiteTrack = json['prerequisite_track'];
//    accessOpensDate = json['access_opens_date'];
//    accessClosesDate = json['access_closes_date'];
//    enrollmentOpensDate = json['enrollment_opens_date'];
//    enrollmentClosesDate = json['enrollment_closes_date'];
//    videoTile = json['video_tile'];
//    instructors = json['instructors'].cast<int>();
//    salesPageType = json['sales_page_type'];
//    salesPagePageId = json['sales_page_page_id'];
//    salesPageUrl = json['sales_page_url'];
//  }
//
//  Map<String, dynamic> toJson() {
//    final Map<String, dynamic> data = new Map<String, dynamic>();
//    if (this.title != null) {
//      data['title'] = this.title.toJson();
//    }
//    if (this.content != null) {
//      data['content'] = this.content.toJson();
//    }
//    if (this.excerpt != null) {
//      data['excerpt'] = this.excerpt.toJson();
//    }
//    if (this.accessOpensMessage != null) {
//      data['access_opens_message'] = this.accessOpensMessage.toJson();
//    }
//    if (this.accessClosesMessage != null) {
//      data['access_closes_message'] = this.accessClosesMessage.toJson();
//    }
//    if (this.enrollmentOpensMessage != null) {
//      data['enrollment_opens_message'] = this.enrollmentOpensMessage.toJson();
//    }
//    if (this.enrollmentClosesMessage != null) {
//      data['enrollment_closes_message'] = this.enrollmentClosesMessage.toJson();
//    }
//    if (this.capacityMessage != null) {
//      data['capacity_message'] = this.capacityMessage.toJson();
//    }
//    if (this.length != null) {
//      data['length'] = this.length.toJson();
//    }
//    if (this.restrictedMessage != null) {
//      data['restricted_message'] = this.restrictedMessage.toJson();
//    }
//    data['id'] = this.id;
//    data['date_created'] = this.dateCreated;
//    data['date_created_gmt'] = this.dateCreatedGmt;
//    data['date_updated'] = this.dateUpdated;
//    data['date_updated_gmt'] = this.dateUpdatedGmt;
//    data['menu_order'] = this.menuOrder;
//    data['slug'] = this.slug;
//    data['permalink'] = this.permalink;
//    data['post_type'] = this.postType;
//    data['status'] = this.status;
//    data['password'] = this.password;
//    data['featured_media'] = this.featuredMedia;
//    data['comment_status'] = this.commentStatus;
//    data['ping_status'] = this.pingStatus;
//    data['catalog_visibility'] = this.catalogVisibility;
//    data['categories'] = this.categories;
//    data['tags'] = this.tags;
//    data['difficulties'] = this.difficulties;
//    data['tracks'] = this.tracks;
//    data['audio_embed'] = this.audioEmbed;
//    data['video_embed'] = this.videoEmbed;
//    data['capacity_enabled'] = this.capacityEnabled;
//    data['capacity_limit'] = this.capacityLimit;
//    data['prerequisite'] = this.prerequisite;
//    data['prerequisite_track'] = this.prerequisiteTrack;
//    data['access_opens_date'] = this.accessOpensDate;
//    data['access_closes_date'] = this.accessClosesDate;
//    data['enrollment_opens_date'] = this.enrollmentOpensDate;
//    data['enrollment_closes_date'] = this.enrollmentClosesDate;
//    data['video_tile'] = this.videoTile;
//    data['instructors'] = this.instructors;
//    data['sales_page_type'] = this.salesPageType;
//    data['sales_page_page_id'] = this.salesPagePageId;
//    data['sales_page_url'] = this.salesPageUrl;
//    return data;
//  }
//}
//
//class Title {
//  String rendered;
//  String raw;
//
//  Title({this.rendered, this.raw});
//
//  Title.fromJson(Map<String, dynamic> json) {
//    rendered = json['rendered'];
//    raw = json['raw'];
//  }
//
//  Map<String, dynamic> toJson() {
//    final Map<String, dynamic> data = new Map<String, dynamic>();
//    data['rendered'] = this.rendered;
//    data['raw'] = this.raw;
//    return data;
//  }
//}
