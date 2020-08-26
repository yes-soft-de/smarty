

import 'package:inject/inject.dart';
import 'package:smarty/courses/repository/course_details_page/course_details_page.repository.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';

@provide
class CourseDetailManager{

  CourseDetailsRepository _courseDetailsRepository;

  CourseDetailManager(this._courseDetailsRepository);

  Future</*List<SectionResponse>*/CourseDetailsResponse> getCourseDetails(int courseId)async{
    return await this._courseDetailsRepository.getCourseDetails(courseId);
  }
}