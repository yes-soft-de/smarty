import 'package:flutter/material.dart';
import 'package:smarty/ui/widget/logo_widget/logo.dart';
import 'package:smarty/ui/widget/text_field_with_image/text_field_with_image_icon.dart';

class SignUp1Page extends StatefulWidget {
  @override
  _SignUp1PageState createState() => _SignUp1PageState();
}

class _SignUp1PageState extends State<SignUp1Page> {
  @override
  Widget build(BuildContext context) {
    return Container(
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
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
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
              TextFieldWithImageIcon(
                  img: 'assets/person.png', hint: 'Your full name'),
              TextFieldWithImageIcon(
                  img: 'assets/person.png', hint: 'Username'),
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
          ),
        ),
      ),
    );
  }
}
