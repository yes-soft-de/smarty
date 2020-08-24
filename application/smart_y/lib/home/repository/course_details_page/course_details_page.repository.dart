import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/home/response/course_details_response/course_details_response.dart';
import 'package:smarty/network/http_client/api_client.dart';
import 'package:smarty/home/response/lesson_response/lesson_response.dart';
import 'package:smarty/home/response/course_details_response/section_response.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';


@provide
class CourseDetailsRepository{

   ApiClient _httpClient;
   SharedPreferencesHelper _preferencesHelper;


   CourseDetailsRepository(this._httpClient,this._preferencesHelper);

   Future<CourseDetailsResponse> getCourseDetails(int courseId)async{
     dynamic response = await _httpClient.
     get(ApiUrls.CourseApi+'/$courseId',{},{});

     // If no Response, return Null
     if (response == null) return null;

     dynamic res = response[0];
     // Decode the data
     CourseDetailsResponse courseDetails = CourseDetailsResponse.fromJson(res["data"]);

     return courseDetails;
   }

 /*  Future<List<SectionResponse>> getCourseDetails(int courseId)async{


     String token = await this._preferencesHelper.getToken();

      dynamic  response = await _httpClient
          .get(ApiUrls.SectionsApi, {'parent':'$courseId'}, {'Authorization': 'Bearer $token'});

      // If no Response, return Null
      if (response == null) return null;

      // Decode the data
      List<SectionResponse> availableSections = [];
      response.forEach((element)async{
         availableSections.add(SectionResponse.fromJson(element));
         var sectionId= availableSections.last.id;

         List<LessonResponse> sectionLessons = await getSectionLessons(sectionId);

         // In case sections should be contain lessons
        if (sectionLessons == null || sectionLessons.length==0) {
          availableSections.last.lessons =new List<LessonResponse>();
        }
        else {
          availableSections.last.lessons =sectionLessons;
        }


      });

      // Return the decoded response
      return availableSections;

   }

   Future<List<LessonResponse>> getSectionLessons(int sectionId)async{
      String token = await this._preferencesHelper.getToken();


      dynamic response = await _httpClient.get(ApiUrls.LessonsApi,{'parent':'$sectionId'},{'Authorization': 'Bearer $token'});


      // If no Response, return Null
      if (response == null  ) return null;

      //Decode the data
      List<LessonResponse> availableLessons = [];
      if(response.toString()!='[]'){

        response.forEach((element){
          availableLessons.add(LessonResponse.fromJson(element));
        });


      }

      // Return the decoded response
      return availableLessons;
   }
*/




}