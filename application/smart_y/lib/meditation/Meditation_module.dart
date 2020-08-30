
import 'package:inject/inject.dart';
import 'package:smarty/abstracts/module.dart';
import 'package:smarty/meditation/ui/screen/meditation_details_page/meditation_details_page.dart';
import 'package:smarty/meditation/ui/screen/meditation_page/meditation_page.dart';
import 'package:smarty/meditation/ui/screen/meditation_setting_page/meditation_setting_page.dart';


@provide
class MeditationModule extends Module {

  static const ROUTE_MEDITATION = '/meditation';
  static const ROUTE_MEDITATION_DETAILS = 'meditation_dtails';
  static const ROUTE_MEDITATION_SETTING = '/meditation_setting';

  MeditationPage _meditationPage;
  MeditationDetailsPage _meditationDetailsPage;
  MeditationSettingPage _meditationSettingPage;

  MeditationModule(
      this._meditationDetailsPage,this._meditationPage,
      this._meditationSettingPage,
      );

  @override
  getRoutes() {
    return {
      ROUTE_MEDITATION: (context) => _meditationPage,
      ROUTE_MEDITATION_DETAILS: (context) => _meditationDetailsPage,
      ROUTE_MEDITATION_SETTING: (context) => _meditationSettingPage,
    };
  }

}