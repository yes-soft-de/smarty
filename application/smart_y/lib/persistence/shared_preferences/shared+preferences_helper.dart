import 'package:inject/inject.dart';
import 'package:shared_preferences/shared_preferences.dart';


@singleton
@provide
class SharedPreferencesHelper {
  // This is were we cache the small info like tokens
  // Other data will use an ORM, but caching to SQLite is not
  // what we are looking for now

  // INFO: Always use Keys as a static consts, this way we avoid typos
  static const String _KEY_TOKEN = "token";

  Future<void> setToken(String token) async {
    SharedPreferences _sharedPreferences = await SharedPreferences.getInstance();
    await _sharedPreferences.setString(_KEY_TOKEN, token);
  }

  Future<String> getToken() async {
    SharedPreferences _sharedPreferences = await SharedPreferences.getInstance();
    return _sharedPreferences.getString(_KEY_TOKEN);
  }
}