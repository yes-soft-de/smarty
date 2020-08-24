import 'app.component.dart' as _i1;
import '../../persistence/shared_preferences/shared_preferences_helper.dart'
    as _i2;
import '../../home/ui/widget/app_drawer/app_drawer.dart' as _i3;
import '../../utils/logger/logger.dart' as _i4;
import '../../network/http_client/api_client.dart' as _i5;
import 'dart:async' as _i6;
import '../../main.dart' as _i7;
import '../../home/home_module.dart' as _i8;
import '../../home/ui/screen/meditation_page/meditation_page.dart' as _i9;
import '../../home/ui/screen/home_page/home_page.dart' as _i10;
import '../../home/ui/screen/courses_page/courses_page.dart' as _i11;
import '../../home/bloc/courses_page/courses_page.bloc.dart' as _i12;
import '../../home/service/courses_page/courses_page.service.dart' as _i13;
import '../../home/manager/courses/cources.manager.dart' as _i14;
import '../../home/repository/courses_page/courses_page.repository.dart'
    as _i15;
import '../../home/ui/screen/course_details_page/Course_details_page.dart'
    as _i16;
import '../../home/bloc/courses_details_page/courses_details_page.bloc.dart'
    as _i17;
import '../../home/service/course_details_page/course_details_page.service.dart'
    as _i18;
import '../../home/manager/course_details/course_details.manager.dart' as _i19;
import '../../home/repository/course_details_page/course_details_page.repository.dart'
    as _i20;
import '../../home/ui/screen/lesson_page/lesson_page.dart' as _i21;
import '../../home/bloc/lesson_page/lesson_page.bloc.dart' as _i22;
import '../../home/service/lesson_page/lesson_page.service.dart' as _i23;
import '../../home/manager/lesson/lesson.manager.dart' as _i24;
import '../../home/repository/lesson_page/lesson_page.repository.dart' as _i25;
import '../../home/ui/screen/programs_page/programs_page.dart' as _i26;
import '../../home/bloc/programs_page/programs_page.bloc.dart' as _i27;
import '../../home/service/programs_page/programs_page.service.dart' as _i28;
import '../../home/ui/screen/news_and_events_page/news_and_evens_page.dart'
    as _i29;
import '../../home/ui/screen/consulting_page/consulting_page.dart' as _i30;
import '../../home/ui/screen/notification_page/notification_page.dart' as _i31;
import '../../authorization/authorization_component.dart' as _i32;
import '../../authorization/ui/screen/login_page/login_page.dart' as _i33;
import '../../authorization/bloc/login_page/login_page.bloc.dart' as _i34;
import '../../authorization/service/login_page/login_page.service.dart' as _i35;
import '../../authorization/manager/login/login.manager.dart' as _i36;
import '../../authorization/repository/login/login_page.repository.dart'
    as _i37;
import '../../authorization/ui/screen/register_page/register_page.dart' as _i38;
import '../../authorization/service/register/register.dart' as _i39;
import '../../authorization/manager/register/register.dart' as _i40;
import '../../authorization/repository/register/register.dart' as _i41;
import '../../authorization/manager/lifter_register_manager/lifter_register_manager.dart'
    as _i42;
import '../../authorization/repository/lifter_register/lifter_register_repository.dart'
    as _i43;

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

  _i7.MyApp _createMyApp() =>
      _i7.MyApp(_createHomeModule(), _createAuthorizationModule());
  _i8.HomeModule _createHomeModule() => _i8.HomeModule(
      _createMeditationPage(),
      _createHomePage(),
      _createCoursesPage(),
      _createCourseDetailPage(),
      _createLessonPage(),
      _createProgramsPage(),
      _createNewsAndEventsPAge(),
      _createConsultingPage(),
      _createNotificationPage());
  _i9.MeditationPage _createMeditationPage() =>
      _i9.MeditationPage(_createAppDrawerWidget());
  _i3.AppDrawerWidget _createAppDrawerWidget() => _singletonAppDrawerWidget ??=
      _i3.AppDrawerWidget(_createSharedPreferencesHelper());
  _i2.SharedPreferencesHelper _createSharedPreferencesHelper() =>
      _singletonSharedPreferencesHelper ??= _i2.SharedPreferencesHelper();
  _i10.HomePage _createHomePage() => _i10.HomePage(_createAppDrawerWidget());
  _i11.CoursesPage _createCoursesPage() => _i11.CoursesPage(
      _createCoursesPageBloc(), _createLogger(), _createAppDrawerWidget());
  _i12.CoursesPageBloc _createCoursesPageBloc() =>
      _i12.CoursesPageBloc(_createCoursesService(), _createLogger());
  _i13.CoursesService _createCoursesService() => _i13.CoursesService(
      _createSharedPreferencesHelper(), _createCoursesManager());
  _i14.CoursesManager _createCoursesManager() =>
      _i14.CoursesManager(_createCoursesRepository());
  _i15.CoursesRepository _createCoursesRepository() => _i15.CoursesRepository(
      _createApiClient(), _createSharedPreferencesHelper());
  _i5.ApiClient _createApiClient() =>
      _singletonApiClient ??= _i5.ApiClient(_createLogger());
  _i4.Logger _createLogger() => _singletonLogger ??= _i4.Logger();
  _i16.CourseDetailPage _createCourseDetailPage() =>
      _i16.CourseDetailPage(_createCourseDetailsBloc(), _createLogger());
  _i17.CourseDetailsBloc _createCourseDetailsBloc() =>
      _i17.CourseDetailsBloc(_createLogger(), _createCourseDetailsService());
  _i18.CourseDetailsService _createCourseDetailsService() =>
      _i18.CourseDetailsService(_createCourseDetailManager());
  _i19.CourseDetailManager _createCourseDetailManager() =>
      _i19.CourseDetailManager(_createCourseDetailsRepository());
  _i20.CourseDetailsRepository _createCourseDetailsRepository() =>
      _i20.CourseDetailsRepository(
          _createApiClient(), _createSharedPreferencesHelper());
  _i21.LessonPage _createLessonPage() =>
      _i21.LessonPage(_createLessonPageBloc(), _createLogger());
  _i22.LessonPageBloc _createLessonPageBloc() =>
      _i22.LessonPageBloc(_createLessonService(), _createLogger());
  _i23.LessonService _createLessonService() =>
      _i23.LessonService(_createLessonManager());
  _i24.LessonManager _createLessonManager() =>
      _i24.LessonManager(_createLessonRepository());
  _i25.LessonRepository _createLessonRepository() => _i25.LessonRepository(
      _createApiClient(), _createSharedPreferencesHelper());
  _i26.ProgramsPage _createProgramsPage() => _i26.ProgramsPage(
      _createAppDrawerWidget(), _createProgramsPageBloc(), _createLogger());
  _i27.ProgramsPageBloc _createProgramsPageBloc() =>
      _i27.ProgramsPageBloc(_createProgramsService(), _createLogger());
  _i28.ProgramsService _createProgramsService() => _i28.ProgramsService();
  _i29.NewsAndEventsPAge _createNewsAndEventsPAge() =>
      _i29.NewsAndEventsPAge(_createAppDrawerWidget());
  _i30.ConsultingPage _createConsultingPage() =>
      _i30.ConsultingPage(_createAppDrawerWidget());
  _i31.NotificationPage _createNotificationPage() =>
      _i31.NotificationPage(_createAppDrawerWidget());
  _i32.AuthorizationModule _createAuthorizationModule() =>
      _i32.AuthorizationModule(_createLoginPage(), _createRegisterPage());
  _i33.LoginPage _createLoginPage() =>
      _i33.LoginPage(_createLoginPageBloc(), _createLogger());
  _i34.LoginPageBloc _createLoginPageBloc() =>
      _i34.LoginPageBloc(_createLoginService(), _createLogger());
  _i35.LoginService _createLoginService() => _i35.LoginService(
      _createLoginManager(), _createSharedPreferencesHelper(), _createLogger());
  _i36.LoginManager _createLoginManager() =>
      _i36.LoginManager(_createLoginRepository());
  _i37.LoginRepository _createLoginRepository() =>
      _i37.LoginRepository(_createApiClient());
  _i38.RegisterPage _createRegisterPage() =>
      _i38.RegisterPage(_createRegisterService());
  _i39.RegisterService _createRegisterService() => _i39.RegisterService(
      _createLogger(),
      _createRegisterManager(),
      _createSharedPreferencesHelper(),
      _createLoginManager(),
      _createLifterRegisterManager());
  _i40.RegisterManager _createRegisterManager() =>
      _i40.RegisterManager(_createRegisterRepository());
  _i41.RegisterRepository _createRegisterRepository() =>
      _i41.RegisterRepository(_createApiClient());
  _i42.LifterRegisterManager _createLifterRegisterManager() =>
      _i42.LifterRegisterManager(_createLifterRegisterRepository());
  _i43.LifterRegisterRepository _createLifterRegisterRepository() =>
      _i43.LifterRegisterRepository(_createApiClient());
  @override
  _i7.MyApp get app => _createMyApp();
}
