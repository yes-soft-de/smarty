 import 'package:inject/inject.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/meditation/repository/meditation_details_page/meditation_details_page.repository.dart';

@provide
class MeditationDetailManager{

  MeditationDetailsRepository _meditationDetailsRepository;

  MeditationDetailManager(this._meditationDetailsRepository);

  Future< CourseDetailsResponse> getMeditationDetails(int meditationId)async{
    return await this._meditationDetailsRepository.getMeditationDetails(meditationId);
  }
}