import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/network/http_client/api_client.dart';
import 'package:smarty/request/lifter_register_request/lifter_register_request.dart';
import 'package:smarty/response/lifter_register_response/lifter_register_response.dart';

@provide
class LifterRegisterRepository {
  final ApiClient _apiClient;

  LifterRegisterRepository(this._apiClient);

  Future<LifterRegisterResponse> registerWithLifter(
      LifterRegisterRequest lifterRegisterRequest, String token) async {
    List<Map> response = await _apiClient
        .post(ApiUrls.lifterKeysApi, lifterRegisterRequest.toJson(), {
      'X-LLMS-CONSUMER-KEY': 'ck_b609eed82e78d32692f2ff07f0196167f555fc8d',
      'X-LLMS-CONSUMER-SECRET': 'cs_6d3f90922d4cbe1746d24140941dea257e054067'
    });

    if (response == null) {
      return null;
    }

    return LifterRegisterResponse.fromJson(response[0]);
  }
}
