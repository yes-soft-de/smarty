import 'package:fluttertoast/fluttertoast.dart';
import 'package:inject/inject.dart';
import 'package:smarty/authorization/manager/register/register.dart';
import 'package:smarty/authorization/request/register_request/register_request.dart';
import 'package:smarty/authorization/response/register/register.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class RegisterService {
  static const TAG = 'RegisterService';

  final RegisterManager _registerManager;
  final Logger _logger;
  final SharedPreferencesHelper _preferencesHelper;

  RegisterService(this._logger, this._registerManager, this._preferencesHelper);

  Future<bool> register(RegisterRequest registerRequest) async {
    RegisterResponse registerResponse =
        await _registerNewUserInWordPress(registerRequest);

    if (registerResponse == null) {
      return false;
    }

    await _preferencesHelper.setToken(registerResponse.token.accessToken);
    await _preferencesHelper.setUserEmail(registerResponse.user.email);
    await _preferencesHelper.setUserPassword(registerRequest.password);
    await _preferencesHelper.setUserName(registerResponse.user.name);
    await _preferencesHelper.setUserId('${registerResponse.user.id}');

    return true;
  }

  Future<RegisterResponse> _registerNewUserInWordPress(
      RegisterRequest registerRequest) async {
    RegisterResponse registerResponse =
        await this._registerManager.register(registerRequest);

    if (registerResponse == null) {
      this._logger.info(TAG, 'Null Register Response');
      return null;
    }

    if (!registerResponse.status) {
      this._logger.info(TAG, 'Null Register Not SuccessFull');
      Fluttertoast.showToast(
          msg: registerResponse.message, toastLength: Toast.LENGTH_LONG);
      return null;
    }

    return registerResponse;
  }
}
