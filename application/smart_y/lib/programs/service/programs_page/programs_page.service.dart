
import 'package:inject/inject.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/programs/manager/programs/programs.manager.dart';
import 'package:smarty/programs/model/program/program_model.dart';

@provide
class ProgramsService{
  ProgramsManager _programsManager;

  ProgramsService(this._programsManager);

 Future<List<ProgramModel>> getPrograms() async{

   List<CourseDetailsResponse> programsResponse =
       await this._programsManager.getPrograms();

   if(programsResponse == null )
     return null;


   List<ProgramModel> availablePrograms = [];

   programsResponse.forEach((element) {
     availablePrograms.add(
       new ProgramModel(
           id: element.course.id,
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