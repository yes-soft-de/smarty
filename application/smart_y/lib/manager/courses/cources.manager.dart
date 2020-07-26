import 'package:analyzer_plugin/protocol/protocol.dart';
import 'package:inject/inject.dart';
import 'package:smarty/model/course/course_list_item.model.dart';
import 'package:smarty/repository/courses_page/courses_page.repository.dart';
import 'package:smarty/response/course_response/course_response.dart';

@provide
class CoursesManager {
  CoursesRepository _coursesRepository;

  CoursesManager(this._coursesRepository);

  Future<List<CourseResponse>> getCourses() async {
    return this._coursesRepository.getCourses();
  }
}
