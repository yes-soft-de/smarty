import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/courses/course_module.dart';
import 'package:smarty/home/home_module.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';
import 'package:smarty/programs/programs_module.dart';


@provide
@singleton
class AppDrawerWidget extends StatelessWidget {
  final SharedPreferencesHelper _preferencesHelper;
  AppDrawerWidget(this._preferencesHelper );
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
              Container(
                padding: EdgeInsetsDirectional.fromSTEB(10, 20, 10, 10),
                child: Row(

                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Image(
                      height: 75,
                      width: 75,
                      image: AssetImage('assets/Rectangle16.png'),
                    ),
                    Text(
                      '${ _preferencesHelper.getUserEmail().toString()}',
                      style: TextStyle(
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),

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
                  Navigator.pushNamed(context, CourseModule.ROUTE_COURSE_LIST);
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
              FlatButton(
                onPressed: (){
                  Navigator.pushNamed(context, ProgramsModule.ROUTE_PROGRAMS);
                },
                child: ListTile(
                  title: Text("Programs",
                    style: TextStyle(
                      color: Colors.white,
                    ),
                  ),
                  trailing: Icon(
                    Icons.satellite,
                    color: Colors.white,
                  ),
                ),
              ),
              FlatButton(
                onPressed: (){
                  Navigator.pushNamed(context, HomeModule.ROUTE_MEDITATION);
                },
                child: ListTile(
                  title: Text("Meditation",
                    style: TextStyle(
                      color: Colors.white,
                    ),
                  ),
                  trailing: Icon(
                    Icons.spa,
                    color: Colors.white,
                  ),
                ),
              ),
//              FlatButton(
//                onPressed: (){
//                  Navigator.pushNamed(context, CourseModule.ROUTE_LESSON);
//                },
//                child: ListTile(
//                  title: Text("Lesson",
//                    style: TextStyle(
//                      color: Colors.white,
//                    ),
//                  ),
//                  trailing: Icon(
//                    Icons.book,
//                    color: Colors.white,
//                  ),
//                ),
//              ),
              FlatButton(
                onPressed: (){
                  Navigator.pushNamed(context, HomeModule.ROUTE_EVENT_AND_NEWS);
                },
                child: ListTile(
                  title: Text("Events & News",
                    style: TextStyle(
                      color: Colors.white,
                    ),
                  ),
                  trailing: Icon(
                    Icons.fiber_new,
                    color: Colors.white,
                  ),
                ),
              ),
              FlatButton(
                onPressed: (){
                  Navigator.pushNamed(context, HomeModule.ROUTE_CONSULTING);
                },
                child: ListTile(
                  title: Text("Consulting",
                    style: TextStyle(
                      color: Colors.white,
                    ),
                  ),
                  trailing: Icon(
                    Icons.question_answer,
                    color: Colors.white,
                  ),
                ),
              ),
              FlatButton(
                onPressed: (){
                  Navigator.pushNamed(context, HomeModule.ROUTE_NOTIFICATION);
                },
                child: ListTile(
                  title: Text("Notification",
                    style: TextStyle(
                      color: Colors.white,
                    ),
                  ),
                  trailing: Icon(
                    Icons.notifications,
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
