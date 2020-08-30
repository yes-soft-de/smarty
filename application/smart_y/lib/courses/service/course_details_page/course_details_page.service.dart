import 'package:inject/inject.dart';
import 'package:smarty/courses/manager/course_details/course_details.manager.dart';
import 'package:smarty/courses/model/course_model/course_details.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';
import 'package:smarty/utils/filter/courrse_sections_filter/course_section_filter.dart';

@provide
class CourseDetailsService {
  CourseDetailManager _courseDetailManager;

  CourseDetailsService(
      this._courseDetailManager);

  Future</*List<Section>*/ CourseDetails> getCourseDetails(int courseId) async {
//    List<SectionResponse> sectionResponse =
//        await _courseDetailManager.getCourseDetails(courseId);
    CourseDetailsResponse courseDetails =
    await _courseDetailManager.getCourseDetails(courseId);

    if (courseDetails == null) {
      return null;
    }

    return new CourseDetails(name: courseDetails.course.name,
      price: courseDetails.course.price,description: courseDetails.description,
      sections: CourseSectionsFilter.getSections(courseDetails.curriculum)
    );


//    List<Section> courseSections = [];
//    sectionResponse.forEach((element) {
//
//      List<Lesson> sectionLessons = [];
//      if(element.lessons!=null ){
//        element.lessons.forEach((el) {
//          sectionLessons.add(Lesson(id: el.id, title: el.title.rendered));
//        });
//      }
//
//
//      courseSections.add(Section(
//          id: element.id,
//          title: element.title.rendered,
//          lessons: sectionLessons));
//    });
//
//    return courseSections;
  }
}
