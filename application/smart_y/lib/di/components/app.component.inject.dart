import 'app.component.dart' as _i1;
import '../../utils/logger/logger.dart' as _i2;
import '../../persistence/shared_preferences/shared+preferences_helper.dart'
    as _i3;
import '../../network/http_client/http_client.dart' as _i4;
import 'dart:async' as _i5;
import '../../main.dart' as _i6;
import '../../ui/screen/login_page/login_page.dart' as _i7;
import '../../bloc/login_page/login_page.bloc.dart' as _i8;
import '../../service/login_page/login_page.service.dart' as _i9;
import '../../manager/login/login.manager.dart' as _i10;
import '../../repository/login_page/login_page.repository.dart' as _i11;
import '../../ui/screen/courses_page/courses_page.dart' as _i12;
import '../../bloc/courses_page/courses_page.bloc.dart' as _i13;
import '../../service/courses_page/courses_page.service.dart' as _i14;
import '../../manager/courses/cources.manager.dart' as _i15;
import '../../repository/courses_page/courses_page.repository.dart' as _i16;

class AppComponent$Injector implements _i1.AppComponent {
  AppComponent$Injector._();

  _i2.Logger _singletonLogger;

  _i3.SharedPreferencesHelper _singletonSharedPreferencesHelper;

  _i4.HttpClient _singletonHttpClient;

  static _i5.Future<_i1.AppComponent> create() async {
    final injector = AppComponent$Injector._();

    return injector;
  }

  _i6.MyApp _createMyApp() =>
      _i6.MyApp(_createLoginPage(), _createCoursesPage());
  _i7.LoginPage _createLoginPage() =>
      _i7.LoginPage(_createLoginPageBloc(), _createLogger());
  _i8.LoginPageBloc _createLoginPageBloc() =>
      _i8.LoginPageBloc(_createLoginService());
  _i9.LoginService _createLoginService() =>
      _i9.LoginService(_createLoginManager(), _createSharedPreferencesHelper());
  _i10.LoginManager _createLoginManager() =>
      _i10.LoginManager(_createLoginRepository());
  _i11.LoginRepository _createLoginRepository() =>
      _i11.LoginRepository(_createHttpClient());
  _i4.HttpClient _createHttpClient() => _singletonHttpClient ??=
      _i4.HttpClient(_createLogger(), _createSharedPreferencesHelper());
  _i2.Logger _createLogger() => _singletonLogger ??= _i2.Logger();
  _i3.SharedPreferencesHelper _createSharedPreferencesHelper() =>
      _singletonSharedPreferencesHelper ??= _i3.SharedPreferencesHelper();
  _i12.CoursesPage _createCoursesPage() =>
      _i12.CoursesPage(_createCoursesPageBloc(), _createLogger());
  _i13.CoursesPageBloc _createCoursesPageBloc() =>
      _i13.CoursesPageBloc(_createCoursesService(), _createLogger());
  _i14.CoursesService _createCoursesService() => _i14.CoursesService(
      _createSharedPreferencesHelper(), _createCoursesManager());
  _i15.CoursesManager _createCoursesManager() =>
      _i15.CoursesManager(_createCoursesRepository());
  _i16.CoursesRepository _createCoursesRepository() =>
      _i16.CoursesRepository(_createHttpClient());
  @override
  _i6.MyApp get app => _createMyApp();
}
