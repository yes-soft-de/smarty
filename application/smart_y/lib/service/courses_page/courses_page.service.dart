import 'package:inject/inject.dart';
import 'package:smarty/manager/courses/cources.manager.dart';
import 'package:smarty/model/course/course_list_item.model.dart';
import 'package:smarty/persistence/shared_preferences/shared+preferences_helper.dart';
import 'dart:convert';

import 'package:smarty/response/course_response/course_response.dart';

@provide
class CoursesService {
  SharedPreferencesHelper _sharedPreferencesHelper;
  CoursesManager _coursesManager;

  CoursesService(this._sharedPreferencesHelper, this._coursesManager);

  // Mapping data, and transforming it to useful data
  Future<List<CourseModel>> getCourses() async {
    List<CourseResponse> coursesResponse =
        await this._coursesManager.getCourses();

    if (coursesResponse == null) {
      return null;
    }

    List<CourseModel> availableCourses = [];
    coursesResponse.forEach((course) {
      // TODO: Create a Course Model
      availableCourses.add(CourseModel(title: course.title.rendered, content: course.excerpt.rendered));
    });
    return availableCourses;
  }
}