// NOTE: user material and only material
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/bloc/login_page/login_page.bloc.dart';
import 'package:smarty/utils/logger/logger.dart';

// NOTE: All Pages are Statefull Widgets
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
      return Scaffold(
          body: Center(
        child: Text("Login Success!"),
      ));
    }

    if (currentState == LoginPageBloc.STATUS_CODE_AUTH_ERROR) {
      // TODO: Show an Error Message on the Login Indicator, and Remove this
      widget._logger.info(widget.tag, "AUTH Error");
      return Scaffold(
          body: Center(
        child: Text("Login Error"),
      ));
    }

    if (currentState == LoginPageBloc.STATUS_CODE_CREDENTIALS_SENT) {
      // TODO: Stop submitting more requests until we get a response
      widget._logger.info(widget.tag, "Sending Login Request");
      return getPageLayout();
    }

    if (currentState == LoginPageBloc.STATUS_CODE_INIT) {
      // RECOMMENDATION: Stick to more general coding style, where we return at the end
      widget._logger.info(widget.tag, "Login Page Started");
      return getPageLayout();
    }

    // Undefined State
    widget._logger.error(widget.tag, "Undefined State");
    return Scaffold(
      body: Center(
        child: Text("Undefined State?!!"),
      ),
    );
  }

  // Always Return a Scaffold from a screen, consistency is the key here
  Widget getPageLayout() {
    // Build Based on Current State :)
    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
            gradient: LinearGradient(
                colors: [Color(0xff5E239D), Color(0xffDCDCDE)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                stops: [0.6, 1])),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Image(
              image: AssetImage('assets/Logo.png'),
            ),
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
            // TODO: Use forms text fields with Validators
            TextField(
              decoration: InputDecoration(
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
            ),
            // TODO: Use forms text fields with Validators
            TextField(
              decoration: InputDecoration(
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
            ),
            Container(
              margin: EdgeInsets.fromLTRB(30, 30, 30, 0),
              child: FlatButton(
                padding: EdgeInsets.fromLTRB(0, 15, 0, 15),
                onPressed: () {},
                color: Color(0xffDCDCDE),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: <Widget>[
                    // TODO: Switch This to a button or a Gesture Detector, and disable it if currentStatus STATUS_CODE_CREDENTIALS_SENT
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
                  // TODO: Use Logger info method
                  print('ddddd');
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
    );
  }

  login() {
    // Get the text from TextFormControl and Submit it to the Bloc
    // No Need to return values, it will show in the stream above
    widget._loginPageBloc.login("Mohammad", "password");
  }
}
