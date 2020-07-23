
import 'package:flutter/material.dart';
import 'package:smarty/bloc/courses_page/courses_page.bloc.dart';
import 'package:smarty/ui/widget/app_drawer.dart';
import 'package:smarty/ui/widget/course_card.dart';
import 'package:smarty/ui/widget/image_icon.dart';

import 'package:inject/inject.dart';
import 'package:smarty/ui/widget/loading_indicator.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class CoursesPage extends StatefulWidget {
  final String tag = "CoursesPage";

  final CoursesPageBloc _coursesPageBloc;
  // If we have time to log every single second in this stage, we would do it, BUT we dont have the time
  final Logger _logger;


  CoursesPage(this._coursesPageBloc, this._logger);

  @override
  _CoursesPageState createState() => _CoursesPageState();
}

class _CoursesPageState extends State<CoursesPage> {
  int currentState = CoursesPageBloc.STATUS_CODE_INIT;

  @override
  Widget build(BuildContext context) {
    widget._coursesPageBloc.loginStateObservable.listen((stateChanged) {
      // This can be used to calculate how many times the page refreshes
      widget._logger.info(widget.tag, "State Changed");
      currentState = stateChanged.first ;
      setState(() {});
    });

    if (currentState == CoursesPageBloc.STATUS_CODE_INIT) {

      widget._logger.info(widget.tag, "Courses List Page Started");
      widget._coursesPageBloc.getCourses();
    }

    if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA) {

      widget._logger.info(widget.tag, "Fetching data from the server");
      return LoadingIndicator();
    }

    if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
      // TODO: Move to Home using Navigator
      widget._logger.info(widget.tag, "Fetching data SUCCESS");
      return getPageLayout();
    }

    if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA_ERROR) {
      // TODO: Show an Error Message on the Login Indicator, and Remove this
      widget._logger.info(widget.tag, "Fetching data Error");
      return Scaffold(
          body: Center(
            child: Text("Login Error"),
          ));
    }





    // Undefined State
    widget._logger.error(widget.tag, "Undefined State");
    return Scaffold(
      body: Center(
        child: Text("Undefined State?!!"),
      ),
    );
  }






    Widget getPageLayout() {
      return Scaffold(
        appBar: AppBar(
          backgroundColor: Color(0xff5E239D),
          actions: <Widget>[
            IconButton(
              icon: ImageAsIcon(
                img:'assets/profilePic.png',
                height: 32.0,
                width: 32.0,
              ),
              onPressed: () {
                // do something
              },
            )
          ],
          leading: Builder(
            builder: (BuildContext context) {
              return IconButton(
                icon: ImageAsIcon(
                  img:'assets/drawer.png',
                  height: 20.0,
                  width: 30.0,
                ),
                onPressed: () {
                  Scaffold.of(context).openDrawer();
                },
                tooltip: MaterialLocalizations.of(context).openAppDrawerTooltip,
              );
            },
          ),

        ),

        drawer: AppDrawer(),
        body: Container(

          color: Color(0xffF4ECEC),
          child: Stack(
            children: <Widget>[
              ListView.builder(
                itemCount: widget._coursesPageBloc.courses.length,
                padding: EdgeInsetsDirectional.fromSTEB(15 , 50, 15, 10),
                itemBuilder: (BuildContext context, int index) {
                    return CourseCard(
                      image: 'assets/yoga.jpg',
                      price: 50,
                      chapters: 42,
                      name: widget._coursesPageBloc.courses[index].title.rendered,
                      description: widget._coursesPageBloc.courses[index].content.rendered,
                    );

                }

              ),
              Positioned(
                left: 0.0,
                right: 0.0,
                top: 0.0,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: <Widget>[
                    Row(
                      children: <Widget>[
                        IconButton(
                          onPressed: (){},
                          icon: ImageAsIcon(
                            img: 'assets/filter_icon.png',
                            width: 20,
                            height: 10,
                          ),
                        ),
                        Text('Filter')
                      ],
                    ),
                    Row(
                      children: <Widget>[
                        IconButton(
                          onPressed: (){},
                          icon: ImageAsIcon(
                            img: 'assets/filter_icon.png',
                            width: 20,
                            height: 10,
                          ),
                        ),
                        Text('Sort')
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),


        ),
      );
    }

  }

