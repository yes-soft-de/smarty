
import 'package:inject/inject.dart';
import 'package:smarty/courses/response/course_response/course_response.dart';
import 'package:smarty/meditation/repository/meditaion_page/meditation_page.repository.dart';

@provide
class MeditationManager{
  MeditationRepository _meditationRepository;

  MeditationManager(this._meditationRepository);

  Future<List<CourseResponse>> getMeditation()async{

    return await this._meditationRepository.getMeditation();
  }
}