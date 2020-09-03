

import 'package:flutter/material.dart';

class LogoWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Image(
      width: 200,
      height: 200,
      image: AssetImage('assets/Logo.png'),
    );
  }
}
