import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';


class LoginPage extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(

      decoration: BoxDecoration(
          gradient: LinearGradient(
              colors: [Color(0xff5E239D),Color(0xffDCDCDE)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              stops: [0.6,1]
          )
      ),
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
                )
            ),
          ),
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
                )
            ),
          ),


          Container(
            margin: EdgeInsets.fromLTRB(30, 30, 30, 0),
            child: FlatButton(
              padding: EdgeInsets.fromLTRB(0, 15, 0, 15),
              onPressed: (){},
              color:Color(0xffDCDCDE),

              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: <Widget>[
                  Text(
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
              onPressed: (){print('ddddd');},
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

    );
  }
}
