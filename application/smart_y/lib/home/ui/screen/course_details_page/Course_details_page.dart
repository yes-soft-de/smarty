import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/home/model/lesson/lesson.dart';
import 'package:smarty/home/ui/widget/course_section/course_section_lessons.dart';
import 'package:smarty/home/ui/widget/image_icon/image_icon.dart';

@provide
class CourseDetailPage extends StatefulWidget {
  @override
  _CourseDetailPageState createState() => _CourseDetailPageState();
}

class _CourseDetailPageState extends State<CourseDetailPage> {
  //mockup data
  List<Lesson> lessons = [new Lesson('lesson 1'),new Lesson('lesson 2'),new Lesson('lesson 3'),new Lesson('lesson 4')];
  List<Lesson> lessons2 = [new Lesson('lesson 1'),new Lesson('lesson 2'),new Lesson('lesson 3'),new Lesson('lesson 4'),new Lesson('lesson 5')];

  @override
  Widget build(BuildContext context) {
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
            Container(
                height: MediaQuery.of(context).size.height*0.4,
                child: CourseSectionLessonsWidget(sectionName: 'Getting started',lessons: lessons,)
            ),
            Container(
                height: MediaQuery.of(context).size.height*0.4,
                child: CourseSectionLessonsWidget(sectionName: 'Introduction',lessons: lessons2,)
            ),

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
}
