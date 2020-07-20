import 'package:dio/dio.dart';
import 'package:inject/inject.dart';
import 'package:smarty/ApiUrls.dart';
import 'package:smarty/persistence/shared_preferences/shared+preferences_helper.dart';
import 'package:smarty/utils/logger/logger.dart';

@singleton
@provide
class HttpClient {
  static const String TAG = "HttpClient";

  final Logger _logger;
  final SharedPreferencesHelper _preferencesHelper;

  HttpClient(this._logger, this._preferencesHelper);

  // INFO: In case we wanted to switch for http, this is where we do it
  static Dio _dio = Dio(new BaseOptions(
      baseUrl: ApiUrls.BaseUrl, connectTimeout: 5000, receiveTimeout: 3000));

  Future<Response> get(String url) async {
    // Inject Auth Header Here :)
    try {
      Response response = await _dio.get(url,
          options: Options(headers: {
              'X-LLMS-CONSUMER-KEY':'ck_f967cde4edbcb452876f690105ef5bd05f347ef5',
              'X-LLMS-CONSUMER-SECRET':'cs_b30af3735e9366c7336e898810f57bde32392d1e'
          }));
      if (response.statusCode >= 200 && response.statusCode < 300) {
        _logger.info(
            TAG,
            "Got Response Code: " +
                response.statusCode.toString() +
                " and Got Response: " +
                response.data);
        return response;
      } else {
        _logger.error(
            TAG, "Error Status Code: " + response.statusCode.toString());
        return null;
      }
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occured: $error stackTrace: $stacktrace");
      return null;
    }
  }

  Future<Response> post(String url, Map<String, dynamic> payLoad) async {
    try {
      String token = await _preferencesHelper.getToken();

      Response response = await _dio.post(url,
          data: payLoad, options: Options(headers: {"authorization": token}));
      if (response.statusCode >= 200 && response.statusCode < 300) {
        _logger.info(
            TAG,
            "Got Response Code: " +
                response.statusCode.toString() +
                " and Got Response: " +
                response.data);
        return response;
      } else {
        _logger.error(
            TAG, "Error Status Code: " + response.statusCode.toString());
        return null;
      }
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occured: $error stackTrace: $stacktrace");
      return null;
    }
  }

  Future<Response> put(String url, Map<String, dynamic> payLoad) async {
    try {
      String token = await _preferencesHelper.getToken();

      Response response = await _dio.get(url,
          options: Options(headers: {"authorization": token}));
      if (response.statusCode >= 200 && response.statusCode < 300) {
        _logger.info(
            TAG,
            "Got Response Code: " +
                response.statusCode.toString() +
                " and Got Response: " +
                response.data);
        return response;
      } else {
        _logger.error(
            TAG, "Error Status Code: " + response.statusCode.toString());
        return null;
      }
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occured: $error stackTrace: $stacktrace");
      return null;
    }
  }

  Future<Response> delete(String url) async {
    try {
      String token = await _preferencesHelper.getToken();

      Response response = await HttpClient._dio
          .get(url, options: Options(headers: {"authorization": token}));
      if (response.statusCode >= 200 && response.statusCode < 300) {
        _logger.info(
            TAG,
            "Got Response Code: " +
                response.statusCode.toString() +
                " and Got Response: " +
                response.data);
        return response;
      } else {
        _logger.error(
            TAG, "Error Status Code: " + response.statusCode.toString());
        return null;
      }
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occured: $error stackTrace: $stacktrace");
      return null;
    }
  }
}
