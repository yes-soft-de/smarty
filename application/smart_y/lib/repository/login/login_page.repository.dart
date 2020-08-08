import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/network/http_client/api_client.dart';
import 'package:smarty/request/login_page/login_request.dart';
import 'package:smarty/response/login_page/login.response.dart';

@provide
class LoginRepository {
  ApiClient _httpClient;

  LoginRepository(this._httpClient);

  /// login from API, and decode the result
  /// Return LoginResponse when Success, and Null otherwise
  Future<LoginResponse> login(LoginRequest loginRequest) async {

    List<Map<String, dynamic>> response =
        await _httpClient.post(ApiUrls.authApi, loginRequest.toJson());

    if (response == null) {
      return null;
    }

    return LoginResponse.fromJson(response[0]);
  }
}
