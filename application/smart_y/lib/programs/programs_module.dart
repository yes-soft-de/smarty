import 'package:inject/inject.dart';
import 'package:smarty/abstracts/module.dart';
import 'package:smarty/programs/ui/screen/programs_page/programs_page.dart';

@provide
class ProgramsModule extends Module{

  static const ROUTE_PROGRAMS = '/programs';

  ProgramsPage _programsPage;

  ProgramsModule(this._programsPage,);
  @override
  getRoutes(){
    return{

      ROUTE_PROGRAMS: (context) => _programsPage,
    };
  }
}