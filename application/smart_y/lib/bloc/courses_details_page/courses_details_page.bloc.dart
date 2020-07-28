

import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/rxdart.dart';
import 'package:smarty/service/course_details_page/course_details_page.service.dart';
import 'package:smarty/utils/logger/logger.dart';
import 'package:smarty/model/section/secction.dart';

@provide
class CourseDetailsBloc{
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_FETCHING_DATA = 566;
  static const int STATUS_CODE_FETCHING_DATA_ERROR = 458;
  static const int STATUS_CODE_FETCHING_DATA_SUCCESS = 758;

  final String tag='CourseDetailBloc';

  CourseDetailsService _courseDetailsService;
  Logger _logger;

  CourseDetailsBloc(this._logger,this._courseDetailsService);
  PublishSubject<Pair<int, List<Section>>> _courseDetailsSubject = new PublishSubject();

  Stream<Pair<int , List<Section>>> get courseDetailsStateObservable =>  _courseDetailsSubject.stream;

  getCourseDetails(int courseId){
    _courseDetailsSubject.add(Pair(STATUS_CODE_FETCHING_DATA, null));
    _courseDetailsService.getCourseDetails(courseId).then((result) {
      if (result != null) {
        _courseDetailsSubject.add(Pair(STATUS_CODE_FETCHING_DATA_SUCCESS, result));
        _logger.info(tag, 'Data Fetched Correctly');
      } else {
        _courseDetailsSubject.add(Pair(STATUS_CODE_FETCHING_DATA_ERROR, null));
        _logger.error(tag, "Error Getting the Data");
      }
    });
  }
}