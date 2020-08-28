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
    as _i18;
import '../../authorization/ui/screen/register_page/register_page.dart' as _i19;
import '../../authorization/service/register/register.dart' as _i20;
import '../../authorization/manager/register/register.dart' as _i21;
import '../../authorization/repository/register/register.dart' as _i22;
import '../../courses/course_module.dart' as _i23;
import '../../courses/ui/screen/courses_page/courses_page.dart' as _i24;
import '../../courses/bloc/courses_page/courses_page.bloc.dart' as _i25;
import '../../courses/service/courses_page/courses_page.service.dart' as _i26;
import '../../courses/manager/courses/cources.manager.dart' as _i27;
import '../../courses/repository/courses_page/courses_page.repository.dart'
    as _i28;
import '../../courses/ui/screen/course_details_page/Course_details_page.dart'
    as _i29;
import '../../courses/bloc/courses_details_page/courses_details_page.bloc.dart'
    as _i30;
import '../../courses/service/course_details_page/course_details_page.service.dart'
    as _i31;
import '../../courses/manager/course_details/course_details.manager.dart'
    as _i32;
import '../../courses/repository/course_details_page/course_details_page.repository.dart'
    as _i33;
import '../../courses/ui/screen/lesson_page/lesson_page.dart' as _i34;
import '../../courses/bloc/lesson_page/lesson_page.bloc.dart' as _i35;
import '../../courses/service/lesson_page/lesson_page.service.dart' as _i36;
import '../../courses/manager/lesson/lesson.manager.dart' as _i37;
import '../../courses/repository/lesson_page/lesson_page.repository.dart'
    as _i38;
import '../../programs/programs_module.dart' as _i39;
import '../../programs/ui/screen/programs_page/programs_page.dart' as _i40;
import '../../programs/bloc/programs_page/programs_page.bloc.dart' as _i41;
import '../../programs/service/programs_page/programs_page.service.dart'
    as _i42;
import '../../programs/manager/programs/programs.manager.dart' as _i43;
import '../../programs/repository/programs_page/programs_page.repository.dart'
    as _i44;
import '../../meditation/Meditation_module.dart' as _i45;
import '../../meditation/ui/screen/meditation_details_page/meditation_details_page.dart'
    as _i46;
import '../../meditation/bloc/meditation_details_page/meditation_details_page.bloc.dart'
    as _i47;
import '../../meditation/service/meditation_details_page/meditation_details_page.service.dart'
    as _i48;
import '../../meditation/manager/meditation_details_page/meditation_details_page.manager.dart'
    as _i49;
import '../../meditation/repository/meditation_details_page/meditation_details_page.repository.dart'
    as _i50;
import '../../meditation/ui/screen/meditation_page/meditation_page.dart'
    as _i51;
import '../../meditation/bloc/meditation_page/meditation_page.bloc.dart'
    as _i52;
import '../../meditation/service/meditation_Page/meditation_page_service.dart'
    as _i53;
import '../../meditation/manager/meditation_page/meditation_page.manager.dart'
    as _i54;
import '../../meditation/repository/meditaion_page/meditation_page.repository.dart'
    as _i55;

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
  _i21.RegisterManager _createRegisterManager() =>
      _i21.RegisterManager(_createRegisterRepository());
  _i22.RegisterRepository _createRegisterRepository() =>
      _i22.RegisterRepository(_createApiClient());
  _i23.CourseModule _createCourseModule() => _i23.CourseModule(
      _createCoursesPage(), _createCourseDetailPage(), _createLessonPage());
  _i24.CoursesPage _createCoursesPage() => _i24.CoursesPage(
      _createCoursesPageBloc(), _createLogger(), _createAppDrawerWidget());
  _i25.CoursesPageBloc _createCoursesPageBloc() =>
      _i25.CoursesPageBloc(_createCoursesService(), _createLogger());
  _i26.CoursesService _createCoursesService() => _i26.CoursesService(
      _createSharedPreferencesHelper(), _createCoursesManager());
  _i27.CoursesManager _createCoursesManager() =>
      _i27.CoursesManager(_createCoursesRepository());
  _i28.CoursesRepository _createCoursesRepository() => _i28.CoursesRepository(
      _createApiClient(), _createSharedPreferencesHelper());
  _i29.CourseDetailPage _createCourseDetailPage() =>
      _i29.CourseDetailPage(_createCourseDetailsBloc(), _createLogger());
  _i30.CourseDetailsBloc _createCourseDetailsBloc() =>
      _i30.CourseDetailsBloc(_createLogger(), _createCourseDetailsService());
  _i31.CourseDetailsService _createCourseDetailsService() =>
      _i31.CourseDetailsService(_createCourseDetailManager());
  _i32.CourseDetailManager _createCourseDetailManager() =>
      _i32.CourseDetailManager(_createCourseDetailsRepository());
  _i33.CourseDetailsRepository _createCourseDetailsRepository() =>
      _i33.CourseDetailsRepository(
          _createApiClient(), _createSharedPreferencesHelper());
  _i34.LessonPage _createLessonPage() =>
      _i34.LessonPage(_createLessonPageBloc(), _createLogger());
  _i35.LessonPageBloc _createLessonPageBloc() =>
      _i35.LessonPageBloc(_createLessonService(), _createLogger());
  _i36.LessonService _createLessonService() =>
      _i36.LessonService(_createLessonManager());
  _i37.LessonManager _createLessonManager() =>
      _i37.LessonManager(_createLessonRepository());
  _i38.LessonRepository _createLessonRepository() => _i38.LessonRepository(
      _createApiClient(), _createSharedPreferencesHelper());
  _i39.ProgramsModule _createProgramsModule() =>
      _i39.ProgramsModule(_createProgramsPage());
  _i40.ProgramsPage _createProgramsPage() => _i40.ProgramsPage(
      _createProgramsPageBloc(), _createAppDrawerWidget(), _createLogger());
  _i41.ProgramsPageBloc _createProgramsPageBloc() =>
      _i41.ProgramsPageBloc(_createProgramsService(), _createLogger());
  _i42.ProgramsService _createProgramsService() =>
      _i42.ProgramsService(_createProgramsManager());
  _i43.ProgramsManager _createProgramsManager() =>
      _i43.ProgramsManager(_createProgramsRepository());
  _i44.ProgramsRepository _createProgramsRepository() =>
      _i44.ProgramsRepository(_createApiClient());
  _i45.MeditationModule _createMeditationModule() => _i45.MeditationModule(
      _createMeditationDetailsPage(), _createMeditationPage());
  _i46.MeditationDetailsPage _createMeditationDetailsPage() =>
      _i46.MeditationDetailsPage(_createAppDrawerWidget(),
          _createMeditationDetailsBloc(), _createLogger());
  _i47.MeditationDetailsBloc _createMeditationDetailsBloc() =>
      _i47.MeditationDetailsBloc(
          _createLogger(), _createMeditationDetailsService());
  _i48.MeditationDetailsService _createMeditationDetailsService() =>
      _i48.MeditationDetailsService(_createMeditationDetailManager());
  _i49.MeditationDetailManager _createMeditationDetailManager() =>
      _i49.MeditationDetailManager(_createMeditationDetailsRepository());
  _i50.MeditationDetailsRepository _createMeditationDetailsRepository() =>
      _i50.MeditationDetailsRepository(
          _createApiClient(), _createSharedPreferencesHelper());
  _i51.MeditationPage _createMeditationPage() => _i51.MeditationPage(
      _createMeditationPageBloc(), _createLogger(), _createAppDrawerWidget());
  _i52.MeditationPageBloc _createMeditationPageBloc() =>
      _i52.MeditationPageBloc(_createMeditationService(), _createLogger());
  _i53.MeditationService _createMeditationService() =>
      _i53.MeditationService(_createMeditationManager());
  _i54.MeditationManager _createMeditationManager() =>
      _i54.MeditationManager(_createMeditationRepository());
  _i55.MeditationRepository _createMeditationRepository() =>
      _i55.MeditationRepository(_createApiClient());
  @override
  _i7.MyApp get app => _createMyApp();
}
