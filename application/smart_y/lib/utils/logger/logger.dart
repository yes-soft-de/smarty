import 'package:fluttertoast/fluttertoast.dart';
import 'package:inject/inject.dart';

@provide
@singleton
class Logger {
  info(String tag, String msg) {
    // TODO: Check Log Level, and Display Accordingly
    String time = DateTime.now().toString();
    print("$time: \t $tag \t $msg");
  }

  warn(String tag, String msg) {
    String time = DateTime.now().toString();
    print("$time: \t $tag \t $msg");

    // TODO: Maybe Send a Log to Google Analytics?
  }

  error(String tag, String msg) {
    String time = DateTime.now().toString();
    print("$time: \t $tag \t $msg");
  }
}