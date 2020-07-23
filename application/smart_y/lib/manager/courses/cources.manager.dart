import 'package:analyzer_plugin/protocol/protocol.dart';
import 'package:inject/inject.dart';
import 'package:smarty/model/course/course_list_item.model.dart';
import 'package:smarty/repository/courses_page/courses_page.repository.dart';

@provide
class CoursesManager{
  CoursesRepository _coursesRepository;
  CoursesManager(this._coursesRepository);

  Future<List<CourseListItem>> getCourses()async{
    var response = await _coursesRepository.getCourses();
     return response;
  }
}