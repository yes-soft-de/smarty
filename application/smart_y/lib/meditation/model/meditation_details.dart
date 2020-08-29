import 'package:smarty/utils/decode_html/decode_html.dart';

class MeditationDetails {
  var name;
  var audiosNumber;
  var description;
  List<Audio> audios;

  MeditationDetails(
      {this.name, this.description, this.audios, this.audiosNumber}) {
    description = DecodeHtml.decode(description);
  }
}

class Audio {
  var name;

  Audio({this.name});
}
