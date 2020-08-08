import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:firebase_analytics/observer.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:inject/inject.dart';
import 'package:smarty/authorization/authorization_component.dart';
import 'package:smarty/home/home_module.dart';

import 'di/components/app.component.dart';
import 'generated/l10n.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

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

  MyApp(this._homeModule, this._authorizationModule);

  @override
  Widget build(BuildContext context) {
    Map<String, WidgetBuilder> fullRoutesList = Map();

    fullRoutesList.addAll(_homeModule.getRoutes());
    fullRoutesList.addAll(_authorizationModule.getRoutes());

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
        initialRoute: AuthorizationModule.ROUTE_REGISTER_PAGE);
  }
}
