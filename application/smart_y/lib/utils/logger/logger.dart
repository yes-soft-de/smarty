import 'dart:developer';

import 'package:inject/inject.dart';

@provide
@singleton
class Logger {
  info(String tag, String msg) {
    String time = DateTime.now().toString();
    print("$time: \t Info \t $tag \t $msg");
  }

  warn(String tag, String msg) {
    String time = DateTime.now().toString();
    print("$time: \t Warn \t $tag \t $msg");
  }

  error(String tag, String msg) {
    String time = DateTime.now().toString();
    print("$time: \t Error \t $tag \t $msg");
  }
}