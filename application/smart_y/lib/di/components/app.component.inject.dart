import 'app.component.dart' as _i1;
import '../../persistence/shared_preferences/shared_preferences_helper.dart'
    as _i2;
import '../../shared/ui/widget/app_drawer/app_drawer.dart' as _i3;
import '../../utils/logger/logger.dart' as _i4;
import '../../network/http_client/api_client.dart' as _i5;
import 'dart:async' as _i6;
import '../../main.dart' as _i7;
import '../../home/home_module.dart' as _i8;
import '../../home/ui/screen/home_page/home_page.dart' as _i9;
import '../../home/ui/screen/news_and_events_page/news_and_evens_page.dart'
    as _i10;
import '../../home/ui/screen/consulting_page/consulting_page.dart' as _i11;
import '../../home/ui/screen/notification_page/notification_page.dart' as _i12;
import '../../authorization/authorization_component.dart' as _i13;
import '../../authorization/ui/screen/login_page/login_page.dart' as _i14;
import '../../authorization/bloc/login_page/login_page.bloc.dart' as _i15;
import '../../authorization/service/login_page/login_page.service.dart' as _i16;
import '../../authorization/manager/login/login.manager.dart' as _i17;
import '../../authorization/repository/login/login_page.repository.dart'


class AppComponent$Injector implements _i1.AppComponent {
  AppComponent$Injector._();

  _i2.SharedPreferencesHelper _singletonSharedPreferencesHelper;

  _i3.AppDrawerWidget _singletonAppDrawerWidget;

  _i4.Logger _singletonLogger;

  _i5.ApiClient _singletonApiClient;

  static _i6.Future<_i1.AppComponent> create() async {
    final injector = AppComponent$Injector._();

    return injector;
  }

  _i7.MyApp _createMyApp() => _i7.MyApp(
      _createHomeModule(),
      _createAuthorizationModule(),
      _createCourseModule(),
      _createProgramsModule(),
      _createMeditationModule());
  _i8.HomeModule _createHomeModule() => _i8.HomeModule(
      _createHomePage(),
      _createNewsAndEventsPAge(),
      _createConsultingPage(),
      _createNotificationPage());
  _i9.HomePage _createHomePage() => _i9.HomePage(_createAppDrawerWidget());
  _i3.AppDrawerWidget _createAppDrawerWidget() => _singletonAppDrawerWidget ??=
      _i3.AppDrawerWidget(_createSharedPreferencesHelper());
  _i2.SharedPreferencesHelper _createSharedPreferencesHelper() =>
      _singletonSharedPreferencesHelper ??= _i2.SharedPreferencesHelper();
  _i10.NewsAndEventsPAge _createNewsAndEventsPAge() =>
      _i10.NewsAndEventsPAge(_createAppDrawerWidget());
  _i11.ConsultingPage _createConsultingPage() =>
      _i11.ConsultingPage(_createAppDrawerWidget());
  _i12.NotificationPage _createNotificationPage() =>
      _i12.NotificationPage(_createAppDrawerWidget());
  _i13.AuthorizationModule _createAuthorizationModule() =>
      _i13.AuthorizationModule(_createLoginPage(), _createRegisterPage());
  _i14.LoginPage _createLoginPage() =>
      _i14.LoginPage(_createLoginPageBloc(), _createLogger());
  _i15.LoginPageBloc _createLoginPageBloc() =>
      _i15.LoginPageBloc(_createLoginService(), _createLogger());
  _i16.LoginService _createLoginService() => _i16.LoginService(
      _createLoginManager(), _createSharedPreferencesHelper(), _createLogger());
  _i17.LoginManager _createLoginManager() =>
      _i17.LoginManager(_createLoginRepository());
  _i18.LoginRepository _createLoginRepository() =>
      _i18.LoginRepository(_createApiClient());
  _i5.ApiClient _createApiClient() =>
      _singletonApiClient ??= _i5.ApiClient(_createLogger());
  _i4.Logger _createLogger() => _singletonLogger ??= _i4.Logger();
  _i19.RegisterPage _createRegisterPage() =>
      _i19.RegisterPage(_createRegisterService());
  _i20.RegisterService _createRegisterService() => _i20.RegisterService(
      _createLogger(),
      _createRegisterManager(),

      _createSharedPreferencesHelper());
  _i38.RegisterManager _createRegisterManager() =>
      _i38.RegisterManager(_createRegisterRepository());
  _i39.RegisterRepository _createRegisterRepository() =>
      _i39.RegisterRepository(_createApiClient());
  @override
  _i7.MyApp get app => _createMyApp();
}
