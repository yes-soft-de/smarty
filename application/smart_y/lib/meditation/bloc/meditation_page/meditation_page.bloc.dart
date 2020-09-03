import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/subjects.dart';
import 'package:smarty/courses/model/course/course_list_item.model.dart';
import 'package:smarty/meditation/service/meditation_Page/meditation_page_service.dart';
import 'package:smarty/utils/logger/logger.dart';

//State Management for courses page
@provide
class MeditationPageBloc {
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_FETCHING_DATA = 566;
  static const int STATUS_CODE_FETCHING_DATA_ERROR = 458;
  static const int STATUS_CODE_FETCHING_DATA_SUCCESS = 758;

  final String tag = 'MeditationPageBloc';

  final MeditationService _meditationService;
  final Logger _logger;

  MeditationPageBloc(this._meditationService, this._logger);

  PublishSubject<Pair<int, List<CourseModel>>> _meditationSubject =
  new PublishSubject();

  Stream<Pair<int, List<CourseModel>>> get meditationStateObservable =>
      _meditationSubject.stream;

  getMeditation() {
    _meditationSubject.add(Pair(STATUS_CODE_FETCHING_DATA, null));
    _meditationService.getMeditation().then((result) {
      if (result != null) {
        _meditationSubject.add(Pair(STATUS_CODE_FETCHING_DATA_SUCCESS, result));
        _logger.info(tag, 'Data Fetched Correctly');
      } else {
        _meditationSubject.add(Pair(STATUS_CODE_FETCHING_DATA_ERROR, null));
        _logger.error(tag, "Error Getting the Data");
      }
    });
  }
}
