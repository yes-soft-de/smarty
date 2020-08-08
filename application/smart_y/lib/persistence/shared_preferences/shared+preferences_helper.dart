import 'package:inject/inject.dart';
import 'package:shared_preferences/shared_preferences.dart';

@singleton
@provide
class SharedPreferencesHelper {
  static const String _KEY_TOKEN = "token";
  static const String _KEY_USER_ID = 'uid';
  static const String _KEY_LIFTER_KEY = "lifter_key";
  static const String _KEY_LIFTER_SECRET = "lifter_secret";

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

  Future<void> setUserId(String userId) async {
    await _sharedPreferences.setString(_KEY_USER_ID, userId);
  }

  Future<String> getUserId() async {
    return _sharedPreferences.getString(_KEY_USER_ID);
  }

  Future<void> setLifterKey(String key) async {
    await _sharedPreferences.setString(_KEY_LIFTER_KEY, key);
  }

  Future<String> getLifterKey(String key) async {
    return _sharedPreferences.getString(_KEY_LIFTER_KEY);
  }

  Future<void> setLifterSecret(String secret) async {
    await _sharedPreferences.setString(_KEY_LIFTER_SECRET, secret);
  }

  Future<String> getLifterSecret() async {
    return _sharedPreferences.getString(_KEY_LIFTER_SECRET);
  }
}
