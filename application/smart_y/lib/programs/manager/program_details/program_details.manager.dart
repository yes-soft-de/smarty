
import 'package:inject/inject.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/programs/repository/program_details_page/program_details_page.repository.dart';

@provide
class ProgramDetailsManager{

  ProgramDetailsRepository _programDetailsRepository;

  ProgramDetailsManager(this._programDetailsRepository);

  Future< CourseDetailsResponse> getProgramDetails(int programId) async{
    return await this._programDetailsRepository.getProgramDetails(programId);
  }
}