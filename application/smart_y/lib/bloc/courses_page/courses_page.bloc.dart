import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/subjects.dart';
import 'package:smarty/model/course/course_list_item.model.dart';
import 'package:smarty/service/courses_page/courses_page.service.dart';
import 'package:smarty/utils/logger/logger.dart';

//State Management for courses page
@provide
class CoursesPageBloc {
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_FETCHING_DATA = 566;
  static const int STATUS_CODE_FETCHING_DATA_ERROR = 458;
  static const int STATUS_CODE_FETCHING_DATA_SUCCESS = 758;

  final String tag = 'CoursesPageBloc';

  final CoursesService _coursesService;
  final Logger _logger;

  CoursesPageBloc(this._coursesService, this._logger);

  PublishSubject<Pair<int, List<CourseModel>>> _coursesSubject =
      new PublishSubject();

  Stream<Pair<int, List<CourseModel>>> get loginStateObservable =>
      _coursesSubject.stream;

  getCourses() {
    _coursesSubject.add(Pair(STATUS_CODE_FETCHING_DATA, null));
    _coursesService.getCourses().then((result) {
      if (result != null) {
        _coursesSubject.add(Pair(STATUS_CODE_FETCHING_DATA_SUCCESS, result));
        _logger.info(tag, 'Data Fetched Correctly');
      } else {
        _coursesSubject.add(Pair(STATUS_CODE_FETCHING_DATA_ERROR, null));
        _logger.error(tag, "Error Getting the Data");
      }
    });
  }
}
