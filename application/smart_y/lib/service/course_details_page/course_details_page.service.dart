

import 'package:inject/inject.dart';
import 'package:smarty/manager/course_details/course_details.manager.dart';
import 'package:smarty/model/lesson/lesson.dart';
import 'package:smarty/model/section/secction.dart';
import 'package:smarty/persistence/shared_preferences/shared+preferences_helper.dart';
import 'package:smarty/response/course_details_response/section_response.dart';

@provide
class CourseDetailsService{
  SharedPreferencesHelper _sharedPreferencesHelper;
  CourseDetailManager _courseDetailManager;

  CourseDetailsService(this._courseDetailManager,this._sharedPreferencesHelper);

  Future<List<Section>> getCourseDetails(int courseId)async{
    List<SectionResponse> sectionResponse= await _courseDetailManager.getCourseDetails(courseId);

    if (sectionResponse == null) {
      return null;
    }


    List<Section> courseSections =[];
    sectionResponse.forEach((element) {

      List<Lesson> sectionLessons = [];
      element.lessons.forEach((el) {sectionLessons.add(Lesson(id: el.id,title: el.title.rendered)); });

      courseSections.add(Section(id: element.id,title: element.title.rendered,lessons:sectionLessons ));
    });

    return courseSections;
}


}