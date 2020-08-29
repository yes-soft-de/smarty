import 'package:smarty/courses/model/section/secction.dart';
import 'package:smarty/utils/decode_html/decode_html.dart';

class CourseDetails {
  var name;
  var price;
  var description;
  List<Section> sections;

  CourseDetails({this.price, this.name, this.description, this.sections}) {
    description = DecodeHtml.decode(description);
  }
}
