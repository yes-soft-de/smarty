import 'package:inject/inject.dart';
import 'package:smarty/abstracts/module.dart';
import 'package:smarty/courses/ui/screen/course_details_page/Course_details_page.dart';
import 'package:smarty/courses/ui/screen/courses_page/courses_page.dart';
import 'package:smarty/courses/ui/screen/lesson_page/lesson_page.dart';

@provide
class CourseModule extends Module{
  static const ROUTE_COURSE_DETAILS = '/course_details';
  static const ROUTE_COURSE_LIST = '/course_list';
  static const ROUTE_LESSON = '/lesson';

  CourseDetailPage _courseDetailPage;
  CoursesPage _coursesPage;
  LessonPage _lessonPage;

  CourseModule(
      this._coursesPage, this._courseDetailPage,
      this._lessonPage ,
      );


  @override
    getRoutes() {
    return {
      ROUTE_COURSE_DETAILS: (context) => _courseDetailPage,
      ROUTE_COURSE_LIST: (context) => _coursesPage,

      ROUTE_LESSON: (context) => _lessonPage,

    };
  }
}