
import 'package:inject/inject.dart';
import 'package:smarty/home/repository/lesson_page/lesson_page.repository.dart';
import 'package:smarty/home/response/lesson_response/lesson_response.dart';

@provide
class LessonManager{
  LessonRepository _lessonRepository;

  LessonManager(this._lessonRepository);

  Future<LessonResponse> getLesson(int lessonId)async{
    return await _lessonRepository.getLesson(lessonId);
  }
}