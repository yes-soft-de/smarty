import 'package:flutter/material.dart';

class TextFieldWithImageIconWidget extends StatelessWidget {
  final String img;
  final String hint;

  TextFieldWithImageIconWidget({@required this.img, this.hint})
      : assert(img != null);

  @override
  Widget build(BuildContext context) {
    return Container(
      child: Theme(
        data: ThemeData(primaryColor: Colors.white, cursorColor: Colors.white),
        child: TextField(
          decoration: InputDecoration(
              hintText: hint,
              icon: Tab(
                icon: Container(
                  child: Image(
                    image: AssetImage(img),
                    fit: BoxFit.cover,
                  ),
                  height: 35.0,
                  width: 25.0,
                ),
              )),
        ),
      ),
    );
  }
}
