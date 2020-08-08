import 'package:inject/inject.dart';
import 'package:smarty/repository/login/login_page.repository.dart';
import 'package:smarty/request/login_page/login_request.dart';
import 'package:smarty/response/login_page/login.response.dart';

@provide
class LoginManager {
  LoginRepository _loginRepository;

  LoginManager(this._loginRepository);

  Future<LoginResponse> login(LoginRequest loginRequest) {
    return _loginRepository.login(loginRequest);
  }
}