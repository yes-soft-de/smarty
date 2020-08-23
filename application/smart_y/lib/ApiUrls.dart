class ApiUrls {
  static const BaseUrl = "http://wow-ae.com/wp-json/wplms/v1/";
  static const HomePageUrl = 'https://wow-ae.com/';

  static const registerApi = HomePageUrl + '?rest_route=/auth/v1/users';
  //static const authApi = HomePageUrl + '?rest_route=/auth/v1/auth';
  static const authApi = BaseUrl+ 'user/signin';
  static const lifterKeysApi = BaseUrl + 'api-keys';
  static const CoursesApi = BaseUrl + "course/category/71";
  static const SectionsApi = BaseUrl +"sections";
  static const LessonsApi =BaseUrl + "lessons";
}
