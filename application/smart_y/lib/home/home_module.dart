import 'package:inject/inject.dart';
import 'package:smarty/abstracts/module.dart';
import 'package:smarty/home/ui/screen/course_details_page/Course_details_page.dart';
import 'package:smarty/home/ui/screen/courses_page/courses_page.dart';
import 'package:smarty/home/ui/screen/home_page/home_page.dart';
import 'package:smarty/home/ui/screen/meditation_page/meditation_page.dart';

@provide
class HomeModule extends Module {
  static const ROUTE_COURSE_DETAILS = '/course_details';
  static const ROUTE_COURSE_LIST = '/course_list';
  static const ROUTE_HOME = '/home';
  static const ROUTE_MEDITATION = 'meditation';

  CourseDetailPage _courseDetailPage;
  CoursesPage _coursesPage;
  HomePage _homePage;
  MeditationPage _meditationPage;

  HomeModule(this._meditationPage, this._homePage,
      this._coursesPage, this._courseDetailPage);

  @override
  getRoutes() {
    return {
      ROUTE_COURSE_DETAILS: (context) => _courseDetailPage,
      ROUTE_COURSE_LIST: (context) => _coursesPage,
      ROUTE_HOME: (context) => _homePage,
      ROUTE_MEDITATION: (context) => _meditationPage
    };
  }
}
