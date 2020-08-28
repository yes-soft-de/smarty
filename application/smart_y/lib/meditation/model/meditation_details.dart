
class MeditationDetails{
  var name;
  var audiosNumber;
  var description;
  List<Audio> audios;

  MeditationDetails({this.name,this.description,this.audios,this.audiosNumber});
}


class Audio{
  var name;

  Audio({this.name});
}