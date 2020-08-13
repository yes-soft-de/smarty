import 'package:flutter/material.dart';

class LoadingIndicatorWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body:Container(
        width: MediaQuery.of(context).size.width,
        color: Color(0xff5E239D),
        child:Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            Text(
              'Loading',
            style: TextStyle(
              color: Colors.white,
            ),
            )
          ],
        ) ,
      ) ,
    );
  }

//  (
//    child:
//  );
}
