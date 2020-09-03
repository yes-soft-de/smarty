import 'package:inject/inject.dart';
import 'package:smarty/courses/model/course/course_list_item.model.dart';
import 'package:smarty/courses/response/course_response/course_response.dart';
import 'package:smarty/meditation/manager/meditation_page/meditation_page.manager.dart';

@provide
class MeditationService{
  MeditationManager _meditationManager;

  MeditationService(this._meditationManager);

  Future<List<CourseModel>> getMeditation() async {
    List<CourseResponse> meditationResponse =
    await this._meditationManager.getMeditation();

    if (meditationResponse == null) {
      return null;
    }

    List<CourseModel> availableMeditation = [];
    meditationResponse.forEach((course) {
      // TODO: Create a Course Model
      availableMeditation.add(
          CourseModel(
              id:course.id,
              title: course.name,
              price: course.price,
              image: course.featuredImage
          )
      );
    });

    return availableMeditation;
  }

}