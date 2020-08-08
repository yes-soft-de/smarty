import 'package:inject/inject.dart';
import 'package:smarty/repository/register/register.dart';
import 'package:smarty/response/register/register.dart';

@provide
class RegisterManager {
  final RegisterRepository _repository;

  RegisterManager(this._repository);

  Future<RegisterResponse> register(email, password) {
    return this._repository.registerByCredentials(email, password);
  }
}