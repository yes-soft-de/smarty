import 'package:flutter/material.dart';
import 'package:smarty/courses/model/lesson/lesson.dart';
import 'package:smarty/shared/ui/widget/image_icon/image_icon.dart';

class CourseSectionLessons extends StatelessWidget {
  final String sectionName;
  final List<Lesson> lessons;
  CourseSectionLessons({@required this.lessons,@required this.sectionName})
      :assert(sectionName != null && lessons != null);
  @override
  Widget build(BuildContext context) {
    return Container(

      child: Stack(
        children: <Widget>[
          ListView.builder(
            scrollDirection: Axis.horizontal,
            itemCount: lessons.length,
            padding: EdgeInsetsDirectional.fromSTEB(15 , 50, 15, 10),
            itemBuilder: (BuildContext context, int index){
              return Card(
                color: Color(0xff3ED598),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(0.0),
                ),
                child: Container(
                  padding: EdgeInsetsDirectional.fromSTEB(10, 10, 10, 10),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: <Widget>[

                      Container(
                        width:MediaQuery.of(context).size.width*0.4,
                        child: Text(lessons[index].title,
                          style: TextStyle(color: Colors.white),
                        ),
                      ),
                      Text('${lessons[index].duration} min',
                        style: TextStyle(color: Colors.white),
                      ),
                      Text('${index+1} of ${lessons.length}',
                        style: TextStyle(color: Colors.white),),

                      Row(

                        children: <Widget>[
                          ImageAsIconWidget(
                            width: 25,
                            height: 25,
                            img: 'assets/Play2.png',
                          ),
                          FlatButton(
                              onPressed: (){},
                              child: Text('Start',style: TextStyle(color: Colors.white ,fontSize: 10),
                              )
                          )
                        ],
                      ),


                    ],
                  ),
                ),
              );
            },
          ),
          Positioned(
            left: 0.0,
            right: 0.0,
            top: 0.0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.start,
              children: <Widget>[
                SizedBox(width: MediaQuery.of(context).size.width*0.07,),
                Container(
                    width: MediaQuery.of(context).size.width*0.8,
                    child: Text(sectionName)
                ),
              ],
            ),
          ),
        ],
      ),


    );
  }
}
