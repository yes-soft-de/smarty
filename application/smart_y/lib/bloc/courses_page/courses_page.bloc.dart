import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/subjects.dart';
import 'package:smarty/model/course/course_list_item.model.dart';
import 'package:smarty/service/courses_page/courses_page.service.dart';

//State Management for courses page
class CoursesPageBloc{
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_FETCHING_DATA = 566;
  static const int STATUS_CODE_FETCHING_DATA_ERROR = 458;
  static const int STATUS_CODE_FETCHING_DATA_SUCCESS = 758;

  CoursesService _coursesService;
  List<CourseListItem> courses;
  CoursesPageBloc(this._coursesService);

  PublishSubject<Pair<int, dynamic>> _coursesSubject = new PublishSubject();
  Stream<Pair<int, dynamic>> get loginStateObservable => _coursesSubject.stream;

  getCourses(){

    _coursesSubject.add(Pair(STATUS_CODE_FETCHING_DATA, null));
    _coursesService.getCourses().then((result) {
      if (result != null ) {
        courses = result;
        _coursesSubject.add(Pair(STATUS_CODE_FETCHING_DATA_SUCCESS, null));
      } else {
        print('fetching courses failed');
        _coursesSubject.add(Pair(STATUS_CODE_FETCHING_DATA_ERROR,
            "Error Fetching data, Please Try again Later"));
      }
    });
  }


}