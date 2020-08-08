import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/network/http_client/api_client.dart';
import 'package:smarty/response/register/register.dart';

@provide
class RegisterRepository {
  final ApiClient _apiClient;

  RegisterRepository(this._apiClient);

  Future<RegisterResponse> registerByCredentials(
      String email, String password) async {
    List<Map<String, dynamic>> apiResponse = await _apiClient.post(
      ApiUrls.registerApi,
      {
        'email': email,
        'password': password,
        'AUTH_KEY': 'THISISMySpeCiaLAUthCode02'
      },
    );

    if (apiResponse == null) {
      return null;
    }

    RegisterResponse registerResponse =
        RegisterResponse.fromJson(apiResponse[0]);

    return registerResponse;
  }

}
