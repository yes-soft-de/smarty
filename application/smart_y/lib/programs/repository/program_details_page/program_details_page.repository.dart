
import 'package:inject/inject.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/network/http_client/api_client.dart';

import '../../../ApiUrls.dart';

@provide
class ProgramDetailsRepository{
  ApiClient _httpClient;
  ProgramDetailsRepository(this._httpClient);

  Future<CourseDetailsResponse> getProgramDetails(int programId)async{
    dynamic response = await _httpClient.
    get(ApiUrls.CourseApi+'/$programId',{},{});

    // If no Response, return Null
    if (response == null) return null;

    dynamic res = response[0];
    // Decode the data
    CourseDetailsResponse programDetails = CourseDetailsResponse.fromJson(res["data"]);

    return programDetails;
  }

}