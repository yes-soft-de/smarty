
import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/rxdart.dart';
import 'package:smarty/courses/model/lesson/lesson.dart';
import 'package:smarty/courses/service/lesson_page/lesson_page.service.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class LessonPageBloc{
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_FETCHING_DATA = 566;
  static const int STATUS_CODE_FETCHING_DATA_ERROR = 458;
  static const int STATUS_CODE_FETCHING_DATA_SUCCESS = 758;

  final String tag = 'LessonPageBloc';

  LessonService _lessonService;
  final Logger _logger;

  LessonPageBloc(this._lessonService,this._logger);

  PublishSubject<Pair<int, Lesson>> _lessonSubject =
  new PublishSubject();

  Stream<Pair<int, Lesson>> get lessonStateObservable =>
      _lessonSubject.stream;

  getLesson(int lessonId) {
    _lessonSubject.add(Pair(STATUS_CODE_FETCHING_DATA, null));
    _lessonService.getLesson(lessonId).then((result) {
      if (result != null) {
        _lessonSubject.add(Pair(STATUS_CODE_FETCHING_DATA_SUCCESS, result));
        _logger.info(tag, 'Data Fetched Correctly');
      } else {
        _lessonSubject.add(Pair(STATUS_CODE_FETCHING_DATA_ERROR, null));
        _logger.error(tag, "Error Getting the Data");
      }
    });
  }
}