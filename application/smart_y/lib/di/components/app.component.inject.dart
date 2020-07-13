import 'app.component.dart' as _i1;
import '../../utils/logger/logger.dart' as _i2;
import '../../network/http_client/http_client.dart' as _i3;
import 'dart:async' as _i4;
import '../../main.dart' as _i5;
import '../../ui/screen/login_page/login_page.dart' as _i6;
import '../../bloc/login_page/login_page.bloc.dart' as _i7;
import '../../service/login_page/login_page.service.dart' as _i8;
import '../../manager/login/login.manager.dart' as _i9;
import '../../repository/login_page/login_page.repository.dart' as _i10;

class AppComponent$Injector implements _i1.AppComponent {
  AppComponent$Injector._();

  _i2.Logger _singletonLogger;

  _i3.HttpClient _singletonHttpClient;

  static _i4.Future<_i1.AppComponent> create() async {
    final injector = AppComponent$Injector._();

    return injector;
  }

  _i5.MyApp _createMyApp() => _i5.MyApp(_createLoginPage());
  _i6.LoginPage _createLoginPage() =>
      _i6.LoginPage(_createLoginPageBloc(), _createLogger());
  _i7.LoginPageBloc _createLoginPageBloc() =>
      _i7.LoginPageBloc(_createLoginService());
  _i8.LoginService _createLoginService() =>
      _i8.LoginService(_createLoginManager());
  _i9.LoginManager _createLoginManager() =>
      _i9.LoginManager(_createLoginRepository());
  _i10.LoginRepository _createLoginRepository() =>
      _i10.LoginRepository(_createHttpClient());
  _i3.HttpClient _createHttpClient() =>
      _singletonHttpClient ??= _i3.HttpClient(_createLogger());
  _i2.Logger _createLogger() => _singletonLogger ??= _i2.Logger();
  @override
  _i5.MyApp get app => _createMyApp();
}
