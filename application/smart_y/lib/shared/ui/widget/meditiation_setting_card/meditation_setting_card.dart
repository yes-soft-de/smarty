import 'package:flutter/material.dart';
import 'package:smarty/shared/project_colors/project_colors.dart';
import 'package:smarty/shared/ui/widget/circle_image/circle_iamge.dart';


class CardMeditationSetting extends StatelessWidget {
  String name;
  VoidCallback press;
  double size;
  String image;

  CardMeditationSetting({this.name,this.size,this.press,this.image});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap:press,
      child: Container(
        decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(100),
            color: ProjectColors.Color2

        ),

        child:Row(

          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            SizedBox(width: 10,),
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: MyCircularImage(size,size,linkImg: image,),
            ),

            Flexible(child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Text("$name",maxLines: 2,style: TextStyle(color: Colors.white,fontSize: 8,),
                textDirection: TextDirection.ltr,textAlign: TextAlign.center,),
            )),


          ],
        ),
      ),
    );
  }
}