import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/courses/response/course_response/course_response.dart';
import 'package:smarty/network/http_client/api_client.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';

@provide
class CoursesRepository {
  ApiClient _httpClient;
  SharedPreferencesHelper _preferencesHelper;

  CoursesRepository(this._httpClient, this._preferencesHelper);

  Future<List<CourseResponse>> getCourses() async {
//    String token = await this._preferencesHelper.getToken();

    dynamic response = await _httpClient
        .get(ApiUrls.CoursesApi, {}, {/*'Authorization': 'Bearer $token'*/});

    // If no Response, return Null
    if (response == null) return null;


    dynamic res = response[0]['courses'];
    // Decode the data
    List<CourseResponse> availableCourses = [];
    res.forEach((element) {
      availableCourses.add(CourseResponse.fromJson(element["data"]));
    });

    // Return the decoded response
    return availableCourses;
  }
}
