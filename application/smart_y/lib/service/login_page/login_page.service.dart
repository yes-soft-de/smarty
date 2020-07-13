import 'package:inject/inject.dart';
import 'package:smarty/manager/login/login.manager.dart';
import 'package:smarty/request/login_page/login_request.dart';
import 'package:smarty/response/login_page/login.response.dart';

@provide
class LoginService {
    LoginManager _loginManager;

    LoginService(this._loginManager);

    /// Returns True if credentials are OK, otherwise returns False
    Future<bool> login(String username, String password) async {
      LoginRequest loginRequest = LoginRequest(username: username, password: password);
      LoginResponse loginResponse = await _loginManager.login(loginRequest);

      if (loginResponse != null) {
        // TODO: Cache the Token and the response

        return true;
      }

      return false;
    }
}