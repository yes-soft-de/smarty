import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/network/http_client/api_client.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';


@provide
class MeditationDetailsRepository {

  ApiClient _httpClient;
  SharedPreferencesHelper _preferencesHelper;


  MeditationDetailsRepository(this._httpClient, this._preferencesHelper);

  Future<CourseDetailsResponse> getMeditationDetails(int meditationId) async {
    dynamic response = await _httpClient.
    get(ApiUrls.CourseApi + '/$meditationId', {}, {});

    // If no Response, return Null
    if (response == null) return null;

    dynamic res = response[0];
    // Decode the data
    CourseDetailsResponse meditationDetails = CourseDetailsResponse.fromJson(
        res["data"]);

    return meditationDetails;
  }
}