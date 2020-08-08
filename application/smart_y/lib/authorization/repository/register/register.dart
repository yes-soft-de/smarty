import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/authorization/request/register_request/register_request.dart';
import 'package:smarty/authorization/response/register/register.dart';
import 'package:smarty/network/http_client/api_client.dart';

@provide
class RegisterRepository {
  final ApiClient _apiClient;

  RegisterRepository(this._apiClient);

  Future<RegisterResponse> registerByCredentials(
      RegisterRequest registerRequest) async {
    registerRequest.authKey = 'THISISMySpeCiaLAUthCode02';
    List<Map<String, dynamic>> apiResponse = await _apiClient.post(
      ApiUrls.registerApi,
      registerRequest.toJson(),
    );

    if (apiResponse == null) {
      return null;
    }

    RegisterResponse registerResponse =
        RegisterResponse.fromJson(apiResponse[0]);

    return registerResponse;
  }
}
