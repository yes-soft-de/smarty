//import 'package:firebase_analytics/firebase_analytics.dart';
//import 'package:firebase_analytics/observer.dart';
import 'package:audio_manager/audio_manager.dart';
import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:firebase_analytics/observer.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_crashlytics/firebase_crashlytics.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:inject/inject.dart';
import 'package:smarty/authorization/authorization_component.dart';
import 'package:smarty/courses/course_module.dart';
import 'package:smarty/home/home_module.dart';
import 'package:smarty/programs/programs_module.dart';

import 'di/components/app.component.dart';
import 'generated/l10n.dart';
import 'meditation/Meditation_module.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
  ]).then((_) async {
    final container = await AppComponent.create();
    runApp(container.app);
  });
}

@provide
class MyApp extends StatelessWidget {
  static FirebaseAnalytics analytics = FirebaseAnalytics();
  static FirebaseAnalyticsObserver observer =
      FirebaseAnalyticsObserver(analytics: analytics);

  // Modulation in Progress :)
  final HomeModule _homeModule;
  final AuthorizationModule _authorizationModule;
  final CourseModule _courseModule;
  final ProgramsModule _programsModule;
  final MeditationModule _meditationModule;

  MyApp(
    this._homeModule,
    this._authorizationModule,
    this._courseModule,
    this._programsModule,
    this._meditationModule,
  );

  @override
  Widget build(BuildContext context) {
    FlutterError.onError = Crashlytics.instance.recordFlutterError;
    
    AudioManager.instance.onEvents((events, args) {
      print("$events, $args");
    });
    
    Map<String, WidgetBuilder> fullRoutesList = Map();

    fullRoutesList.addAll(_homeModule.getRoutes());
    fullRoutesList.addAll(_authorizationModule.getRoutes());
    fullRoutesList.addAll(_courseModule.getRoutes());
    fullRoutesList.addAll(_programsModule.getRoutes());
    fullRoutesList.addAll(_meditationModule.getRoutes());

    return MaterialApp(
        navigatorObservers: <NavigatorObserver>[
          observer
        ],
        localizationsDelegates: [
          S.delegate,
          GlobalMaterialLocalizations.delegate,
          GlobalWidgetsLocalizations.delegate,
          GlobalCupertinoLocalizations.delegate,
        ],
        theme: ThemeData(
            primaryColor: Colors.greenAccent, accentColor: Colors.greenAccent),
        supportedLocales: S.delegate.supportedLocales,
        title: 'Smart Y',
        routes: fullRoutesList,
        initialRoute: AuthorizationModule.ROUTE_LOGIN_PAGE);
  }
}
