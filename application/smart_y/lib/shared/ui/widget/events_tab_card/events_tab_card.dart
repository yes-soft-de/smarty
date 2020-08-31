import 'package:flutter/material.dart';
import 'package:smarty/shared/ui/widget/circle_image/circle_iamge.dart';

class EventsTabCard extends StatelessWidget {
  final String content;
  final String avatar;
  final String instructor;

  EventsTabCard({this.content,this.avatar,this.instructor});
  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 20),
      child: new Card(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(0),
        ),
        child: Column(
          children: [
            Container(
              padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 0),
              child: Row(
                children: <Widget>[

                    MyCircularImage(
                      50,50,linkImg: avatar,

                  ),
                  Column(
                    mainAxisAlignment: MainAxisAlignment.start,
                    children: <Widget>[
                      Text(instructor),
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
              child: Text (
                  content,
                  textAlign: TextAlign.start,
                  style: TextStyle(
                    fontSize: 10
                  ),
              ),
            ),

            //participants number
            Container (
              padding: EdgeInsetsDirectional.fromSTEB(16.0, 0, 16.0, 16.0),
              width: MediaQuery.of(context).size.width*0.85,
              child: Text (
                '33 Participate',
                textAlign: TextAlign.start,
                style: TextStyle(
                    fontSize: 10
                ),
              ),
            ),

                Container(
                   height: MediaQuery.of(context).size.width*0.5,
                   width: MediaQuery.of(context).size.width*0.75,
                   child: Row(
                     mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                     children: [
                       ClipRRect(
                         borderRadius: BorderRadius.circular(15),
                         child: Image(
                           image: AssetImage('assets/Bitmap (3).png'),
                           width:MediaQuery.of(context).size.width*0.35 ,
                           height: MediaQuery.of(context).size.width*0.4,

                         ),
                       ),
                       Column(
                         mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                         children: [
                           ClipRRect(
                            borderRadius: BorderRadius.circular(13),
                             child: Image(
                               image: AssetImage('assets/Bitmap (1).png'),
                               width:MediaQuery.of(context).size.width*0.30 ,
                               height: MediaQuery.of(context).size.width*0.18,
                             ),
                           ),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(13),
                              child:Image(
                                image: AssetImage('assets/Bitmap (2).png'),
                                width:MediaQuery.of(context).size.width*0.30 ,
                                height: MediaQuery.of(context).size.width*0.18,
                              ),
                            ),
                         ],
                       )
                     ],
                   ),
           ),
          ],
        )
      ),
      decoration: new BoxDecoration(
         
        boxShadow: [
          new BoxShadow(
            color: Colors.black54,
            blurRadius: 10.0,
          ),
        ],
      ),
    );
  }
}
