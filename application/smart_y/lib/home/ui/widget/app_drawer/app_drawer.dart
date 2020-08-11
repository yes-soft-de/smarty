import 'package:flutter/material.dart';

import '../../../home_module.dart';

class AppDrawerWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: MediaQuery.of(context).size.width*0.75,
      child: Drawer(

        child: Container(

          decoration: BoxDecoration(
              gradient: LinearGradient(
                  colors: [Color(0xff30124E), Color(0xff1F2E35)],
                  begin: Alignment.centerLeft,
                  end: Alignment.centerRight,
                  stops: [0.6, 1])),

          child: ListView(
            children: <Widget>[
              ListTile(
                title: Text('Smarty',
                  style: TextStyle(
                    color: Colors.white,
                  ),
                ) ,
              ),

              FlatButton(
                onPressed: (){
                  Navigator.pushNamed(context, HomeModule.ROUTE_HOME);
                },
                child: ListTile(
                  title: Text("Home",
                  style: TextStyle(
                    color: Colors.white,
                  ),
                  ),
                  trailing: Icon(
                    Icons.home,
                  color: Colors.white,
                  ),
                ),
              ),
              FlatButton(
                onPressed: (){
                  Navigator.pushNamed(context, HomeModule.ROUTE_COURSE_LIST);
                },
                child: ListTile(
                  title: Text("Courses",
                    style: TextStyle(
                        color: Colors.white,
                    ),
                  ),
                  trailing: Icon(
                      Icons.book,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
