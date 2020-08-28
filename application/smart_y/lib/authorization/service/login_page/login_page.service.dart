import 'package:inject/inject.dart';
import 'package:smarty/authorization/manager/login/login.manager.dart';
import 'package:smarty/authorization/request/login_page/login_request.dart';
import 'package:smarty/authorization/response/login_page/login.response.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class LoginService {
  static const TAG = "LoginService";

  SharedPreferencesHelper _preferencesHelper;
  LoginManager _loginManager;
  Logger _logger;

  LoginService(this._loginManager, this._preferencesHelper, this._logger);

  /// Returns True if credentials are OK, otherwise returns False
  Future<bool> login(String email, String password) async {
    LoginResponse loginResponse =
        await _authorizeWithWordPress(email, password);

    if (loginResponse == null) {
      return false;
    }

    await _preferencesHelper.setToken(loginResponse.data.jwt);

    return true;
  }

  Future<bool> refreshToken() async {
    String email = await _preferencesHelper.getUserEmail();
    String password = await _preferencesHelper.getUserPassword();

    return login(email, password);
  }

  Future<LoginResponse> _authorizeWithWordPress(
      String email, String password) async {
    this._logger.info(TAG, 'Requesting Login');
    LoginResponse loginResponse = await _loginManager
        .login(LoginRequest(username: email, password: password));

    if (loginResponse == null) return null;

    if (!loginResponse.success) {
      return null;
    }

    // Cache the Token
    await _preferencesHelper.setToken(loginResponse.data.jwt);
    await _preferencesHelper.setUserEmail(email);
    await _preferencesHelper.setUserPassword(password);

    return loginResponse;
  }
}
