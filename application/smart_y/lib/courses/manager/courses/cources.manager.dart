import 'package:inject/inject.dart';
import 'package:smarty/courses/repository/courses_page/courses_page.repository.dart';
import 'package:smarty/courses/response/course_response/course_response.dart';

@provide
class CoursesManager {
  CoursesRepository _coursesRepository;

  CoursesManager(this._coursesRepository);

  Future<List<CourseResponse>> getCourses() async {
    return this._coursesRepository.getCourses();
  }
}
