
import 'package:inject/inject.dart';
import 'package:smarty/home/manager/lesson/lesson.manager.dart';
import 'package:smarty/home/model/lesson/lesson.dart';
import 'package:smarty/home/response/lesson_response/lesson_response.dart';

@provide
class LessonService{
  LessonManager _lessonManager;

  LessonService(this._lessonManager);

  Future<Lesson> getLesson(int lessonId)async {
    LessonResponse lessonResponse = await _lessonManager.getLesson(lessonId);

    if (lessonResponse == null) {
      return null;
    }

  Lesson lesson = new Lesson(id:lessonResponse.id,title: lessonResponse.title.rendered,content: lessonResponse.content.rendered);

    return lesson;
  }
}