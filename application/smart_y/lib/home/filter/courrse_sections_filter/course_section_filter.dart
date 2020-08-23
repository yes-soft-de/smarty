import 'package:smarty/home/model/lesson/lesson.dart';
import 'package:smarty/home/model/section/secction.dart';
import 'package:smarty/home/response/course_details_response/course_details_response.dart';

class CourseSectionsFilter{

  static List<Section> getSections( List<Curriculum> curriculum){
    List<Section> sections = [];
    Section tempSection = null;
    curriculum.forEach((element) {
      if(element.type == 'section'){
        if(tempSection != null){
          sections.add(tempSection);
        }
       tempSection = new Section(title: element.title,id: element.id,lessons: []);
      }
      else if(element.type == 'unit'){
        print('its unit');
        tempSection.lessons.add(
            new Lesson(id: element.id,title: element.title,duration: element.duration,content: '')
        );
      }
    });
 return sections;
  }

}