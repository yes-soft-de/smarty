import 'package:inject/inject.dart';
import 'package:smarty/authorization/manager/lifter_register_manager/lifter_register_manager.dart';
import 'package:smarty/authorization/manager/login/login.manager.dart';
import 'package:smarty/authorization/manager/register/register.dart';
import 'package:smarty/authorization/request/lifter_register_request/lifter_register_request.dart';
import 'package:smarty/authorization/request/login_page/login_request.dart';
import 'package:smarty/authorization/response/lifter_register_response/lifter_register_response.dart';
import 'package:smarty/authorization/response/login_page/login.response.dart';
import 'package:smarty/authorization/response/register/register.dart';
import 'package:smarty/persistence/shared_preferences/shared+preferences_helper.dart';
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

  Future<bool> register(String email, String password) async {
    RegisterResponse registerResponse = await _registerNewUserInWordPress(email, password);

    if (registerResponse == null) {
      return null;
    }

    LoginResponse loginResponse = await _authorizeWithWordPress(email, password);

    if (loginResponse == null) {
      return null;
    }

    _authorizeWithLifter(registerResponse.user.iD, loginResponse.data.jwt);

    return true;
  }

  Future<RegisterResponse> _registerNewUserInWordPress(String email, String password) async {
    RegisterResponse registerResponse =
        await this._registerManager.register(email, password);

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

  Future<dynamic> _authorizeWithLifter(String userId, String jwt) async {
    // Request Lifter END Point Access
    LifterRegisterResponse lifterResponse = await this._lifterRegisterManager.register(LifterRegisterRequest(
        userId: int.parse(userId),
        description: 'User Endpoint No.' + userId,
        permissions: 'read'
    ), jwt);
  }
}
