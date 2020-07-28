import 'package:smarty/model/lesson/lesson.dart';

class Section{
  int id;
  String title;
  List<Lesson> lessons;

  Section({this.id,this.title,this.lessons});
}