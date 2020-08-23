import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/home/bloc/courses_details_page/courses_details_page.bloc.dart';
import 'package:smarty/home/bloc/courses_page/courses_page.bloc.dart';
import 'package:smarty/home/model/course/course_list_item.model.dart';
import 'package:smarty/home/model/course_model/course_details.dart';
import 'package:smarty/home/model/lesson/lesson.dart';
import 'package:smarty/home/model/section/secction.dart';
import 'package:smarty/home/ui/widget/course_section_lessons/course_section_lessons.dart';
import 'package:smarty/home/ui/widget/image_icon/image_icon.dart';
import 'package:smarty/home/ui/widget/loading_indicator/loading_indicator.dart';
import 'package:smarty/utils/logger/logger.dart';

@provide
class CourseDetailPage extends StatefulWidget {
  final String tag = "CourseDetailsPage";

  final CourseDetailsBloc _courseDetailsBloc;
  final Logger _logger;


  CourseDetailPage(this._courseDetailsBloc,this._logger) ;

  @override
  _CourseDetailPageState createState() => _CourseDetailPageState();
}

class _CourseDetailPageState extends State<CourseDetailPage> {


  int currentState = CoursesPageBloc.STATUS_CODE_INIT;
  List<Section> sections;
  CourseDetails courseDetails;
  CourseModel _course;

  //mockup data
  List<Lesson> lessons = [new Lesson(title:'lesson 1',id:1),new Lesson(title:'lesson 2',id:2),new Lesson(title:'lesson 3',id:3),new Lesson(title:'lesson 4',id:4)];
  List<Lesson> lessons2 = [new Lesson(title:'lesson 1',id:5),new Lesson(title:'lesson 2',id:6),new Lesson(title:'lesson 3',id:7),new Lesson(title:'lesson 4',id:8),new Lesson(title:'lesson 5',id:9)];

  @override
  Widget build(BuildContext context) {
    _course = ModalRoute.of(context).settings.arguments;

    widget._courseDetailsBloc.courseDetailsStateObservable.listen((stateChanged) {
      currentState = stateChanged.first;

      if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
//        this.sections = stateChanged.last;
          this.courseDetails = stateChanged.last;
      }

      if(this.mounted){
        setState(() {});
      }

    });

    if(currentState == CourseDetailsBloc.STATUS_CODE_INIT){
      widget._logger.info(widget.tag, "Course details Page Started");
      widget._courseDetailsBloc.getCourseDetails(_course.id);
    }

    if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA) {
      widget._logger.info(widget.tag, "Fetching data from the server");
      return LoadingIndicatorWidget();
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

                icon: ImageAsIconWidget(
                  img:'assets/goback.png',
                  height: 20.0,
                  width: 30.0,
                ),
                onPressed: () {
                  Navigator.pop(context);
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
                image: NetworkImage(_course.image),
                width: MediaQuery.of(context).size.width,
              ),
              Container(
                padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 20),
                child: Row(

                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: <Widget>[
                    Container(
                        width:MediaQuery.of(context).size.width*0.4,
                        child: Text('${_course.title}')),
                    FlatButton(
                      onPressed: (){},
                      color: Color(0xff5E239D),
                      child: Text(
                        'Start',
                        style: TextStyle(
                          color: Colors.white,
                        ),
                      ),
                    )
                  ],
                ),
              ),

              Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  SizedBox(width: MediaQuery.of(context).size.width*0.07,),
                  Text('Course category',
                    style: TextStyle(
                      fontSize: 10,
                      color: Color(0xff5E239D),
                    ),
                  ),

                ],
              ),

              //horizontal space
              Container(
                height: 10,
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  SizedBox(width: MediaQuery.of(context).size.width*0.07,),
                  Text('Estimation time 1 day' ,
                    style: TextStyle(
                        fontSize: 12
                    ),
                  ),
                  VerticalDivider(width: 30,),
                  Text('For ${_course.price} \$',
                    style: TextStyle(
                        fontSize: 12
                    ),
                  ),
                ],
              ),
              //horizontal space
              Container(
                height: 10,
              ),


              Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  SizedBox(width: MediaQuery.of(context).size.width*0.07,),
                  Container(
                    width: MediaQuery.of(context).size.width*0.9,
                    child: Text(courseDetails.description,

                    ),
                  ),
                ],
              ),
//horizontal space
              Container(
                height: 10,
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  SizedBox(width: MediaQuery.of(context).size.width*0.07,),
                  Text('Course content' ,
                    style: TextStyle(
                        fontSize: 12
                    ),
                  ),
                  VerticalDivider(width: 70,),
                  Text('42 Chapters',
                    style: TextStyle(
                        fontSize: 12
                    ),
                  ),
                ],
              ),

              Divider(),

               sectionsColumn(courseDetails.sections),




              Divider(),


              //comments && likes number
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

              //comment field
              Container(
                padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 20),
                child: Row(
                  children: <Widget>[
                    Container(
                      padding: EdgeInsetsDirectional.fromSTEB(0, 0, 10, 0),
                      child: Image(

                        image: AssetImage('assets/profile_pic.png'),
                        height: MediaQuery.of(context).size.width*0.17,
                      ),
                    ),

                    Container(
                      width: MediaQuery.of(context).size.width*0.6,
                      height: MediaQuery.of(context).size.width*0.16,
                      child: TextField(
                        textAlign: TextAlign.start,
                        keyboardType: TextInputType.text,
                        decoration: InputDecoration(
                            suffixIcon: Icon(Icons.attach_file,color: Colors.grey,),
                            hintStyle: TextStyle(fontSize: 10),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(15),
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
              ),

              //previous comments
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


            ],
          ),
        )

    );



  }

  Widget sectionsColumn(List<Section> sections){
    List<Widget> list = new List<Widget>();
    for(var i = 0; i < sections.length; i++){
      list.add(new  Container(
          height: MediaQuery.of(context).size.height*0.6,
          child:sections[i].lessons.length>0?
          CourseSectionLessons(sectionName: sections[i].title,lessons: sections[i].lessons,):
          Container(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Text(
                      '${sections[i].title}'
                    ),
                  ],
                ),
                Text(
                  'No Lessons',
                  style: TextStyle(
                    color: Colors.redAccent,
                  ),
                )
              ],
            ),
          )
      )

      );
    }
    return new Column(children: list);


  }
}
