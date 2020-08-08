import 'app.component.dart' as _i1;
import '../../persistence/shared_preferences/shared+preferences_helper.dart'
    as _i2;
import '../../utils/logger/logger.dart' as _i3;
import '../../network/http_client/api_client.dart' as _i4;
import 'dart:async' as _i5;
import '../../main.dart' as _i6;
import '../../home/home_module.dart' as _i7;
import '../../home/ui/screen/meditation_page/meditation_page.dart' as _i8;
import '../../home/ui/screen/home_page/home_page.dart' as _i9;
import '../../home/ui/screen/courses_page/courses_page.dart' as _i10;
import '../../home/bloc/courses_page/courses_page.bloc.dart' as _i11;
import '../../home/service/courses_page/courses_page.service.dart' as _i12;
import '../../home/manager/courses/cources.manager.dart' as _i13;
import '../../home/repository/courses_page/courses_page.repository.dart'
    as _i14;
import '../../home/ui/screen/course_details_page/Course_details_page.dart'
    as _i15;
import '../../authorization/authorization_component.dart' as _i16;
import '../../authorization/ui/screen/login_page/login_page.dart' as _i17;
import '../../authorization/bloc/login_page/login_page.bloc.dart' as _i18;
import '../../authorization/service/login_page/login_page.service.dart' as _i19;
import '../../authorization/manager/login/login.manager.dart' as _i20;
import '../../authorization/repository/login/login_page.repository.dart'
    as _i21;
import '../../authorization/ui/screen/register_page/register_page.dart' as _i22;
import '../../authorization/service/register/register.dart' as _i23;
import '../../authorization/manager/register/register.dart' as _i24;
import '../../authorization/repository/register/register.dart' as _i25;
import '../../authorization/manager/lifter_register_manager/lifter_register_manager.dart'
    as _i26;
import '../../authorization/repository/lifter_register/lifter_register_repository.dart'
    as _i27;

class AppComponent$Injector implements _i1.AppComponent {
  AppComponent$Injector._();

  _i2.SharedPreferencesHelper _singletonSharedPreferencesHelper;

  _i3.Logger _singletonLogger;

  _i4.ApiClient _singletonApiClient;

  static _i5.Future<_i1.AppComponent> create() async {
    final injector = AppComponent$Injector._();

    return injector;
  }

  _i6.MyApp _createMyApp() =>
      _i6.MyApp(_createHomeModule(), _createAuthorizationModule());
  _i7.HomeModule _createHomeModule() => _i7.HomeModule(_createMeditationPage(),
      _createHomePage(), _createCoursesPage(), _createCourseDetailPage());
  _i8.MeditationPage _createMeditationPage() => _i8.MeditationPage();
  _i9.HomePage _createHomePage() => _i9.HomePage();
  _i10.CoursesPage _createCoursesPage() =>
      _i10.CoursesPage(_createCoursesPageBloc(), _createLogger());
  _i11.CoursesPageBloc _createCoursesPageBloc() =>
      _i11.CoursesPageBloc(_createCoursesService(), _createLogger());
  _i12.CoursesService _createCoursesService() => _i12.CoursesService(
      _createSharedPreferencesHelper(), _createCoursesManager());
  _i2.SharedPreferencesHelper _createSharedPreferencesHelper() =>
      _singletonSharedPreferencesHelper ??= _i2.SharedPreferencesHelper();
  _i13.CoursesManager _createCoursesManager() =>
      _i13.CoursesManager(_createCoursesRepository());
  _i14.CoursesRepository _createCoursesRepository() =>
      _i14.CoursesRepository(_createApiClient());
  _i4.ApiClient _createApiClient() =>
      _singletonApiClient ??= _i4.ApiClient(_createLogger());
  _i3.Logger _createLogger() => _singletonLogger ??= _i3.Logger();
  _i15.CourseDetailPage _createCourseDetailPage() => _i15.CourseDetailPage();
  _i16.AuthorizationModule _createAuthorizationModule() =>
      _i16.AuthorizationModule(_createLoginPage(), _createRegisterPage());
  _i17.LoginPage _createLoginPage() =>
      _i17.LoginPage(_createLoginPageBloc(), _createLogger());
  _i18.LoginPageBloc _createLoginPageBloc() =>
      _i18.LoginPageBloc(_createLoginService(), _createLogger());
  _i19.LoginService _createLoginService() => _i19.LoginService(
      _createLoginManager(), _createSharedPreferencesHelper());
  _i20.LoginManager _createLoginManager() =>
      _i20.LoginManager(_createLoginRepository());
  _i21.LoginRepository _createLoginRepository() =>
      _i21.LoginRepository(_createApiClient());
  _i22.RegisterPage _createRegisterPage() =>
      _i22.RegisterPage(_createRegisterService());
  _i23.RegisterService _createRegisterService() => _i23.RegisterService(
      _createLogger(),
      _createRegisterManager(),
      _createSharedPreferencesHelper(),
      _createLoginManager(),
      _createLifterRegisterManager());
  _i24.RegisterManager _createRegisterManager() =>
      _i24.RegisterManager(_createRegisterRepository());
  _i25.RegisterRepository _createRegisterRepository() =>
      _i25.RegisterRepository(_createApiClient());
  _i26.LifterRegisterManager _createLifterRegisterManager() =>
      _i26.LifterRegisterManager(_createLifterRegisterRepository());
  _i27.LifterRegisterRepository _createLifterRegisterRepository() =>
      _i27.LifterRegisterRepository(_createApiClient());
  @override
  _i6.MyApp get app => _createMyApp();
}
