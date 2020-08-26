import 'app.component.dart' as _i1;
import '../../persistence/shared_preferences/shared_preferences_helper.dart'
    as _i2;
import '../../shared/ui/widget/app_drawer/app_drawer.dart' as _i3;
import '../../utils/logger/logger.dart' as _i4;
import '../../network/http_client/api_client.dart' as _i5;
import 'dart:async' as _i6;
import '../../main.dart' as _i7;
import '../../home/home_module.dart' as _i8;
import '../../home/ui/screen/meditation_page/meditation_page.dart' as _i9;
import '../../home/ui/screen/home_page/home_page.dart' as _i10;
import '../../home/ui/screen/news_and_events_page/news_and_evens_page.dart'
    as _i11;
import '../../home/ui/screen/consulting_page/consulting_page.dart' as _i12;
import '../../home/ui/screen/notification_page/notification_page.dart' as _i13;
import '../../authorization/authorization_component.dart' as _i14;
import '../../authorization/ui/screen/login_page/login_page.dart' as _i15;
import '../../authorization/bloc/login_page/login_page.bloc.dart' as _i16;
import '../../authorization/service/login_page/login_page.service.dart' as _i17;
import '../../authorization/manager/login/login.manager.dart' as _i18;
import '../../authorization/repository/login/login_page.repository.dart'
    as _i19;
import '../../authorization/ui/screen/register_page/register_page.dart' as _i20;
import '../../authorization/service/register/register.dart' as _i21;
import '../../authorization/manager/register/register.dart' as _i22;
import '../../authorization/repository/register/register.dart' as _i23;
import '../../authorization/manager/lifter_register_manager/lifter_register_manager.dart'
    as _i24;
import '../../authorization/repository/lifter_register/lifter_register_repository.dart'
    as _i25;
import '../../courses/course_module.dart' as _i26;
import '../../courses/ui/screen/courses_page/courses_page.dart' as _i27;
import '../../courses/bloc/courses_page/courses_page.bloc.dart' as _i28;
import '../../courses/service/courses_page/courses_page.service.dart' as _i29;
import '../../courses/manager/courses/cources.manager.dart' as _i30;
import '../../courses/repository/courses_page/courses_page.repository.dart'
    as _i31;
import '../../courses/ui/screen/course_details_page/Course_details_page.dart'
    as _i32;
import '../../courses/bloc/courses_details_page/courses_details_page.bloc.dart'
    as _i33;
import '../../courses/service/course_details_page/course_details_page.service.dart'
    as _i34;
import '../../courses/manager/course_details/course_details.manager.dart'
    as _i35;
import '../../courses/repository/course_details_page/course_details_page.repository.dart'
    as _i36;
import '../../courses/ui/screen/lesson_page/lesson_page.dart' as _i37;
import '../../courses/bloc/lesson_page/lesson_page.bloc.dart' as _i38;
import '../../courses/service/lesson_page/lesson_page.service.dart' as _i39;
import '../../courses/manager/lesson/lesson.manager.dart' as _i40;
import '../../courses/repository/lesson_page/lesson_page.repository.dart'
    as _i41;
import '../../programs/programs_module.dart' as _i42;
import '../../programs/ui/screen/programs_page/programs_page.dart' as _i43;
import '../../programs/bloc/programs_page/programs_page.bloc.dart' as _i44;
import '../../programs/service/programs_page/programs_page.service.dart'
    as _i45;
import '../../programs/manager/programs/programs.manager.dart' as _i46;
import '../../programs/repository/programs_page/programs_page.repository.dart'
    as _i47;

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
      _createProgramsModule());
  _i8.HomeModule _createHomeModule() => _i8.HomeModule(
      _createMeditationPage(),
      _createHomePage(),
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
  _i11.NewsAndEventsPAge _createNewsAndEventsPAge() =>
      _i11.NewsAndEventsPAge(_createAppDrawerWidget());
  _i12.ConsultingPage _createConsultingPage() =>
      _i12.ConsultingPage(_createAppDrawerWidget());
  _i13.NotificationPage _createNotificationPage() =>
      _i13.NotificationPage(_createAppDrawerWidget());
  _i14.AuthorizationModule _createAuthorizationModule() =>
      _i14.AuthorizationModule(_createLoginPage(), _createRegisterPage());
  _i15.LoginPage _createLoginPage() =>
      _i15.LoginPage(_createLoginPageBloc(), _createLogger());
  _i16.LoginPageBloc _createLoginPageBloc() =>
      _i16.LoginPageBloc(_createLoginService(), _createLogger());
  _i17.LoginService _createLoginService() => _i17.LoginService(
      _createLoginManager(), _createSharedPreferencesHelper(), _createLogger());
  _i18.LoginManager _createLoginManager() =>
      _i18.LoginManager(_createLoginRepository());
  _i19.LoginRepository _createLoginRepository() =>
      _i19.LoginRepository(_createApiClient());
  _i5.ApiClient _createApiClient() =>
      _singletonApiClient ??= _i5.ApiClient(_createLogger());
  _i4.Logger _createLogger() => _singletonLogger ??= _i4.Logger();
  _i20.RegisterPage _createRegisterPage() =>
      _i20.RegisterPage(_createRegisterService());
  _i21.RegisterService _createRegisterService() => _i21.RegisterService(
      _createLogger(),
      _createRegisterManager(),
      _createSharedPreferencesHelper(),
      _createLoginManager(),
      _createLifterRegisterManager());
  _i22.RegisterManager _createRegisterManager() =>
      _i22.RegisterManager(_createRegisterRepository());
  _i23.RegisterRepository _createRegisterRepository() =>
      _i23.RegisterRepository(_createApiClient());
  _i24.LifterRegisterManager _createLifterRegisterManager() =>
      _i24.LifterRegisterManager(_createLifterRegisterRepository());
  _i25.LifterRegisterRepository _createLifterRegisterRepository() =>
      _i25.LifterRegisterRepository(_createApiClient());
  _i26.CourseModule _createCourseModule() => _i26.CourseModule(
      _createCoursesPage(), _createCourseDetailPage(), _createLessonPage());
  _i27.CoursesPage _createCoursesPage() => _i27.CoursesPage(
      _createCoursesPageBloc(), _createLogger(), _createAppDrawerWidget());
  _i28.CoursesPageBloc _createCoursesPageBloc() =>
      _i28.CoursesPageBloc(_createCoursesService(), _createLogger());
  _i29.CoursesService _createCoursesService() => _i29.CoursesService(
      _createSharedPreferencesHelper(), _createCoursesManager());
  _i30.CoursesManager _createCoursesManager() =>
      _i30.CoursesManager(_createCoursesRepository());
  _i31.CoursesRepository _createCoursesRepository() => _i31.CoursesRepository(
      _createApiClient(), _createSharedPreferencesHelper());
  _i32.CourseDetailPage _createCourseDetailPage() =>
      _i32.CourseDetailPage(_createCourseDetailsBloc(), _createLogger());
  _i33.CourseDetailsBloc _createCourseDetailsBloc() =>
      _i33.CourseDetailsBloc(_createLogger(), _createCourseDetailsService());
  _i34.CourseDetailsService _createCourseDetailsService() =>
      _i34.CourseDetailsService(_createCourseDetailManager());
  _i35.CourseDetailManager _createCourseDetailManager() =>
      _i35.CourseDetailManager(_createCourseDetailsRepository());
  _i36.CourseDetailsRepository _createCourseDetailsRepository() =>
      _i36.CourseDetailsRepository(
          _createApiClient(), _createSharedPreferencesHelper());
  _i37.LessonPage _createLessonPage() =>
      _i37.LessonPage(_createLessonPageBloc(), _createLogger());
  _i38.LessonPageBloc _createLessonPageBloc() =>
      _i38.LessonPageBloc(_createLessonService(), _createLogger());
  _i39.LessonService _createLessonService() =>
      _i39.LessonService(_createLessonManager());
  _i40.LessonManager _createLessonManager() =>
      _i40.LessonManager(_createLessonRepository());
  _i41.LessonRepository _createLessonRepository() => _i41.LessonRepository(
      _createApiClient(), _createSharedPreferencesHelper());
  _i42.ProgramsModule _createProgramsModule() =>
      _i42.ProgramsModule(_createProgramsPage());
  _i43.ProgramsPage _createProgramsPage() => _i43.ProgramsPage(
      _createProgramsPageBloc(), _createAppDrawerWidget(), _createLogger());
  _i44.ProgramsPageBloc _createProgramsPageBloc() =>
      _i44.ProgramsPageBloc(_createProgramsService(), _createLogger());
  _i45.ProgramsService _createProgramsService() =>
      _i45.ProgramsService(_createProgramsManager());
  _i46.ProgramsManager _createProgramsManager() =>
      _i46.ProgramsManager(_createProgramsRepository());
  _i47.ProgramsRepository _createProgramsRepository() =>
      _i47.ProgramsRepository(_createApiClient());
  @override
  _i7.MyApp get app => _createMyApp();
}
