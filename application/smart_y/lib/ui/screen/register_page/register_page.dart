import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/ui/widget/logo_widget/logo.dart';

@provide
class RegisterPage extends StatefulWidget {
  @override
  State<StatefulWidget> createState() => RegisterPageState();
}

class RegisterPageState extends State<RegisterPage> {
  final GlobalKey signUpFormKey = GlobalKey<FormState>();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    PageView signUpPageView = PageView();

    return Scaffold(
      body: Container(
        alignment: Alignment.center,
        decoration: BoxDecoration(
            gradient: LinearGradient(
                colors: [Color(0xff5E239D), Color(0xffDCDCDE)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                stops: [0.6, 1])),
        child: Form(child: signUpPageView),
      ),
    );
  }

  Widget getStepOnePage() {
    return Flex(
      direction: Axis.vertical,
      crossAxisAlignment: CrossAxisAlignment.center,
      children: <Widget>[
        Logo(),
        Row(
          mainAxisAlignment: MainAxisAlignment.start,
          children: <Widget>[
            Text(
              'Hello!',
              style: TextStyle(
                color: Colors.white,
                fontSize: 25,
              ),
            ),
          ],
        ),
        Row(
          mainAxisAlignment: MainAxisAlignment.start,
          children: <Widget>[
            Text(
              'Lets introduce',
              style: TextStyle(
                color: Colors.white,
              ),
            ),
          ],
        ),
        // This needs a controller, i.e. it is a form field isn't it?
        // And since this is the case, we need validation
        TextFormField(
          decoration: InputDecoration(
              labelText: 'Full Name', icon: Image.asset('assets/person.png')),
          validator: (name) {
            if (name.isEmpty) {
              return 'Please enter some text';
            }
            if (name.length < 5) {
              return 'Name is too short';
            }
            return null;
          },
          controller: _nameController,
        ),
        TextFormField(
          decoration: InputDecoration(
              labelText: 'Username', icon: Image.asset('assets/person.png')),
          validator: (name) {
            if (name.isEmpty) {
              return 'Please enter some text';
            }
            if (name.length < 5) {
              return 'Name is too short';
            }
            return null;
          },
          controller: _usernameController,
        ),
        Padding(
          padding: const EdgeInsets.all(10.0),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: <Widget>[
              Container(
                margin: EdgeInsetsDirectional.fromSTEB(0, 0, 7.5, 0),
                child: FlatButton(
                  padding: EdgeInsetsDirectional.fromSTEB(0, 15, 7.5, 15),
                  onPressed: () {},
                  color: Color(0xffDCDCDE),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: <Widget>[
                      Icon(
                        Icons.arrow_back,
                        color: Colors.white,
                      )
                    ],
                  ),
                ),
              ),
              Padding(
                padding: EdgeInsetsDirectional.fromSTEB(7.5, 0, 0, 0),
                child: FlatButton(
                  padding: EdgeInsetsDirectional.fromSTEB(7.5, 15, 0, 15),
                  onPressed: () {
                    _signUp();
                  },
                  color: Color(0xffDCDCDE),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: <Widget>[
                      Text(
                        'Next',
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
            ],
          ),
        ),
      ],
    );
  }

  Widget getStepTowPage() {
    return Flex(
      direction: Axis.vertical,
      children: <Widget>[
        Logo(),
        Row(
          mainAxisAlignment: MainAxisAlignment.start,
          children: <Widget>[
            Text(
              'Hello!',
              style: TextStyle(
                color: Colors.white,
                fontSize: 25,
              ),
            ),
          ],
        ),
        Row(
          mainAxisAlignment: MainAxisAlignment.start,
          children: <Widget>[
            Text(
              'Lets introduce',
              style: TextStyle(
                color: Colors.white,
              ),
            ),
          ],
        ),
        TextFormField(
          validator: (password) {
            if (password.isEmpty) return 'Please input your password';
            if (password.length < 6) return 'Short Password!';
            return null;
          },
          controller: _passwordController,
          decoration: InputDecoration(
              icon: Image.asset('assets/password.png'),
              hintText: 'Create a Password'),
        ),
        TextFormField(
          controller: _passwordController,
          validator: (passwordConfirmation) {
            if (passwordConfirmation.isEmpty)
              return 'Please input your password';
            if (passwordConfirmation.length < 6) return 'Short Password!';
            if (passwordConfirmation != _passwordController.text)
              return 'Passwords did not match';
            return null;
          },
          decoration: InputDecoration(
              icon: Image.asset('assets/password.png'),
              hintText: 'Create a Password'),
        ),
        Padding(
          padding: const EdgeInsets.all(10.0),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: <Widget>[
              Container(
                margin: EdgeInsetsDirectional.fromSTEB(0, 0, 7.5, 0),
                child: FlatButton(
                  padding: EdgeInsetsDirectional.fromSTEB(0, 15, 7.5, 15),
                  onPressed: () {},
                  color: Color(0xffDCDCDE),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: <Widget>[
                      Icon(
                        Icons.arrow_back,
                        color: Colors.white,
                      )
                    ],
                  ),
                ),
              ),
              Container(
                margin: EdgeInsetsDirectional.fromSTEB(7.5, 0, 0, 0),
                child: FlatButton(
                  padding: EdgeInsetsDirectional.fromSTEB(7.5, 15, 0, 15),
                  onPressed: () {},
                  color: Color(0xffDCDCDE),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: <Widget>[
                      Text(
                        'Next',
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
            ],
          ),
        ),
      ],
    );
  }

  _signUp() {}
}
