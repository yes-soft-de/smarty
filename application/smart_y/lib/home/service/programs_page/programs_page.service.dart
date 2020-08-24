
import 'package:inject/inject.dart';
import 'package:smarty/home/manager/programs/programs.manager.dart';
import 'package:smarty/home/model/program/program_model.dart';
import 'package:smarty/home/response/course_details_response/course_details_response.dart';

@provide
class ProgramsService{
  ProgramsManager _programsManager;

 Future<List<ProgramModel>> getPrograms() async{
   List<CourseDetailsResponse> programsResponse =
       await _programsManager.getPrograms();

   if(programsResponse == null )
     return null;

   List<ProgramModel> availablePrograms = [];

   programsResponse.forEach((element) {
     availablePrograms.add(
       new ProgramModel(
           name: element.course.name,
           content: element.description,
           price: element.course.price,
           image: element.course.featuredImage,
           participant: element.course.totalStudents
       )
     );
   });

 return availablePrograms;
 }
}