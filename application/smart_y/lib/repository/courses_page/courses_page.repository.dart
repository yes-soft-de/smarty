import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/model/course/course_list_item.model.dart';


import 'package:smarty/network/http_client/http_client.dart';

@provide
class CoursesRepository{
  HttpClient _httpClient;
  CoursesRepository(this._httpClient);

  Future<List<CourseListItem>> getCourses() async{
    var response = await _httpClient.get(ApiUrls.CoursesApi);

    if(response == null) return null;

   // Map<String, dynamic> coursesData = jsonDecode(response.data );
//    List<dynamic> list = json.decode(response.data);
    var courseslist = jsonDecode(response.data) as List;
    List<CourseListItem> courses = courseslist.map((course) => CourseListItem.fromJson(course)).toList();


    return courses;
}
}