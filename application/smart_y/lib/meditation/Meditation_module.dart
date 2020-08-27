
import 'package:inject/inject.dart';
import 'package:smarty/abstracts/module.dart';
import 'package:smarty/meditation/ui/screen/meditation_details_page/meditation_page.dart';
import 'package:smarty/meditation/ui/screen/meditation_page/meditation_page.dart';


@provide
class MeditationModule extends Module {

  static const ROUTE_MEDITATION = '/meditation';
  static const ROUTE_MEDITATION_DETAILS = 'meditation_dtails';

  MeditationPage _meditationPage;
  MeditationDetailsPage _meditationDetailsPage;

  MeditationModule(this._meditationDetailsPage,this._meditationPage);

  @override
  getRoutes() {
    return {
      ROUTE_MEDITATION: (context) => _meditationPage,
      ROUTE_MEDITATION_DETAILS: (context) => _meditationDetailsPage,
    };
  }

}