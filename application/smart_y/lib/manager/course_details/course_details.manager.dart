

import 'package:inject/inject.dart';
import 'package:smarty/repository/course_details_page/course_details_page.repository.dart';
import 'package:smarty/response/course_details_response/section_response.dart';

@provide
class CourseDetailManager{

  CourseDetailsRepository _courseDetailsRepository;

  CourseDetailManager(this._courseDetailsRepository);

  Future<List<SectionResponse>> getCourseDetails(int courseId)async{
    return this._courseDetailsRepository.getCourseDetails(courseId);
  }
}