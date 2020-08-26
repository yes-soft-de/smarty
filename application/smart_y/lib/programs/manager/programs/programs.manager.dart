

import 'package:inject/inject.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/programs/repository/programs_page/programs_page.repository.dart';

@provide
class ProgramsManager{
  ProgramsRepository _programsRepository;

  ProgramsManager(this._programsRepository);

  Future<List<CourseDetailsResponse>> getPrograms() async{
    List<CourseDetailsResponse> programsResponse=
        await this._programsRepository.getPrograms();


    return  programsResponse;
  }

}