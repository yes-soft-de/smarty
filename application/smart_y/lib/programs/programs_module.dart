import 'package:inject/inject.dart';
import 'package:smarty/abstracts/module.dart';
import 'package:smarty/programs/ui/screen/program_details_page/program_details_page.dart';
import 'package:smarty/programs/ui/screen/programs_page/programs_page.dart';

@provide
class ProgramsModule extends Module{

  static const ROUTE_PROGRAMS = '/programs';
  static const ROUTE_PROGRAM_DETAILS ='/programs_details';

  ProgramsPage _programsPage;
  ProgramDetailsPage _programDetailsPage;
  ProgramsModule(this._programsPage,this._programDetailsPage);
  @override
  getRoutes(){
    return{

      ROUTE_PROGRAMS: (context) => _programsPage,
      ROUTE_PROGRAM_DETAILS: (context) => _programDetailsPage,
    };
  }
}