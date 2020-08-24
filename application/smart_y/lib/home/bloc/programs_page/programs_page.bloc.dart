
import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/rxdart.dart';
import 'package:smarty/home/model/program/program_model.dart';
import 'package:smarty/home/service/programs_page/programs_page.service.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class ProgramsPageBloc{
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_FETCHING_DATA = 566;
  static const int STATUS_CODE_FETCHING_DATA_ERROR = 458;
  static const int STATUS_CODE_FETCHING_DATA_SUCCESS = 758;

  final String tag = 'CProgramsPageBloc';

  ProgramsService _programsService;
  final Logger _logger;

  ProgramsPageBloc(this._programsService,this._logger);

  PublishSubject<Pair<int , List<ProgramModel>>> _programsSubject =
      new PublishSubject();

  Stream<Pair<int, List<ProgramModel>>> get programsStateObservable =>
      _programsSubject.stream;

  getPrograms(){
    _programsSubject.add(Pair(STATUS_CODE_FETCHING_DATA, null));
    _programsService.getPrograms().then((result) {
      if (result != null) {
        _programsSubject.add(Pair(STATUS_CODE_FETCHING_DATA_SUCCESS, result));
        _logger.info(tag, 'Data Fetched Correctly');
      } else {
        _programsSubject.add(Pair(STATUS_CODE_FETCHING_DATA_ERROR, null));
        _logger.error(tag, "Error Getting the Data");
      }
    });
  }
}