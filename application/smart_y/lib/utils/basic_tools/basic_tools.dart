import 'package:flutter/material.dart';
import 'package:inject/inject.dart';

@provide
@singleton
class BasicTools{

  void openPage(context, page) async {
    var route = new MaterialPageRoute(builder: (BuildContext context) {
      return page;
    });
    Navigator.of(context).push(route);
  }

}