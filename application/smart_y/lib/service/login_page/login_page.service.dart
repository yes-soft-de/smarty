import 'package:analyzer_plugin/protocol/protocol.dart';
import 'package:inject/inject.dart';
import 'package:smarty/manager/login/login.manager.dart';
import 'package:smarty/persistence/shared_preferences/shared+preferences_helper.dart';
import 'package:smarty/request/login_page/login_request.dart';
import 'package:smarty/response/login_page/login.response.dart';

@provide
class LoginService {
    SharedPreferencesHelper _sharedPreferencesHelper;
    LoginManager _loginManager;

    LoginService(this._loginManager, this._sharedPreferencesHelper);

    /// Returns True if credentials are OK, otherwise returns False
    Future<bool> login(String email, String password) async {
      LoginRequest loginRequest = LoginRequest(email: email, password: password);
      LoginResponse loginResponse = await _loginManager.login(loginRequest);

      if (loginResponse != null) {
        await _sharedPreferencesHelper.setToken(loginResponse.data.jwt);

        return true;
      }

      return false;
    }
}