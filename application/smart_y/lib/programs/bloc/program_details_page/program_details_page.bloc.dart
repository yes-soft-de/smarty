
import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/rxdart.dart';
import 'package:smarty/programs/model/program_details_model/program_details_model.dart';
import 'package:smarty/programs/service/program_details_page/program_details_page.service.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class ProgramDetailsPageBloc{
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_FETCHING_DATA = 566;
  static const int STATUS_CODE_FETCHING_DATA_ERROR = 458;
  static const int STATUS_CODE_FETCHING_DATA_SUCCESS = 758;

  final String tag = 'CProgramsPageBloc';

  ProgramDetailsService _programDetailsService;
  final Logger _logger;

  ProgramDetailsPageBloc(this._programDetailsService,this._logger);

  PublishSubject<Pair<int , ProgramDetailsModel>> _programdetailsSubject =
  new PublishSubject();

  Stream<Pair<int, ProgramDetailsModel>> get programdetailsStateObservable =>
      _programdetailsSubject.stream;

  getProgramDetails(int programId ){

    _programdetailsSubject.add(Pair(STATUS_CODE_FETCHING_DATA, null));

    _programDetailsService.getProgramDetails(programId).then((result) {
      if (result != null) {

        _programdetailsSubject.add(Pair(STATUS_CODE_FETCHING_DATA_SUCCESS, result));
        _logger.info(tag, 'Data Fetched Correctly');
      } else {

        _programdetailsSubject.add(Pair(STATUS_CODE_FETCHING_DATA_ERROR, null));
        _logger.error(tag, "Error Getting the Data");
      }
    });
  }
}