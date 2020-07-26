import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';

import 'package:smarty/network/http_client/http_client.dart';
import 'package:smarty/response/course_response/course_response.dart';

@provide
class CoursesRepository {
  HttpClient _httpClient;

  CoursesRepository(this._httpClient);

  Future<List<CourseResponse>> getCourses() async {
    dynamic response = await _httpClient.get(ApiUrls.CoursesApi);

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
