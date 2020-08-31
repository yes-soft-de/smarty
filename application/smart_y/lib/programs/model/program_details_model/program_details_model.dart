
class ProgramDetailsModel{
  List<About> about;
  List<Video> videos;
  List<Audio> audios;
  List<Article> articles;
  ProgramDetailsModel({this.audios,this.about,this.articles,this.videos});
}

class About{
   String content;
   About({this.content});
}

class Video{
  String name;
  String videoUrl;
  String instructorAvatar;
  String instructorName;
  Video({this.name,this.videoUrl,this.instructorAvatar,this.instructorName});

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
