import 'package:flutter/material.dart';
import 'package:smarty/bloc/courses_page/courses_page.bloc.dart';
import 'package:smarty/model/course/course_list_item.model.dart';
import 'package:smarty/ui/widget/app_drawer/app_drawer.dart';
import 'file:///D:/YesSoft%20projects/Smart%20Y/the%20flutter%20project/smarty/application/smart_y/lib/ui/widget/course_card/course_card.dart';
import 'file:///D:/YesSoft%20projects/Smart%20Y/the%20flutter%20project/smarty/application/smart_y/lib/ui/widget/image_icon/image_icon.dart';

import 'package:inject/inject.dart';
import 'file:///D:/YesSoft%20projects/Smart%20Y/the%20flutter%20project/smarty/application/smart_y/lib/ui/widget/loading_indicator/loading_indicator.dart';
import 'file:///D:/YesSoft%20projects/Smart%20Y/the%20flutter%20project/smarty/application/smart_y/lib/ui/widget/smarty_app_bar/smarty_app_bar.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class CoursesPage extends StatefulWidget {
  final String tag = "CoursesPage";

  final CoursesPageBloc _coursesPageBloc;
  final Logger _logger;

  CoursesPage(this._coursesPageBloc, this._logger);

  @override
  _CoursesPageState createState() => _CoursesPageState();
}

class _CoursesPageState extends State<CoursesPage> {
  int currentState = CoursesPageBloc.STATUS_CODE_INIT;
  List<CourseModel> courses;

  @override
  Widget build(BuildContext context) {
    widget._coursesPageBloc.loginStateObservable.listen((stateChanged) {
      currentState = stateChanged.first;

      if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
        this.courses = stateChanged.last;
      }

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
      widget._logger.info(widget.tag, "Fetching data SUCCESS");
      return getPageLayout();
    }

    if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA_ERROR) {
      widget._logger.info(widget.tag, "Fetching data Error");
      return Scaffold(
          body: Center(
        child: Text("Fetching data Error"),
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
      appBar: SmartyAppBar(appBar: AppBar(),title: 'Courses',),
      drawer: AppDrawer(),
      body: Container(
        color: Color(0xffF4ECEC),
        child: Stack(
          children: <Widget>[
            ListView.builder(
                itemCount: courses.length,
                padding: EdgeInsetsDirectional.fromSTEB(15, 50, 15, 10),
                itemBuilder: (BuildContext context, int index) {
                  return CourseCard(
                    image: 'assets/yoga.jpg',
                    price: 50,
                    chapters: 42,
                    name: courses[index].title,
                    description: courses[index].content,
                  );
                }),
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
                        onPressed: () {},
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
                        onPressed: () {},
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
