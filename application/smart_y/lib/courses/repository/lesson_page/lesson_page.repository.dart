import 'package:inject/inject.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/courses/response/lesson_response/lesson_response.dart';
import 'package:smarty/network/http_client/api_client.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';

import '../../../ApiUrls.dart';

@provide
class LessonRepository{
    ApiClient _httpClient;
    SharedPreferencesHelper _preferencesHelper;

    LessonRepository(this._httpClient,this._preferencesHelper);

    Future<CourseDetailsResponse> getLesson(int lessonId)async{
//      String token = await this._preferencesHelper.getToken();

      dynamic  response = await _httpClient
          .get(ApiUrls.CourseApi+'/$lessonId', {}, { });

      // If no Response, return Null
      if (response == null) return null;

      dynamic res = response[0];
      // Decode the data
      CourseDetailsResponse lesson = CourseDetailsResponse.fromJson(res["data"]);


      // Return the decoded response
      return lesson;
    }
}