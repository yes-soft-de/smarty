import 'package:inject/inject.dart';
import 'package:smarty/authorization/repository/register/register.dart';
import 'package:smarty/authorization/response/register/register.dart';

@provide
class RegisterManager {
  final RegisterRepository _repository;

  RegisterManager(this._repository);

  Future<RegisterResponse> register(email, password) {
    return this._repository.registerByCredentials(email, password);
  }
}