import 'package:smarty/utils/decode_html/decode_html.dart';

class ProgramModel {
  var id;
  var participant;
  var price;
  var content;
  var name;
  var image;

  ProgramModel({
    this.id,
    this.participant,
    this.price,
    this.content,
    this.name,
    this.image,
  }) {
    this.content = DecodeHtml.decode(this.content);
  }
}
