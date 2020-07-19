import 'package:analyzer_plugin/utilities/pair.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/subjects.dart';
import 'package:smarty/service/login_page/login_page.service.dart';

// State Mangment for Login Page
@provide
class LoginPageBloc {
  // Status Indicators, Random ints are used here
  static const int STATUS_CODE_INIT = -1;
  static const int STATUS_CODE_CREDENTIALS_SENT = 566;
  static const int STATUS_CODE_AUTH_ERROR = 458;
  static const int STATUS_CODE_AUTH_SUCCESS = 758;

  // DI Section
  LoginService _loginService;
  LoginPageBloc(this._loginService);

  // Observables Section
  /// The Pair Represent the Bloc, But more general
  PublishSubject<Pair<int, dynamic>> _loginSubject = new PublishSubject();
  Stream<Pair<int, dynamic>> get loginStateObservable => _loginSubject.stream;

  login(String username, String password) {
    _loginSubject.add(Pair(STATUS_CODE_CREDENTIALS_SENT, null));
    _loginService.login(username, password).then((loginSuccess) {
      if (loginSuccess) {
        // This Might be used later to indicate an error message
        // The important Part is the first paramter, the second is optional.
        // I use it for generalized service response models
        _loginSubject.add(Pair(STATUS_CODE_AUTH_SUCCESS, null));
      } else {
        print('login failed');
        _loginSubject.add(Pair(STATUS_CODE_AUTH_ERROR,
            "Error Signing in, Please Try again Later"));
      }
    });
  }
}
