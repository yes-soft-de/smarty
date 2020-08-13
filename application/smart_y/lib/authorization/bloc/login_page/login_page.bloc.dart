import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/subjects.dart';
import 'package:smarty/authorization/service/login_page/login_page.service.dart';
import 'package:smarty/utils/logger/logger.dart';

// State Management for Login Page
@provide
class LoginPageBloc {
  static const String tag = 'Login Service';

  // Status Indicators, Random ints are used here
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_CREDENTIALS_SENT = 566;
  static const int STATUS_CODE_AUTH_ERROR = 458;
  static const int STATUS_CODE_AUTH_SUCCESS = 758;

  // DI Section
  LoginService _loginService;
  Logger _logger;
  LoginPageBloc(this._loginService, this._logger);

  // Observables Section
  /// The Pair Represent the Bloc, But more general
  PublishSubject<Pair<int, dynamic>> _loginSubject = new PublishSubject();
  Stream<Pair<int, dynamic>> get loginStateObservable => _loginSubject.stream;

  login(String username, String password) {

    _loginSubject.add(Pair(STATUS_CODE_CREDENTIALS_SENT, null));

    _loginService.login(username, password).then((loginSuccess) {
      if (loginSuccess) {
        _loginSubject.add(Pair(STATUS_CODE_AUTH_SUCCESS, null));
      } else {
        this._logger.warn(tag, 'login failed');
        _loginSubject.add(Pair(STATUS_CODE_AUTH_ERROR,
            "Error Signing in, Please Try again Later"));
      }
    });
  }
}
