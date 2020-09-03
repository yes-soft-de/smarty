import 'package:flutter/material.dart';

abstract class Module {
  Map<String, WidgetBuilder> getRoutes();
}