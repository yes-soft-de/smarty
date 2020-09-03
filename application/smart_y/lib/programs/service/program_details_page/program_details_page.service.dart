import 'package:inject/inject.dart';
import 'package:smarty/courses/model/lesson/lesson.dart';
import 'package:smarty/courses/model/section/secction.dart';
import 'package:smarty/courses/response/course_details_response/course_details_response.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';
import 'package:smarty/programs/manager/program_details/program_details.manager.dart';
import 'package:smarty/programs/model/program_details_model/program_details_model.dart';
import 'package:smarty/utils/decode_html/decode_html.dart';
import 'package:smarty/utils/filter/courrse_sections_filter/course_section_filter.dart';
import 'package:smarty/utils/media_extractor/media_extractor.dart';

@provide
class ProgramDetailsService {
  ProgramDetailsManager _programDetailsManager;

  ProgramDetailsService(this._programDetailsManager);

  Future<ProgramDetailsModel> getProgramDetails(int programId) async {
    CourseDetailsResponse courseDetails =
    await _programDetailsManager.getProgramDetails(programId);

    if (courseDetails == null) {
      return null;
    }
    List<Section>  programSections =  CourseSectionsFilter.getSections(courseDetails.curriculum);

    List<About> about;
    List<Video> videos;
    List<Audio> audios;
    List<Article> articles;



    for(int i=0; i<programSections.length; i++){
      if(programSections[i].title == 'About') about=await getAboutProgram(programSections[i].lessons);
      if(programSections[i].title == 'Video') videos=await getProgramVideos(programSections[i].lessons);
      if(programSections[i].title == 'Audio') audios=await getProgramAudios(programSections[i].lessons);
      if(programSections[i].title == 'Article') articles=await getProgramArticles(programSections[i].lessons);

    }


  return new ProgramDetailsModel(
    audios: audios,
    videos: videos,
    articles: articles,
    about: about
  );


  }
  Future<List<About>> getAboutProgram(List<Lesson> abouts) async{
    List<About> result = new List();

    for(int i=0; i<abouts.length; i++){
      CourseDetailsResponse videoDetails =
      await _programDetailsManager.getProgramDetails(abouts[i].id);
      About about = new About(
          content:DecodeHtml.decode( videoDetails.description)
      );

      result.add(about);
    }
    return result;
  }

  Future<List<Video>> getProgramVideos(List<Lesson> videos) async{
    List<Video> result = new List();

    for(int i=0; i<videos.length; i++){
      CourseDetailsResponse videoDetails =
      await _programDetailsManager.getProgramDetails(videos[i].id);
      Video video = new Video(
          name: videoDetails.course.name,
          videoUrl:MediaExtractor.extractMedia(videoDetails.description),
          instructorAvatar: videoDetails.course.instructor.avatar,
          instructorName:   videoDetails.course.instructor.name
      );

      result.add(video);
    }
      return result;
  }

  Future<List<Audio>> getProgramAudios(List<Lesson> audios) async{
    List<Audio> result = new List();

    for(int i=0; i<audios.length; i++){
      CourseDetailsResponse audioDetails =
      await _programDetailsManager.getProgramDetails(audios[i].id);
      Audio audio = new Audio(
            audioUrl: MediaExtractor.extractMedia(audioDetails.description),
            instructorName: audioDetails.course.instructor.name,
            instructorAvatar: audioDetails.course.instructor.avatar,
      );

      result.add(audio);
    }
    return result;
  }

  Future<List<Article>> getProgramArticles(List<Lesson> articles) async{
    List<Article> result = new List();

    for(int i=0; i<articles.length; i++){
      CourseDetailsResponse articleDetails =
      await _programDetailsManager.getProgramDetails(articles[i].id);
      Article article = new Article(
         content: DecodeHtml.decode(articleDetails.description),
        instructorName: articleDetails.course.instructor.name,
        instructorAvatar: articleDetails.course.instructor.avatar,
      );
      result.add(article);
    }


    return result;
  }
}
