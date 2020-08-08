import 'package:flutter/cupertino.dart';
import 'package:inject/inject.dart';
import 'package:smarty/abstracts/module.dart';
import 'package:smarty/authorization/ui/screen/login_page/login_page.dart';
import 'package:smarty/authorization/ui/screen/register_page/register_page.dart';

@provide
class AuthorizationModule extends Module {
  static const ROUTE_LOGIN_PAGE = '/login';
  static const ROUTE_REGISTER_PAGE = '/register';

  final LoginPage _loginPage;
  final RegisterPage _registerPage;

  AuthorizationModule(this._loginPage, this._registerPage);

  Map<String, WidgetBuilder> getRoutes() {
    return {
      ROUTE_LOGIN_PAGE: (context) => _loginPage,
      ROUTE_REGISTER_PAGE: (context) => _registerPage
    };
  }
}