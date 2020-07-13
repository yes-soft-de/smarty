import 'package:dio/dio.dart';
import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/network/http_client/http_client.dart';
import 'package:smarty/request/login_page/login_request.dart';
import 'package:smarty/response/login_page/login.response.dart';

@provide
class LoginRepository {
  HttpClient _httpClient;
  
  LoginRepository(this._httpClient);

  /// login from API, and decode the result
  /// Return LoginResponse when Success, and Null otherwise
  Future<LoginResponse> login(LoginRequest loginRequest) async {
    Response response  = await _httpClient.post(ApiUrls.LoginApi, loginRequest.toJson());

    if (response == null) {
      return null;
    }

    return LoginResponse.fromJson(response.data);
  }
}