import 'package:inject/inject.dart';
import 'package:smarty/abstracts/module.dart';
import 'package:smarty/home/ui/screen/consulting_page/consulting_page.dart';
import 'package:smarty/home/ui/screen/course_details_page/Course_details_page.dart';
import 'package:smarty/home/ui/screen/courses_page/courses_page.dart';
import 'package:smarty/home/ui/screen/home_page/home_page.dart';
import 'package:smarty/home/ui/screen/lesson_page/lesson_page.dart';
import 'package:smarty/home/ui/screen/meditation_page/meditation_page.dart';
import 'package:smarty/home/ui/screen/news_and_events_page/news_and_evens_page.dart';
import 'package:smarty/home/ui/screen/programs_page/programs_page.dart';

@provide
class HomeModule extends Module {
  static const ROUTE_COURSE_DETAILS = '/course_details';
  static const ROUTE_COURSE_LIST = '/course_list';
  static const ROUTE_HOME = '/home';
  static const ROUTE_MEDITATION = 'meditation';
  static const ROUTE_LESSON = '/lesson';
  static const ROUTE_PROGRAMS = '/programs';
  static const ROUTE_EVENT_AND_NEWS= '/events_and_news';
  static const ROUTE_CONSULTING= '/consulting';


  CourseDetailPage _courseDetailPage;
  CoursesPage _coursesPage;
  HomePage _homePage;
  MeditationPage _meditationPage;
   LessonPage _lessonPage;
  ProgramsPage _programsPage;
  NewsAndEventsPAge _newsAndEventsPAge;
  ConsultingPage _consultingPage;

  HomeModule(this._meditationPage, this._homePage,
      this._coursesPage, this._courseDetailPage,
      this._lessonPage , this._programsPage,
      this._newsAndEventsPAge,this._consultingPage
      );

  @override
  getRoutes() {
    return {
      ROUTE_COURSE_DETAILS: (context) => _courseDetailPage,
      ROUTE_COURSE_LIST: (context) => _coursesPage,
      ROUTE_HOME: (context) => _homePage,
      ROUTE_MEDITATION: (context) => _meditationPage,
      ROUTE_LESSON: (context) => _lessonPage,
      ROUTE_PROGRAMS: (context) => _programsPage,
      ROUTE_EVENT_AND_NEWS : (context) => _newsAndEventsPAge,
      ROUTE_CONSULTING : (context) => _consultingPage,
    };
  }
}
