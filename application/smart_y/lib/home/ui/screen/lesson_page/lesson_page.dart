import 'package:flutter/material.dart';
import 'package:smarty/home/bloc/lesson_page/lesson_page.bloc.dart';
import 'package:smarty/home/model/lesson/lesson.dart';
import 'package:smarty/home/ui/widget/image_icon/image_icon.dart';
import 'package:smarty/home/ui/widget/loading_indicator/loading_indicator.dart';
import 'package:smarty/utils/logger/logger.dart';

class LessonPage extends StatefulWidget {
  final int lessonId;
  final String tag = "LessonPage";

  final LessonPageBloc _lessonPageBloc;
  final Logger _logger;

  LessonPage(this.lessonId,this._lessonPageBloc,this._logger);

  @override
  _LessonPageState createState() => _LessonPageState();
}

class _LessonPageState extends State<LessonPage> {
  int currentState = LessonPageBloc.STATUS_CODE_INIT;
  Lesson lesson;

  @override
  Widget build(BuildContext context) {
   widget._lessonPageBloc.lessonStateObservable.listen((stateChanged) {
     currentState = stateChanged.first;

     if (currentState == LessonPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
       this.lesson = stateChanged.last;
     }

     setState(() {});
   });

   switch(currentState){
     case LessonPageBloc.STATUS_CODE_INIT: {
       widget._logger.info(widget.tag, "Lesson Page Started");
       widget._lessonPageBloc.getLesson(widget.lessonId);
       break;
     }
     case LessonPageBloc.STATUS_CODE_FETCHING_DATA: {
       widget._logger.info(widget.tag, "Fetching data from the server");
       return LoadingIndicatorWidget();
     }

     case LessonPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS: {
       widget._logger.info(widget.tag, "Fetching data SUCCESS");
       return getPageLayout();
     }

     case LessonPageBloc.STATUS_CODE_FETCHING_DATA_ERROR: {
       widget._logger.info(widget.tag, "Fetching data Error");
       return Scaffold(
           body: Center(
             child: Text("Fetching data Error"),
           ));
     }
     default:{
       // Undefined State
       widget._logger.error(widget.tag, "Undefined State");
       return Scaffold(
         body: Center(
           child: Text("Undefined State?!!"),
         ),
       );
     }
   }
  }



  Widget getPageLayout(){
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Color(0xff5E239D),
        leading: Builder(
          builder: (BuildContext context) {
            return IconButton(
              icon: ImageAsIconWidget(
                img:'assets/goback.png',
                height: 20.0,
                width: 30.0,
              ),
              onPressed: () {

              },

            );
          },
        ),
        title: Text('Introduce'),
      ),
      body: SingleChildScrollView(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Image(
              image: AssetImage('assets/course_image.png'),
              width: MediaQuery.of(context).size.width,
            ),
            Container(
              padding: EdgeInsetsDirectional.fromSTEB(10, 10, 10, 10),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  Text('What is it?')
                ],
              ),
            ),
            Container(
              padding: EdgeInsetsDirectional.fromSTEB(10, 0, 10, 10),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  Text(lesson.title,
                    style: TextStyle(
                      color: Color(0xff5E239D),
                      fontSize: 10.0,
                    ),
                  )
                ],
              ),
            ),
            Container(
              width: MediaQuery.of(context).size.width*0.98,
              padding: EdgeInsetsDirectional.fromSTEB(10, 0, 10, 10),

              child:   Text(lesson.content,
              ),

            ),

            Container(
              height:3,
              width: MediaQuery.of(context).size.width*0.94,
              color: Colors.black87,
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: <Widget>[
                FlatButton(
                  onPressed: (){},
                  child: Text('<< Previous chapter' ,
                    style: TextStyle(
                      fontSize: 11,
                    ),
                  ),

                ),
                Container(

                  height: 30,
                  width: 2,
                  color: Colors.black54,

                ),
                FlatButton(
                  onPressed: (){},
                  child: Text('Next chapter >>',
                    style: TextStyle(
                      fontSize: 11,
                    ),
                  ),
                ),
              ],
            ),

            Container(
              padding: EdgeInsetsDirectional.fromSTEB(0, 0, 0, 20),
              height:3,
              color: Colors.black54,
            ),

          ],
        ),
      ),

    );
  }
}
