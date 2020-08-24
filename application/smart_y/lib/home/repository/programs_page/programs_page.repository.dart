
import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/home/response/course_details_response/course_details_response.dart';
import 'package:smarty/home/response/course_response/course_response.dart';
import 'package:smarty/network/http_client/api_client.dart';

@provide
class ProgramsRepository{
  ApiClient _httpClient;
  ProgramsRepository(this._httpClient);

  Future<List<CourseDetailsResponse>> getPrograms() async{
  dynamic response = _httpClient.get(ApiUrls.ProgramsApi,{},{});

  // If no Response, return Null
  if(response == null)
    return null;
  dynamic res = response[0]['courses'];
  // Decode the data
  List<CourseDetailsResponse> availablePrograms = [];
  res.forEach((element)async {
    int programId = element["data"]["id"];

    CourseDetailsResponse program = await getProgramWithDescription(programId);

    availablePrograms.add(
        program
    );
  });

  return availablePrograms;
  }

  Future<CourseDetailsResponse> getProgramWithDescription(int programId) async{
    dynamic response = await _httpClient.
    get(ApiUrls.CoursesApi+'/$programId',{},{});

    // If no Response, return Null
    if (response == null) return null;

    dynamic res = response[0];
    // Decode the data
    CourseDetailsResponse programDetails = CourseDetailsResponse.fromJson(res["data"]);

    return programDetails;

  }

}