import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/rxdart.dart';
import 'package:smarty/courses/model/course_model/course_details.dart';
import 'package:smarty/meditation/model/meditation_details.dart';
import 'package:smarty/meditation/service/meditation_details_page/meditation_details_page.service.dart';
import 'package:smarty/utils/logger/logger.dart';


@provide
class MeditationDetailsBloc{
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_FETCHING_DATA = 566;
  static const int STATUS_CODE_FETCHING_DATA_ERROR = 458;
  static const int STATUS_CODE_FETCHING_DATA_SUCCESS = 758;

  final String tag='MeditationDetailsBloc';

  MeditationDetailsService _meditationDetailsService;
  Logger _logger;

  MeditationDetailsBloc(this._logger,this._meditationDetailsService);
  PublishSubject<Pair<int,MeditationDetails>> _meditationDetailsSubject = new PublishSubject();

  Stream<Pair<int ,MeditationDetails>> get meditationDetailsStateObservable =>  _meditationDetailsSubject.stream;

  getMeditationDetails(int meditationId){
    _meditationDetailsSubject.add(Pair(STATUS_CODE_FETCHING_DATA, null));
    _meditationDetailsService.getMeditationDetails(meditationId).then((result) {
      if (result != null) {
        _meditationDetailsSubject.add(Pair(STATUS_CODE_FETCHING_DATA_SUCCESS, result));
        _logger.info(tag, 'Data Fetched Correctly');
      } else {
        _meditationDetailsSubject.add(Pair(STATUS_CODE_FETCHING_DATA_ERROR, null));
        _logger.error(tag, "Error Getting the Data");
      }
    });
  }
}