
import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/courses/response/course_response/course_response.dart';
import 'package:smarty/network/http_client/api_client.dart';

@provide
class MeditationRepository {
  ApiClient _httpClient;

  MeditationRepository(this._httpClient);

  Future< List<CourseResponse> > getMeditation() async{

    dynamic response = await this._httpClient
        .get(ApiUrls.MeditationsApi,{},{});

    // If no Response, return Null
    if (response == null) return null;


    dynamic res = response[0]['courses'];
    // Decode the data
    List<CourseResponse> availableMeditations = [];
    res.forEach((element) {
      availableMeditations.add(CourseResponse.fromJson(element["data"]));
    });

    // Return the decoded response
    return availableMeditations;
  }
}