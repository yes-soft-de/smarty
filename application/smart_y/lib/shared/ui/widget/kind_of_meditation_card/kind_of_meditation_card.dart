import 'package:flutter/material.dart';
import 'package:smarty/shared/project_colors/project_colors.dart';
import 'package:smarty/shared/ui/widget/circle_image/circle_iamge.dart';

class CardKindOfMeditation extends StatelessWidget {
  String name;
  VoidCallback press;
  double size;
  String image;


  CardKindOfMeditation(this.name,this.size,{ this.press,this.image});


  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap:press,
      child: Container(
        decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(100),
            color:ProjectColors.color8

        ),

        child:Column(
          children: <Widget>[
            SizedBox(height: 8,),
            MyCircularImage(size,size,linkImg: image,),
            SizedBox(height: 8,),
            Row(
              children: <Widget>[Expanded(child: Padding(
                  padding: EdgeInsets.all(4),
                  child: Text("$name",maxLines: 2,style: TextStyle(color: Colors.white,fontSize: 14,),
                    textDirection: TextDirection.ltr,textAlign: TextAlign.center,)))],
            ),


          ],
        ),
      ),
    );
  }
}