import 'package:inject/inject.dart';
import 'package:smarty/authorization/manager/lifter_register_manager/lifter_register_manager.dart';
import 'package:smarty/authorization/manager/login/login.manager.dart';
import 'package:smarty/authorization/manager/register/register.dart';
import 'package:smarty/authorization/request/lifter_register_request/lifter_register_request.dart';
import 'package:smarty/authorization/request/login_page/login_request.dart';
import 'package:smarty/authorization/request/register_request/register_request.dart';
import 'package:smarty/authorization/response/lifter_register_response/lifter_register_response.dart';
import 'package:smarty/authorization/response/login_page/login.response.dart';
import 'package:smarty/authorization/response/register/register.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class RegisterService {
  static const TAG = 'RegisterService';

  final RegisterManager _registerManager;
  final LoginManager _loginManager;
  final LifterRegisterManager _lifterRegisterManager;
  final Logger _logger;
  final SharedPreferencesHelper _preferencesHelper;

  RegisterService(
      this._logger,
      this._registerManager,
      this._preferencesHelper,
      this._loginManager,
      this._lifterRegisterManager);

  Future<bool> register(RegisterRequest registerRequest) async {
    RegisterResponse registerResponse = await _registerNewUserInWordPress(registerRequest);

    if (registerResponse == null) {
      return false;
    }

    LoginResponse loginResponse = await _authorizeWithWordPress(registerRequest.email, registerRequest.password);

    if (loginResponse == null) {
      return false;
    }

    await _preferencesHelper.setToken(loginResponse.data.jwt);

    return true;
  }

  Future<RegisterResponse> _registerNewUserInWordPress(RegisterRequest registerRequest) async {
    RegisterResponse registerResponse =
        await this._registerManager.register(registerRequest);

    if (registerResponse == null) {
      this._logger.info(TAG, 'Null Register Response');
      return null;
    }

    if (!registerResponse.success) {
      this._logger.info(TAG, 'Null Register Not SuccessFull');
      return null;
    }

    return registerResponse;
  }

  Future<LoginResponse> _authorizeWithWordPress(String email, String password) async {
    this._logger.info(TAG, 'Requesting Login');
    LoginResponse loginResponse = await _loginManager.login(LoginRequest(email: email, password: password));

    if (!loginResponse.success) {
      return null;
    }

    // Cache the Token
    await _preferencesHelper.setToken(loginResponse.data.jwt);

    return loginResponse;
  }
}
