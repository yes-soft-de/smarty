import 'package:inject/inject.dart';
import 'package:smarty/home/manager/courses/cources.manager.dart';
import 'package:smarty/home/model/course/course_list_item.model.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';
import 'package:smarty/home/response/course_response/course_response.dart';

@provide
class CoursesService {
  SharedPreferencesHelper _sharedPreferencesHelper;
  CoursesManager _coursesManager;

  CoursesService(this._sharedPreferencesHelper, this._coursesManager);

  // Mapping data, and transforming it to useful data
  Future<List<CourseModel>> getCourses() async {
    List<CourseResponse> coursesResponse =
        await this._coursesManager.getCourses();

    if (coursesResponse == null) {
      return null;
    }

    List<CourseModel> availableCourses = [];
    coursesResponse.forEach((course) {
      // TODO: Create a Course Model
      availableCourses.add(
          CourseModel(id:course.id,title: course.name,price: course.price,image: course.featuredImage )
      );
    });

    return availableCourses;
  }
}
