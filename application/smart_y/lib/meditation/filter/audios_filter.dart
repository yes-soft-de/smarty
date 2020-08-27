import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/meditation/model/meditation_details.dart';

class AudiosFilter{

 static List<Audio> getAudios( List<Curriculum> curriculum){
    List<Audio> result = [];

    for(int i=0; i < curriculum.length ; i++){
      result.add(new Audio(
        name: curriculum[i].title
      ));
    }

    return result;
  }
}