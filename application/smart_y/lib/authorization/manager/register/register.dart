import 'package:inject/inject.dart';
import 'package:smarty/authorization/repository/register/register.dart';
import 'package:smarty/authorization/request/register_request/register_request.dart';
import 'package:smarty/authorization/response/register/register.dart';

@provide
class RegisterManager {
  final RegisterRepository _repository;

  RegisterManager(this._repository);

  Future<RegisterResponse> register(RegisterRequest registerRequest) {
    return this._repository.registerByCredentials(registerRequest);
  }
}