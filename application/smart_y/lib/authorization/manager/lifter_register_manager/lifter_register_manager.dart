import 'package:inject/inject.dart';
import 'package:smarty/authorization/repository/lifter_register/lifter_register_repository.dart';
import 'package:smarty/authorization/request/lifter_register_request/lifter_register_request.dart';
import 'package:smarty/authorization/response/lifter_register_response/lifter_register_response.dart';

@provide
class LifterRegisterManager {
  final LifterRegisterRepository _repository;

  LifterRegisterManager(this._repository);

  Future<LifterRegisterResponse> register(LifterRegisterRequest lifterRegisterRequest, String token) {
    return _repository.registerWithLifter(lifterRegisterRequest, token);
  }
}