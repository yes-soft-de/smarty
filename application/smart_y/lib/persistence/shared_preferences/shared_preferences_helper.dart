import 'package:inject/inject.dart';
import 'package:shared_preferences/shared_preferences.dart';

@singleton
@provide
class SharedPreferencesHelper {
  static const String _KEY_TOKEN = "token";
  static const String _KEY_PASSWORD = 'password';
  static const String _KEY_EMAIL = 'email';
  static const String _KEY_USERNAME = 'userName';

  SharedPreferences _sharedPreferences;

  SharedPreferencesHelper() {
    SharedPreferences.getInstance().then((value) {
      _sharedPreferences = value;
    });
  }

  Future<void> setToken(String token) async {
    await _sharedPreferences.setString(_KEY_TOKEN, token);
  }

  Future<String> getToken() async {
    return _sharedPreferences.getString(_KEY_TOKEN);
  }

  Future<void> setUserEmail(String email) async {
    return _sharedPreferences.setString(_KEY_EMAIL, email);
  }

  //now it's not return in the response of login
  Future<void> setUserName(String userName) async {
    return _sharedPreferences.setString(_KEY_USERNAME, userName);
  }

  Future<void> setUserPassword(String password) async {
    return _sharedPreferences.setString(_KEY_PASSWORD, password);
  }

  String getUserEmail() {
    dynamic res = _sharedPreferences.getString(_KEY_EMAIL);
    return (res == null) ? 'johnDoe@wow.com' : res;
  }

  Future<String> getUserName() async {
    return _sharedPreferences.getString(_KEY_USERNAME);
  }

  Future<String> getUserPassword() async {
    return _sharedPreferences.getString(_KEY_PASSWORD);
  }

  Future<void> setUserId(String userId) async {
    return _sharedPreferences.setString('userId', userId);
  }

  Future<String> getUserId() async {
    return _sharedPreferences.getString('userId');
  }
}
