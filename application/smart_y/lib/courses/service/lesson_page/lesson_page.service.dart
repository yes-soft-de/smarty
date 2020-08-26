
import 'package:inject/inject.dart';
import 'package:smarty/courses/manager/lesson/lesson.manager.dart';
import 'package:smarty/courses/model/lesson/lesson.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/courses/response/lesson_response/lesson_response.dart';

@provide
class LessonService{
  LessonManager _lessonManager;

  LessonService(this._lessonManager);

  Future<Lesson> getLesson(int lessonId)async {
    CourseDetailsResponse lessonResponse = await _lessonManager.getLesson(lessonId);

    if (lessonResponse == null) {
      return null;
    }

  Lesson lesson = new Lesson(id:lessonResponse.course.id,title: lessonResponse.course.name,content: lessonResponse.description);

    return lesson;
  }
}