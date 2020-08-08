class ApiUrls {
  static const BaseUrl = "https://wow-ae.com/wp-json/llms/v1/";
  static const HomePageUrl = 'https://wow-ae.com/';

  static const registerApi = HomePageUrl + '?rest_route=/auth/v1/users';
  static const authApi = HomePageUrl + '?rest_route=/auth/v1/auth';
  static const lifterKeysApi = BaseUrl + 'api-keys';
  static const CoursesApi = BaseUrl + "courses";
}
