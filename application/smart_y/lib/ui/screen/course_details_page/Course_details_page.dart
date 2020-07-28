import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/bloc/courses_details_page/courses_details_page.bloc.dart';
import 'package:smarty/bloc/courses_page/courses_page.bloc.dart';
import 'package:smarty/model/lesson/lesson.dart';
import 'package:smarty/model/section/secction.dart';
import 'package:smarty/ui/widget/course_section_lessons.dart';
import 'package:smarty/ui/widget/image_icon.dart';
import 'package:smarty/ui/widget/loading_indicator.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class CourseDetailPage extends StatefulWidget {
  final int courseId;
  final String tag = "CourseDetailsPage";

  final CourseDetailsBloc _courseDetailsBloc;
  final Logger _logger;

  CourseDetailPage( this.courseId,this._courseDetailsBloc,this._logger) ;

  @override
  _CourseDetailPageState createState() => _CourseDetailPageState();
}

class _CourseDetailPageState extends State<CourseDetailPage> {
  int currentState = CoursesPageBloc.STATUS_CODE_INIT;
  List<Section> sections;

  //mockup data
  List<Lesson> lessons = [new Lesson(title:'lesson 1',id:1),new Lesson(title:'lesson 2',id:2),new Lesson(title:'lesson 3',id:3),new Lesson(title:'lesson 4',id:4)];
  List<Lesson> lessons2 = [new Lesson(title:'lesson 1',id:5),new Lesson(title:'lesson 2',id:6),new Lesson(title:'lesson 3',id:7),new Lesson(title:'lesson 4',id:8),new Lesson(title:'lesson 5',id:9)];

  @override
  Widget build(BuildContext context) {
    widget._courseDetailsBloc.courseDetailsStateObservable.listen((stateChanged) {
      currentState = stateChanged.first;

      if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
        this.sections = stateChanged.last;
      }

      setState(() {});
    });

      if(currentState == CourseDetailsBloc.STATUS_CODE_INIT){
        widget._logger.info(widget.tag, "Course details Page Started");
        widget._courseDetailsBloc.getCourseDetails(widget.courseId);
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
        appBar: AppBar(
          backgroundColor: Color(0xff5E239D),
          leading: Builder(
            builder: (BuildContext context) {
              return IconButton(
                icon: ImageAsIcon(
                  img:'assets/goback.png',
                  height: 20.0,
                  width: 30.0,
                ),
                onPressed: () {

                },

              );
            },
          ),
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
                padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 20),
                child: Row(

                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: <Widget>[
                    Text('Weekly progress'),
                    Text('For 50 \$'),
                  ],
                ),
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  SizedBox(width: MediaQuery.of(context).size.width*0.1,),
                  Text('Weekly progress on dieting',
                    style: TextStyle(
                      color: Colors.grey,
                      fontSize: 9,
                    ),
                  ),
                ],
              ),

              Container(
                padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 10),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: <Widget>[
                    Row(
                      children: <Widget>[
                        Icon(Icons.favorite,color: Colors.grey,),
                        Text('42 Likes',
                          style: TextStyle(
                            color: Colors.grey,
                            fontSize: 10,
                          ),)
                      ],
                    ),
                    Row(
                      children: <Widget>[
                        Icon(Icons.comment,color: Colors.grey,),
                        Text('7 Comments',
                          style: TextStyle(
                            color: Colors.grey,
                            fontSize: 10,
                          ),)
                      ],
                    ),
                  ],
                ),
              ),

              Divider(),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: <Widget>[
                  Text('Lessons',
                    style: TextStyle(color:  Color(0xff5E239D)),)
                ],
              ),
              Divider(),

              sectionsColumn(sections),

              Container(
                padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 20),
                child: Row(
                  children: <Widget>[
                    Container(
                      padding: EdgeInsetsDirectional.fromSTEB(0, 0, 10, 0),
                      child: Image(

                        image: AssetImage('assets/profilePic.png'
                        ),
                        height: MediaQuery.of(context).size.width*0.2,
                      ),
                    ),
                    Column(
                      mainAxisAlignment: MainAxisAlignment.start,
                      children: <Widget>[
                        Text('Alex Smith'),
                        Text('20 April at 4:20 PM',
                            style: TextStyle(
                              color: Colors.grey,
                              fontSize: 9,
                            )
                        )
                      ],
                    )
                  ],
                ),
              ),

              Container (
                padding: const EdgeInsets.all(16.0),
                width: MediaQuery.of(context).size.width*0.85,
                child: Text ("Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2", textAlign: TextAlign.center),
              ),

              Divider(),
              Container(
                padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 10),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: <Widget>[

                    Row(
                      children: <Widget>[
                        Icon(Icons.comment,color: Colors.grey,),
                        Text('7 Comments',
                          style: TextStyle(
                            color: Colors.grey,
                            fontSize: 10,
                          ),)
                      ],
                    ),
                    Row(
                      children: <Widget>[
                        Icon(Icons.favorite,color: Colors.grey,),
                        Text('42 Likes',
                          style: TextStyle(
                            color: Colors.grey,
                            fontSize: 10,
                          ),)
                      ],
                    ),
                  ],
                ),
              ),
              Divider(),
              Container(
                padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 20),
                child: Row(
                  children: <Widget>[
                    Container(
                      padding: EdgeInsetsDirectional.fromSTEB(0, 0, 10, 0),
                      child: Image(

                        image: AssetImage('assets/profile_pic.png'),
                        height: MediaQuery.of(context).size.width*0.2,
                      ),
                    ),

                    Container(
                      width: MediaQuery.of(context).size.width*0.6,
                      child: TextField(
                        textAlign: TextAlign.start,
                        keyboardType: TextInputType.text,
                        decoration: InputDecoration(
                            suffixIcon: Icon(Icons.attach_file,color: Colors.grey,),
                            hintStyle: TextStyle(fontSize: 10),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: BorderSide(
                                width: 0,
                                style: BorderStyle.none,
                              ),
                            ),
                            filled: true,

                            fillColor: Color(0xffebecfd),
                            hintText: 'Write comment...'
                        ),
                      ),
                    ),


                  ],
                ),
              )
            ],
          ),
        ),
      );


  }

  Widget sectionsColumn(List<Section> sections){
    List<Widget> list = new List<Widget>();
    for(var i = 0; i < sections.length; i++){
      list.add(new  Container(
          height: MediaQuery.of(context).size.height*0.4,
          child: CourseSectionLessons(sectionName: sections[i].title,lessons: sections[i].lessons,)
      )

      );
    }
    return new Column(children: list);


  }
}
