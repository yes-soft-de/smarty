


import 'package:dio/dio.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:smarty/shared/ui/widget/circle_image/circle_iamge.dart';

class CardVideosName extends StatelessWidget {

  VoidCallback press;
  String name;
  String commentNumber;
  String loveNumber;

  CardVideosName({this.press, this.name,this.commentNumber,this.loveNumber});

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
                  MyCircularImage(70,70,),
                  SizedBox(width: 4,),
                  Flexible(child:
                    Text(name,style: TextStyle(color: Colors.black,fontSize: 16 ),),)

                ],
              ),
              SizedBox(height: 10,),
            Row(
              children: <Widget>[
                Expanded(
                  child: Container(
                    height: 200,


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
                    child: Text("weekly progress",style: TextStyle(fontSize: 14),),
                  ),
                  Expanded(
                    child: Text("2-2-2020",style: TextStyle(fontSize: 14),textAlign: TextAlign.end,),
                  ),
                ],
              ),
              SizedBox(height: 10,),
              Row(
                mainAxisSize: MainAxisSize.max,
                mainAxisAlignment: MainAxisAlignment.start,
                children: <Widget>[
                  Icon(Icons.comment,color: Colors.grey,),
                  SizedBox(width: 4,),
                  Flexible(
                    child: Text(commentNumber,style: TextStyle(fontSize: 14,color:  Colors.grey,),),
                  ),
                  SizedBox(width: 4,),
                  Icon(Icons.assistant_photo,color:  Colors.grey,),
                  SizedBox(width: 4,),
                  Flexible(
                    child: Text(loveNumber,style: TextStyle(fontSize: 14,color:  Colors.grey,),),
                  ),
                  SizedBox(width: 4,),
                ],
              )


            ],
          ),
        ),
      ),
    );
  }
}
