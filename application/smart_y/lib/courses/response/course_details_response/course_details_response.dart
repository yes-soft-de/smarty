class CourseDetailsResponse {
  Course course;
  var description;
  List<Curriculum> curriculum;
//  List<Null> reviews;
  List<Instructors> instructors;
  var purchaseLink;

  CourseDetailsResponse(
      {this.course,
        this.description,
        this.curriculum,
//        this.reviews,
        this.instructors,
        this.purchaseLink});

  CourseDetailsResponse.fromJson(Map<String, dynamic> json) {
    course =
    json['course'] != null ? new Course.fromJson(json['course']) : null;
    description = json['description'];
    if (json['curriculum'] != null && json['curriculum'] != false) {
      curriculum = new List<Curriculum>();
      json['curriculum'].forEach((v) {
        curriculum.add(new Curriculum.fromJson(v));
      });
    }
//    if (json['reviews'] != null) {
//      reviews = new List<Null>();
//      json['reviews'].forEach((v) {
//        reviews.add(new Null.fromJson(v));
//      });
//    }
    if (json['instructors'] != null) {
      instructors = new List<Instructors>();
      json['instructors'].forEach((v) {
        instructors.add(new Instructors.fromJson(v));
      });
    }
    purchaseLink = json['purchase_link'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    if (this.course != null) {
      data['course'] = this.course.toJson();
    }
    data['description'] = this.description;
    if (this.curriculum != null) {
      data['curriculum'] = this.curriculum.map((v) => v.toJson()).toList();
    }
//    if (this.reviews != null) {
//      data['reviews'] = this.reviews.map((v) => v.toJson()).toList();
//    }
    if (this.instructors != null) {
      data['instructors'] = this.instructors.map((v) => v.toJson()).toList();
    }
    data['purchase_link'] = this.purchaseLink;
    return data;
  }
}

class Course {
  var id;
  var name;
  var dateCreated;
  var status;
  var price;
  var priceHtml;
  var totalStudents;
  var seats;
  var startDate;
  var averageRating;
  var ratingCount;
  var featuredImage;
  List<Categories> categories;
  Instructor instructor;
  var menuOrder;

  Course(
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

  Course.fromJson(Map<String, dynamic> json) {
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
  var id;
  var name;
  var slug;
  var image;

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

class Curriculum {
  var key;
  var id;
  var type;
  var title;
  var duration;
  List<Null> meta;

  Curriculum(
      {this.key, this.id, this.type, this.title, this.duration, this.meta});

  Curriculum.fromJson(Map<String, dynamic> json) {
    key = json['key'];
    id = json['id'];
    type = json['type'];
    title = json['title'];
    duration = json['duration'];
//    if (json['meta'] != null) {
//      meta = new List<Null>();
//      json['meta'].forEach((v) {
//        meta.add(new Null.fromJson(v));
//      });
//    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['key'] = this.key;
    data['id'] = this.id;
    data['type'] = this.type;
    data['title'] = this.title;
    data['duration'] = this.duration;
//    if (this.meta != null) {
//      data['meta'] = this.meta.map((v) => v.toJson()).toList();
//    }
    return data;
  }
}

class Instructors {
  var id;
  var name;
  var avatar;
  var sub;
  var averageRating;
  var studentCount;
  var courseCount;
  var bio;

  Instructors(
      {this.id,
        this.name,
        this.avatar,
        this.sub,
        this.averageRating,
        this.studentCount,
        this.courseCount,
        this.bio});

  Instructors.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    avatar = json['avatar'];
    sub = json['sub'];
    averageRating = json['average_rating'];
    studentCount = json['student_count'];
    courseCount = json['course_count'];
    bio = json['bio'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['avatar'] = this.avatar;
    data['sub'] = this.sub;
    data['average_rating'] = this.averageRating;
    data['student_count'] = this.studentCount;
    data['course_count'] = this.courseCount;
    data['bio'] = this.bio;
    return data;
  }
}
