


import 'package:dio/dio.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:smarty/shared/ui/widget/circle_image/circle_iamge.dart';

class CardVideosName extends StatelessWidget {

  VoidCallback press;
  String name;
  String commentNumber;
  String loveNumber;
  String instructorName;
  String avatar;

  CardVideosName({this.press,this.avatar,this.instructorName, this.name,this.commentNumber,this.loveNumber});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap:press ,
      child: Card(

        color: Colors.white,
        clipBehavior: Clip.hardEdge,
        elevation: 4,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10)
        ),
        child: Padding(
          padding: const EdgeInsets.all(8.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: <Widget>[
              SizedBox(height: 10,),
              Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  MyCircularImage(50,50,linkImg:avatar ,),
                  SizedBox(width: 4,),
                  Flexible(child:
                    Text(instructorName,style: TextStyle(color: Colors.black,fontSize: 14 ),),)

                ],
              ),
              SizedBox(height: 10,),
            Row(
              children: <Widget>[
                Expanded(
                  child: Container(
                    height: 150,


                      child: Card(
                        clipBehavior: Clip.hardEdge,
                   shape: RoundedRectangleBorder(
                     borderRadius: BorderRadius.circular(20)
                   ),
                        child:  Stack(
                          fit: StackFit.expand,
                          children: <Widget>[
                            Image.asset('assets/yoga.jpg',fit: BoxFit.cover,),
                            Align(
                              alignment: Alignment.center,
                              child: ClipOval(

                                  child: Container(color: Colors.white.withOpacity(0.4)

                                      ,child: Padding(
                                        padding: const EdgeInsets.all(8.0),
                                        child: Icon(Icons.play_arrow,size: 50,color: Colors.blue),
                                      ))),
                            )
                          ],
                        )
                      )),
                ),
              ],
            ),
              SizedBox(height: 10,),

              Row(
                mainAxisSize: MainAxisSize.max,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: <Widget>[
                  Expanded(
                    child: Text(name,style: TextStyle(fontSize: 12),),
                  ),
                  Expanded(
                    child: Text("2-2-2020",style: TextStyle(fontSize: 12),textAlign: TextAlign.end,),
                  ),
                ],
              ),
              SizedBox(height: 10,),
              Row(
                mainAxisSize: MainAxisSize.max,
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: <Widget>[

                  Row(
                    children: [
                      Icon(Icons.comment,color: Colors.grey,),
                      SizedBox(width: 4,),
                      Text(commentNumber,style: TextStyle(fontSize: 12,color:  Colors.grey,),),

                    ],
                  ),
                 Row(
                   children: [
                     Icon(Icons.favorite,color:  Colors.grey,),
                     SizedBox(width: 4,),
                      Text(loveNumber,style: TextStyle(fontSize: 12,color:  Colors.grey,),),


                   ],
                 )
                ],
              )


            ],
          ),
        ),
      ),
    );
  }
}
