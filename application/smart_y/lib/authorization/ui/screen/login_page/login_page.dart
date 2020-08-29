import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/authorization/authorization_component.dart';
import 'package:smarty/authorization/bloc/login_page/login_page.bloc.dart';
import 'package:smarty/home/home_module.dart';
import 'package:smarty/shared/ui/widget/logo_widget/logo.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class LoginPage extends StatefulWidget {
  final String tag = "LoginPage";

  final LoginPageBloc _loginPageBloc;

  // If we have time to log every single second in this stage, we would do it, BUT we dont have the time
  final Logger _logger;

  LoginPage(this._loginPageBloc, this._logger);

  @override
  State<StatefulWidget> createState() => LoginPageState();
}

class LoginPageState extends State<LoginPage> {
  int currentState = LoginPageBloc.STATUS_CODE_INIT;
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  bool _autoValidate = false;

  String _userName;
  String _password;

  bool _buttonsDisabled = false;
  bool authError = false;

  @override
  Widget build(BuildContext context) {
    widget._loginPageBloc.loginStateObservable.listen((stateChanged) {
      // This can be used to calulate how many times the page refreshes
      widget._logger.info(widget.tag, "State Changed");
      currentState = stateChanged.first;
      // We Could use a message generated from a bloc with stateChanged.second
      setState(() {});
    });

    if (currentState == LoginPageBloc.STATUS_CODE_AUTH_SUCCESS) {
      // TODO: Move to Home using Navigator
      widget._logger.info(widget.tag, "AUTH SUCCESS");
      Future.delayed(Duration(milliseconds: 100), () {
        Navigator.pushReplacementNamed(context, HomeModule.ROUTE_HOME);
      });
    }

    if (currentState == LoginPageBloc.STATUS_CODE_AUTH_ERROR) {
      _buttonsDisabled = false;
      widget._logger.info(widget.tag, "AUTH Error");
      return getPageLayout();
    }

    if (currentState == LoginPageBloc.STATUS_CODE_CREDENTIALS_SENT) {
      // TODO: Stop submitting more requests until we get a response
      widget._logger.info(widget.tag, "Sending Login Request");
      _buttonsDisabled = true;
      return getPageLayout();
    }

    if (currentState == LoginPageBloc.STATUS_CODE_INIT) {
      // RECOMMENDATION: Stick to more general coding style, where we return at the end
      widget._logger.info(widget.tag, "Login Page Started");
      _buttonsDisabled = false;
      return getPageLayout();
    }

    // Undefined State
    widget._logger.error(widget.tag, "Undefined State");

    _buttonsDisabled = true;
    return getPageLayout();
  }

  // Always Return a Scaffold from a screen, consistency is the key here
  Widget getPageLayout() {
    // Build Based on Current State :)
    return Scaffold(
      body: Container(
        height: MediaQuery.of(context).size.height,
        padding: EdgeInsetsDirectional.fromSTEB(10.0, 0, 10.0, 0),
        decoration: BoxDecoration(
            gradient: LinearGradient(
                colors: [Color(0xff5E239D), Color(0xffDCDCDE)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                stops: [0.6, 1])),
        child: SafeArea(
          child: SingleChildScrollView(
            child: new Form(
              key: _formKey,
              autovalidate: _autoValidate,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: <Widget>[
                  LogoWidget(),
                  Text(
                    'Welcome !',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 25,
                    ),
                  ),
                  Text(
                    'Sign in to continue',
                    style: TextStyle(
                      color: Colors.white,
                    ),
                  ),
                  new TextFormField(
                    controller: _usernameController,
                    decoration: InputDecoration(
                        labelText: 'Email',
                        icon: Tab(
                          icon: Container(
                            child: Image(
                              image: AssetImage('assets/person.png'),
                              fit: BoxFit.cover,
                            ),
                            height: 30,
                            width: 20,
                          ),
                        )),
                    keyboardType: TextInputType.text,
                    validator: (username) => _validateName(username),
                  ),
                  new TextFormField(
                    controller: _passwordController,
                    obscureText: true,
                    decoration: InputDecoration(
                        labelText: 'Password',
                        icon: Tab(
                          icon: Container(
                            child: Image(
                              image: AssetImage('assets/password.png'),
                              fit: BoxFit.cover,
                            ),
                            height: 30,
                            width: 20,
                          ),
                        )),
                    keyboardType: TextInputType.visiblePassword,
                    validator: (password) => _validatePassword(password),
                  ),
                  Container(
                    margin: EdgeInsets.fromLTRB(30, 30, 30, 0),
                    child: FlatButton(
                      padding: EdgeInsets.fromLTRB(0, 15, 0, 15),
                      onPressed: /*(){  Navigator.pushReplacementNamed(context, HomeModule.ROUTE_HOME);},*/

                      _buttonsDisabled
                          ? null
                          : () => /*_validateInputsAndLogin()*/ login(),
                      color: Color(0xffDCDCDE),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: <Widget>[
                          Text(
                            // This needs to translate, but Later, the dependency is installed
                            'Sign in',
                            style: TextStyle(
                              color: Colors.white,
                            ),
                          ),
                          Icon(
                            Icons.arrow_forward,
                            color: Colors.white,
                          )
                        ],
                      ),
                    ),
                  ),
                  Container(
                    margin: EdgeInsets.fromLTRB(30, 30, 30, 0),
                    child: FlatButton(
                      padding: EdgeInsets.fromLTRB(0, 15, 0, 15),
                      color: Color(0xffDCDCDE),
                      onPressed: () {
                        Navigator.pushNamed(
                            context, AuthorizationModule.ROUTE_REGISTER_PAGE);
                      },
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: <Widget>[
                          Text(
                            'Create an account',
                            style: TextStyle(
                              color: Colors.white,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  String _validateName(String value) {
    if (value.length < 1)
      return 'Name can\'t be empty ';
    else
      return null;
  }

  String _validatePassword(String value) {
    if (value.length < 1)
      return 'Password can\'t be empty ';
    else
      return null;
  }

  void login() {
    if (_formKey.currentState.validate()) {
      widget._loginPageBloc.login(
          _usernameController.text.trim(), _passwordController.text.trim());
    }
  }
}
