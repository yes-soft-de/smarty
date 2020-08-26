import 'package:inject/inject.dart';
import 'package:smarty/abstracts/module.dart';
import 'package:smarty/home/ui/screen/consulting_page/consulting_page.dart';
import 'package:smarty/home/ui/screen/home_page/home_page.dart';
import 'package:smarty/home/ui/screen/meditation_page/meditation_page.dart';
import 'package:smarty/home/ui/screen/news_and_events_page/news_and_evens_page.dart';
import 'package:smarty/home/ui/screen/notification_page/notification_page.dart';

@provide
class HomeModule extends Module {

  static const ROUTE_HOME = '/home';
  static const ROUTE_MEDITATION = 'meditation';
  static const ROUTE_EVENT_AND_NEWS= '/events_and_news';
  static const ROUTE_CONSULTING= '/consulting';
  static const ROUTE_NOTIFICATION= '/notification';



  HomePage _homePage;
  MeditationPage _meditationPage;


  NewsAndEventsPAge _newsAndEventsPAge;
  ConsultingPage _consultingPage;
  NotificationPage _notificationPage;

  HomeModule(this._meditationPage, this._homePage,

      this._newsAndEventsPAge,this._consultingPage,
      this._notificationPage,
      );

  @override
  getRoutes() {
    return {
      ROUTE_HOME: (context) => _homePage,
      ROUTE_MEDITATION: (context) => _meditationPage,
      ROUTE_EVENT_AND_NEWS : (context) => _newsAndEventsPAge,
      ROUTE_CONSULTING : (context) => _consultingPage,
      ROUTE_NOTIFICATION :(context) => _notificationPage,
    };
  }
}
