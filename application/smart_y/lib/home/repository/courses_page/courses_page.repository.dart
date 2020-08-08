import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';

import 'package:smarty/network/http_client/api_client.dart';
import 'package:smarty/home/response/course_response/course_response.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';

@provide
class CoursesRepository {
  ApiClient _httpClient;
  SharedPreferencesHelper _preferencesHelper;

  CoursesRepository(this._httpClient, this._preferencesHelper);

  Future<List<CourseResponse>> getCourses() async {
    String token = await this._preferencesHelper.getToken();

    List<Map<String, dynamic>> response = await _httpClient
        .get(ApiUrls.CoursesApi, {}, {'Authorization': 'Bearer $token'});

    // If no Response, return Null
    if (response == null) return null;

    // Decode the data
    List<CourseResponse> availableCourses = [];
    response.forEach((element) {
      availableCourses.add(CourseResponse.fromJson(element));
    });

    // Return the decoded response
    return availableCourses;
  }
}
