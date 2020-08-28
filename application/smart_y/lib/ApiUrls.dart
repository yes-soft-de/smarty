class ApiUrls {
  static const BaseUrl = "http://wow-ae.com/wp-json/wplms/v1/";
  static const HomePageUrl = 'https://wow-ae.com/';

  static const loginApi = HomePageUrl + 'wp-json/wplms/v1/user/signin';
  static const registerApi = HomePageUrl + 'wp-json/wplms/v1/user/register';
  static const lifterKeysApi = BaseUrl + 'api-keys';
  static const CourseApi = BaseUrl + "course";
  static const CoursesApi = BaseUrl + "course/category/71";
  static const ProgramsApi = BaseUrl + "course/category/72";
  static const MeditationsApi = BaseUrl + "course/category/113";
  static const SectionsApi = BaseUrl +"sections";
  static const LessonsApi =BaseUrl + "lessons";
}
