import 'package:inject/inject.dart';
import 'package:smarty/courses/model/course_model/course_details.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/meditation/filter/audios_filter.dart';
import 'package:smarty/meditation/manager/meditation_details_page/meditation_details_page.manager.dart';
import 'package:smarty/meditation/model/meditation_details.dart';

@provide
class MeditationDetailsService {
  MeditationDetailManager _meditationDetailManager;

  MeditationDetailsService(this._meditationDetailManager);

  Future< MeditationDetails> getMeditationDetails(int meditationId) async {

    CourseDetailsResponse meditationDetails =
    await _meditationDetailManager.getMeditationDetails(meditationId);

    if (meditationDetails == null) {
      return null;
    }

    List<Audio> audios = await getAudios(meditationDetails.curriculum);

    return new MeditationDetails(
        name: meditationDetails.course.name,
      description: meditationDetails.description,
      audiosNumber: meditationDetails.curriculum.length,
      audios: audios,
    );

  }
  Future<List<Audio>> getAudios(List<Curriculum> curriculum)async{
    List<CourseDetailsResponse>  audios = new List();

    for(int i=0; i<curriculum.length ; i++){
      CourseDetailsResponse audio =
          await _meditationDetailManager.getMeditationDetails(int.tryParse(curriculum[i].id));

      audios.add(audio);
    }

    return AudiosFilter.getAudios(audios);
  }
}
