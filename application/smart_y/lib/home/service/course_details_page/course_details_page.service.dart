import 'package:inject/inject.dart';
import 'package:smarty/home/manager/course_details/course_details.manager.dart';
import 'package:smarty/home/model/lesson/lesson.dart';
import 'package:smarty/home/model/section/secction.dart';
import 'package:smarty/home/response/course_details_response/section_response.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';

@provide
class CourseDetailsService {
  CourseDetailManager _courseDetailManager;

  CourseDetailsService(
      this._courseDetailManager);

  Future<List<Section>> getCourseDetails(int courseId) async {
    List<SectionResponse> sectionResponse =
        await _courseDetailManager.getCourseDetails(courseId);

    if (sectionResponse == null) {
      return null;
    }


    List<Section> courseSections = [];
    sectionResponse.forEach((element) {

      List<Lesson> sectionLessons = [];
      if(element.lessons!=null ){
        element.lessons.forEach((el) {
          sectionLessons.add(Lesson(id: el.id, title: el.title.rendered));
        });
      }


      courseSections.add(Section(
          id: element.id,
          title: element.title.rendered,
          lessons: sectionLessons));
    });

    return courseSections;
  }
}
