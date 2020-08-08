import 'dart:developer';

import 'package:inject/inject.dart';

@provide
@singleton
class Logger {
  info(String tag, String msg) {
    String time = DateTime.now().toString();
    log("$time: \t Info \t $tag \t $msg");
  }

  warn(String tag, String msg) {
    String time = DateTime.now().toString();
    log("$time: \t Warn \t $tag \t $msg");
  }

  error(String tag, String msg) {
    String time = DateTime.now().toString();
    log("$time: \t Error \t $tag \t $msg");
  }
}