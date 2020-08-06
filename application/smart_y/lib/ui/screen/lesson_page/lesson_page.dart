import 'package:flutter/material.dart';
import 'package:smarty/ui/widget/image_icon/image_icon.dart';

class LessonPage extends StatefulWidget {
  @override
  _LessonPageState createState() => _LessonPageState();
}

class _LessonPageState extends State<LessonPage> {
  @override
  Widget build(BuildContext context) {
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
                  Text('Course name',
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

               child:   Text('the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson the content of the lesson',
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
