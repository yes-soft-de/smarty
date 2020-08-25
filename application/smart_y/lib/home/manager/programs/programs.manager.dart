

import 'package:inject/inject.dart';
import 'package:smarty/home/repository/programs_page/programs_page.repository.dart';
import 'package:smarty/home/response/course_details_response/course_details_response.dart';

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