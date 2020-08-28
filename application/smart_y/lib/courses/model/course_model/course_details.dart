import 'package:smarty/courses/model/section/secction.dart';

class CourseDetails{
  var name;
  var price;
  var description;
  List<Section> sections;

  CourseDetails({this.price,this.name,this.description,this.sections});
}