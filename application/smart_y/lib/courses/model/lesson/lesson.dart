import 'package:smarty/utils/decode_html/decode_html.dart';

class Lesson {
  var id;
  String title;
  String content;
  int duration;
  Lesson({this.id, this.title, this.duration, this.content}) {
    content = DecodeHtml.decode(content);
  }
}
