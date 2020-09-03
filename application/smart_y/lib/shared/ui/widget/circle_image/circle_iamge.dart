import 'dart:io';

import 'package:flutter/material.dart';

class MyCircularImage extends StatelessWidget {
  final double w;
  final double h;
  final String linkImg;
  final File fileImg;


  MyCircularImage(this.w, this.h,{this.linkImg,this.fileImg});

  @override
  Widget build(BuildContext context) {
    return Container(decoration: BoxDecoration(
        border: Border.all(
            width: 1,
            color: Colors.grey
        ),
        borderRadius: BorderRadius.circular(100)

    ),
        width: w,
        height: h,
        child: ClipOval(child:
        linkImg!=null?
        Image.network(linkImg,fit: BoxFit.cover,):fileImg!=null? Image.file(fileImg):Container(
          color: Colors.blue,
        )
        ));
  }
}