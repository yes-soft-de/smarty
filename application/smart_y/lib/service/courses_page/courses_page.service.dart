import 'package:inject/inject.dart';
import 'package:smarty/manager/courses/cources.manager.dart';
import 'package:smarty/model/course/course_list_item.model.dart';
import 'package:smarty/persistence/shared_preferences/shared+preferences_helper.dart';
import 'dart:convert';

@provide
class CoursesService{
  SharedPreferencesHelper _sharedPreferencesHelper;
  CoursesManager _coursesManager;
  CoursesService(this._sharedPreferencesHelper,this._coursesManager);

  Future<List<CourseListItem>> getCourses()async{

      return _coursesManager.getCourses();
  }
}