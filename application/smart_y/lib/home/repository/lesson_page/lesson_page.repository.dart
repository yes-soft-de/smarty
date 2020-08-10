import 'package:inject/inject.dart';
import 'package:smarty/home/response/lesson_response/lesson_response.dart';
import 'package:smarty/network/http_client/api_client.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';

import '../../../ApiUrls.dart';

@provide
class LessonRepository{
    ApiClient _httpClient;
    SharedPreferencesHelper _preferencesHelper;

    LessonRepository(this._httpClient,this._preferencesHelper);

    Future<LessonResponse> getLesson(int lessonId)async{
      String token = await this._preferencesHelper.getToken();

      dynamic  response = await _httpClient
          .get(ApiUrls.SectionsApi+'?parent=$lessonId', {}, {'Authorization': 'Bearer $token'});

      // If no Response, return Null
      if (response == null) return null;

      LessonResponse lesson = LessonResponse.fromJson(response.body);

      // Return the decoded response
      return lesson;
    }
}