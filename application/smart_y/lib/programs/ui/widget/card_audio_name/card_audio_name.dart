

import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:smarty/shared/ui/widget/circle_image/circle_iamge.dart';

class CardAudioName extends StatelessWidget {
  VoidCallback press;
  String name;
  String avatar;
  String commentNumber;
  String loveNumber;

  CardAudioName({this.press, this.name,this.avatar, this.commentNumber, this.loveNumber});

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
                  MyCircularImage(60,60,linkImg: avatar,),
                  SizedBox(width: 4,),
                  Flexible(child:
                  Text(name,style: TextStyle(color: Colors.black,fontSize: 16 ),),),

                  Expanded(
                    flex: 2,
                    child: Container(

                      child: Align(
                        alignment: Alignment.topRight,
                        child: ClipOval(

                            child: Container(color: Colors.blue.withOpacity(0.4)

                                ,child: Padding(
                                  padding: const EdgeInsets.all(2.0),
                                  child: Icon(Icons.play_arrow,size: 30,color: Colors.blue),
                                ))),
                      ),
                    ),
                  ),

                ],
              ),

              SizedBox(height: 10,),

            /*  Row(
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
              ),*/
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
