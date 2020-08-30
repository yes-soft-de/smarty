
class ProgramDetailsModel{
//  List<About> about;
  List<Video> videos;
  List<Audio> audios;
  List<Article> articles;
  ProgramDetailsModel({this.audios,/*this.about,*/this.articles,this.videos});
}

class About{

}

class Video{
  String name;
  String videoUrl;

  Video({this.name,this.videoUrl});

}

class Audio{
  String instructorName;
  String instructorAvatar;
  String audioUrl;

  Audio({this.audioUrl,this.instructorAvatar,this.instructorName});
}

class Article{
  String content;

  Article({this.content});
}
