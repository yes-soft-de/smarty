/*
    This is the only important part from the response,
    as such a service should only return a list of that

    NOTE: No fromJson or toJson here
*/
class CourseList{
  List<CourseListItem> courses;
}

class CourseListItem {
  Title title;
  Content1 content;
  Title excerpt;
  Title accessOpensMessage;
  Title accessClosesMessage;
  Title enrollmentOpensMessage;
  Title enrollmentClosesMessage;
  Title capacityMessage;
  Title length;
  Title restrictedMessage;
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
  String catalogVisibility;
  List<int> categories;
  List<int> tags;
  List<int> difficulties;
  List<int> tracks;
  String audioEmbed;
  String videoEmbed;
  bool capacityEnabled;
  int capacityLimit;
  int prerequisite;
  int prerequisiteTrack;
  String accessOpensDate;
  String accessClosesDate;
  String enrollmentOpensDate;
  String enrollmentClosesDate;
  bool videoTile;
  List<int> instructors;
  String salesPageType;
  int salesPagePageId;
  String salesPageUrl;
  Links lLinks;

  CourseListItem(
      {this.title,
        this.content,
        this.excerpt,
        this.accessOpensMessage,
        this.accessClosesMessage,
        this.enrollmentOpensMessage,
        this.enrollmentClosesMessage,
        this.capacityMessage,
        this.length,
        this.restrictedMessage,
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
        this.catalogVisibility,
        this.categories,
        this.tags,
        this.difficulties,
        this.tracks,
        this.audioEmbed,
        this.videoEmbed,
        this.capacityEnabled,
        this.capacityLimit,
        this.prerequisite,
        this.prerequisiteTrack,
        this.accessOpensDate,
        this.accessClosesDate,
        this.enrollmentOpensDate,
        this.enrollmentClosesDate,
        this.videoTile,
        this.instructors,
        this.salesPageType,
        this.salesPagePageId,
        this.salesPageUrl,
        this.lLinks});

  CourseListItem.fromJson(Map<String, dynamic> json) {
    title = json['title'] != null ? new Title.fromJson(json['title']) : null;
    content =
    json['content'] != null ? new Content1.fromJson(json['content']) : null;
    excerpt =
    json['excerpt'] != null ? new Title.fromJson(json['excerpt']) : null;
    accessOpensMessage = json['access_opens_message'] != null
        ? new Title.fromJson(json['access_opens_message'])
        : null;
    accessClosesMessage = json['access_closes_message'] != null
        ? new Title.fromJson(json['access_closes_message'])
        : null;
    enrollmentOpensMessage = json['enrollment_opens_message'] != null
        ? new Title.fromJson(json['enrollment_opens_message'])
        : null;
    enrollmentClosesMessage = json['enrollment_closes_message'] != null
        ? new Title.fromJson(json['enrollment_closes_message'])
        : null;
    capacityMessage = json['capacity_message'] != null
        ? new Title.fromJson(json['capacity_message'])
        : null;
    length = json['length'] != null ? new Title.fromJson(json['length']) : null;
    restrictedMessage = json['restricted_message'] != null
        ? new Title.fromJson(json['restricted_message'])
        : null;
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
    catalogVisibility = json['catalog_visibility'];
    categories = json['categories'].cast<int>();
    tags = json['tags'].cast<int>();
    difficulties = json['difficulties'].cast<int>();
    tracks = json['tracks'].cast<int>();
    audioEmbed = json['audio_embed'];
    videoEmbed = json['video_embed'];
    capacityEnabled = json['capacity_enabled'];
    capacityLimit = json['capacity_limit'];
    prerequisite = json['prerequisite'];
    prerequisiteTrack = json['prerequisite_track'];
    accessOpensDate = json['access_opens_date'];
    accessClosesDate = json['access_closes_date'];
    enrollmentOpensDate = json['enrollment_opens_date'];
    enrollmentClosesDate = json['enrollment_closes_date'];
    videoTile = json['video_tile'];
    instructors = json['instructors'].cast<int>();
    salesPageType = json['sales_page_type'];
    salesPagePageId = json['sales_page_page_id'];
    salesPageUrl = json['sales_page_url'];
    lLinks = json['_links'] != null ? new Links.fromJson(json['_links']) : null;
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
    if (this.accessOpensMessage != null) {
      data['access_opens_message'] = this.accessOpensMessage.toJson();
    }
    if (this.accessClosesMessage != null) {
      data['access_closes_message'] = this.accessClosesMessage.toJson();
    }
    if (this.enrollmentOpensMessage != null) {
      data['enrollment_opens_message'] = this.enrollmentOpensMessage.toJson();
    }
    if (this.enrollmentClosesMessage != null) {
      data['enrollment_closes_message'] = this.enrollmentClosesMessage.toJson();
    }
    if (this.capacityMessage != null) {
      data['capacity_message'] = this.capacityMessage.toJson();
    }
    if (this.length != null) {
      data['length'] = this.length.toJson();
    }
    if (this.restrictedMessage != null) {
      data['restricted_message'] = this.restrictedMessage.toJson();
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
    data['catalog_visibility'] = this.catalogVisibility;
    data['categories'] = this.categories;
    data['tags'] = this.tags;
    data['difficulties'] = this.difficulties;
    data['tracks'] = this.tracks;
    data['audio_embed'] = this.audioEmbed;
    data['video_embed'] = this.videoEmbed;
    data['capacity_enabled'] = this.capacityEnabled;
    data['capacity_limit'] = this.capacityLimit;
    data['prerequisite'] = this.prerequisite;
    data['prerequisite_track'] = this.prerequisiteTrack;
    data['access_opens_date'] = this.accessOpensDate;
    data['access_closes_date'] = this.accessClosesDate;
    data['enrollment_opens_date'] = this.enrollmentOpensDate;
    data['enrollment_closes_date'] = this.enrollmentClosesDate;
    data['video_tile'] = this.videoTile;
    data['instructors'] = this.instructors;
    data['sales_page_type'] = this.salesPageType;
    data['sales_page_page_id'] = this.salesPagePageId;
    data['sales_page_url'] = this.salesPageUrl;
    if (this.lLinks != null) {
      data['_links'] = this.lLinks.toJson();
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
class Content1 {
  String rendered;
  String raw;

  Content1({this.rendered, this.raw});

  Content1.fromJson(Map<String, dynamic> json) {
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

class Links {
  List<Self> self;
  List<Collection> collection;
  List<AccessPlans> accessPlans;
  List<Content> content;
  List<Enrollments> enrollments;
  List<Instructors> instructors;
  List<Prerequisites> prerequisites;
  List<Students> students;
  List<WpFeaturedMedia> wpFeaturedMedia;
  List<WpTerm> wpTerm;

  Links(
      {this.self,
        this.collection,
        this.accessPlans,
        this.content,
        this.enrollments,
        this.instructors,
        this.prerequisites,
        this.students,
        this.wpFeaturedMedia,
        this.wpTerm});

  Links.fromJson(Map<String, dynamic> json) {
    if (json['self'] != null) {
      self = new List<Self>();
      json['self'].forEach((v) {
        self.add(new Self.fromJson(v));
      });
    }
    if (json['collection'] != null) {
      collection = new List<Collection>();
      json['collection'].forEach((v) {
        collection.add(new Collection.fromJson(v));
      });
    }
    if (json['access_plans'] != null) {
      accessPlans = new List<AccessPlans>();
      json['access_plans'].forEach((v) {
        accessPlans.add(new AccessPlans.fromJson(v));
      });
    }
    if (json['content'] != null) {
      content = new List<Content>();
      json['content'].forEach((v) {
        content.add(new Content.fromJson(v));
      });
    }
    if (json['enrollments'] != null) {
      enrollments = new List<Enrollments>();
      json['enrollments'].forEach((v) {
        enrollments.add(new Enrollments.fromJson(v));
      });
    }
    if (json['instructors'] != null) {
      instructors = new List<Instructors>();
      json['instructors'].forEach((v) {
        instructors.add(new Instructors.fromJson(v));
      });
    }
    if (json['prerequisites'] != null) {
      prerequisites = new List<Prerequisites>();
      json['prerequisites'].forEach((v) {
        prerequisites.add(new Prerequisites.fromJson(v));
      });
    }
    if (json['students'] != null) {
      students = new List<Students>();
      json['students'].forEach((v) {
        students.add(new Students.fromJson(v));
      });
    }
    if (json['wp:featured_media'] != null) {
      wpFeaturedMedia = new List<WpFeaturedMedia>();
      json['wp:featured_media'].forEach((v) {
        wpFeaturedMedia.add(new WpFeaturedMedia.fromJson(v));
      });
    }
    if (json['wp:term'] != null) {
      wpTerm = new List<WpTerm>();
      json['wp:term'].forEach((v) {
        wpTerm.add(new WpTerm.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    if (this.self != null) {
      data['self'] = this.self.map((v) => v.toJson()).toList();
    }
    if (this.collection != null) {
      data['collection'] = this.collection.map((v) => v.toJson()).toList();
    }
    if (this.accessPlans != null) {
      data['access_plans'] = this.accessPlans.map((v) => v.toJson()).toList();
    }
    if (this.content != null) {
      data['content'] = this.content.map((v) => v.toJson()).toList();
    }
    if (this.enrollments != null) {
      data['enrollments'] = this.enrollments.map((v) => v.toJson()).toList();
    }
    if (this.instructors != null) {
      data['instructors'] = this.instructors.map((v) => v.toJson()).toList();
    }
    if (this.prerequisites != null) {
      data['prerequisites'] =
          this.prerequisites.map((v) => v.toJson()).toList();
    }
    if (this.students != null) {
      data['students'] = this.students.map((v) => v.toJson()).toList();
    }
    if (this.wpFeaturedMedia != null) {
      data['wp:featured_media'] =
          this.wpFeaturedMedia.map((v) => v.toJson()).toList();
    }
    if (this.wpTerm != null) {
      data['wp:term'] = this.wpTerm.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Self {
  String href;

  Self({this.href});

  Self.fromJson(Map<String, dynamic> json) {
    href = json['href'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['href'] = this.href;
    return data;
  }
}

class Collection{
  String href;

  Collection({this.href});

  Collection.fromJson(Map<String, dynamic> json) {
    href = json['href'];
}
  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['href'] = this.href;
    return data;
  }
}
class AccessPlans{
  String href;

  AccessPlans({this.href});

  AccessPlans.fromJson(Map<String, dynamic> json) {
    href = json['href'];
  }
  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['href'] = this.href;
    return data;
  }
}
class Content{
  String href;

  Content({this.href});

  Content.fromJson(Map<String, dynamic> json) {
    href = json['href'];
  }
  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['href'] = this.href;
    return data;
  }
}
class Enrollments{
  String href;

  Enrollments({this.href});

  Enrollments.fromJson(Map<String, dynamic> json) {
    href = json['href'];
  }
  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['href'] = this.href;
    return data;
  }
}

class Instructors{
  String href;

  Instructors({this.href});

  Instructors.fromJson(Map<String, dynamic> json) {
    href = json['href'];
  }
  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['href'] = this.href;
    return data;
  }
}
class Students{
  String href;

  Students({this.href});

  Students.fromJson(Map<String, dynamic> json) {
    href = json['href'];
  }
  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['href'] = this.href;
    return data;
  }
}

class WpFeaturedMedia{
  String href;

  WpFeaturedMedia({this.href});

  WpFeaturedMedia.fromJson(Map<String, dynamic> json) {
    href = json['href'];
  }
  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['href'] = this.href;
    return data;
  }
}
class Prerequisites {
  String type;
  String href;

  Prerequisites({this.type, this.href});

  Prerequisites.fromJson(Map<String, dynamic> json) {
    type = json['type'];
    href = json['href'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['type'] = this.type;
    data['href'] = this.href;
    return data;
  }
}

class WpTerm {
  String taxonomy;
  String href;

  WpTerm({this.taxonomy, this.href});

  WpTerm.fromJson(Map<String, dynamic> json) {
    taxonomy = json['taxonomy'];
    href = json['href'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['taxonomy'] = this.taxonomy;
    data['href'] = this.href;
    return data;
  }
}
