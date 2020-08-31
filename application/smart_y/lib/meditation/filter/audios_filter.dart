import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/meditation/model/meditation_details.dart';
import 'package:smarty/utils/media_extractor/media_extractor.dart';

class AudiosFilter{

 static List<Audio> getAudios( List<CourseDetailsResponse>  curriculum){
    List<Audio> result = [];

    for(int i=0; i < curriculum.length ; i++){
      result.add(new Audio(
        name: curriculum[i].course.name,
        audioUrl:  MediaExtractor.extractMedia( curriculum[i].description)
      ));
    }

    return result;
  }
}