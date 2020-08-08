import 'app.component.dart' as _i1;
import '../../utils/logger/logger.dart' as _i2;
import '../../network/http_client/api_client.dart' as _i3;
import '../../persistence/shared_preferences/shared+preferences_helper.dart'
    as _i4;
import 'dart:async' as _i5;
import '../../main.dart' as _i6;
import '../../authorization/ui/screen/login_page/login_page.dart' as _i7;
import '../../authorization/bloc/login_page/login_page.bloc.dart' as _i8;
import '../../authorization/service/login_page/login_page.service.dart' as _i9;
import '../../authorization/manager/login/login.manager.dart' as _i10;
import '../../authorization/repository/login/login_page.repository.dart'
    as _i11;
import '../../home/ui/screen/courses_page/courses_page.dart' as _i12;
import '../../home/bloc/courses_page/courses_page.bloc.dart' as _i13;
import '../../home/service/courses_page/courses_page.service.dart' as _i14;
import '../../home/manager/courses/cources.manager.dart' as _i15;
import '../../home/repository/courses_page/courses_page.repository.dart'
    as _i16;
import '../../authorization/ui/screen/register_page/register_page.dart' as _i17;
import '../../authorization/service/register/register.dart' as _i18;
import '../../authorization/manager/register/register.dart' as _i19;
import '../../authorization/repository/register/register.dart' as _i20;
import '../../authorization/manager/lifter_register_manager/lifter_register_manager.dart'
    as _i21;
import '../../authorization/repository/lifter_register/lifter_register_repository.dart'
    as _i22;

class AppComponent$Injector implements _i1.AppComponent {
  AppComponent$Injector._();

  _i2.Logger _singletonLogger;

  _i3.ApiClient _singletonApiClient;

  _i4.SharedPreferencesHelper _singletonSharedPreferencesHelper;

  static _i5.Future<_i1.AppComponent> create() async {
    final injector = AppComponent$Injector._();

    return injector;
  }

  _i6.MyApp _createMyApp() => _i6.MyApp(
      _createLoginPage(), _createCoursesPage(), _createRegisterPage());
  _i7.LoginPage _createLoginPage() =>
      _i7.LoginPage(_createLoginPageBloc(), _createLogger());
  _i8.LoginPageBloc _createLoginPageBloc() =>
      _i8.LoginPageBloc(_createLoginService(), _createLogger());
  _i9.LoginService _createLoginService() =>
      _i9.LoginService(_createLoginManager(), _createSharedPreferencesHelper());
  _i10.LoginManager _createLoginManager() =>
      _i10.LoginManager(_createLoginRepository());
  _i11.LoginRepository _createLoginRepository() =>
      _i11.LoginRepository(_createApiClient());
  _i3.ApiClient _createApiClient() =>
      _singletonApiClient ??= _i3.ApiClient(_createLogger());
  _i2.Logger _createLogger() => _singletonLogger ??= _i2.Logger();
  _i4.SharedPreferencesHelper _createSharedPreferencesHelper() =>
      _singletonSharedPreferencesHelper ??= _i4.SharedPreferencesHelper();
  _i12.CoursesPage _createCoursesPage() =>
      _i12.CoursesPage(_createCoursesPageBloc(), _createLogger());
  _i13.CoursesPageBloc _createCoursesPageBloc() =>
      _i13.CoursesPageBloc(_createCoursesService(), _createLogger());
  _i14.CoursesService _createCoursesService() => _i14.CoursesService(
      _createSharedPreferencesHelper(), _createCoursesManager());
  _i15.CoursesManager _createCoursesManager() =>
      _i15.CoursesManager(_createCoursesRepository());
  _i16.CoursesRepository _createCoursesRepository() =>
      _i16.CoursesRepository(_createApiClient());
  _i17.RegisterPage _createRegisterPage() =>
      _i17.RegisterPage(_createRegisterService());
  _i18.RegisterService _createRegisterService() => _i18.RegisterService(
      _createLogger(),
      _createRegisterManager(),
      _createSharedPreferencesHelper(),
      _createLoginManager(),
      _createLifterRegisterManager());
  _i19.RegisterManager _createRegisterManager() =>
      _i19.RegisterManager(_createRegisterRepository());
  _i20.RegisterRepository _createRegisterRepository() =>
      _i20.RegisterRepository(_createApiClient());
  _i21.LifterRegisterManager _createLifterRegisterManager() =>
      _i21.LifterRegisterManager(_createLifterRegisterRepository());
  _i22.LifterRegisterRepository _createLifterRegisterRepository() =>
      _i22.LifterRegisterRepository(_createApiClient());
  @override
  _i6.MyApp get app => _createMyApp();
}
