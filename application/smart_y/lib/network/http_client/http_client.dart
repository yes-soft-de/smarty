import 'package:dio/dio.dart';
import 'package:inject/inject.dart';
import 'package:smarty/utils/logger/logger.dart';

@singleton
@provide
class HttpClient {
  static const String TAG = "HttpClient";

  Logger _logger;

  HttpClient(this._logger);

  // INFO: In case we wanted to switch for http, this is where we do it
  static Dio _dio = Dio(new BaseOptions(
    baseUrl: "https://www.xx.com/api",
    connectTimeout: 5000,
    receiveTimeout: 3000,
  ));

  Future<Response> get(String url) async {
    try {
      Response response = await _dio.get(url);
      if (response.statusCode >= 200 && response.statusCode < 300) {
        _logger.info(TAG, "Got Response Code: " + response.statusCode.toString() + " and Got Response: " + response.data);
        return response;
      } else {
        _logger.error(TAG, "Error Status Code: " + response.statusCode.toString());
        return null;
      }
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occured: $error stackTrace: $stacktrace");
      return null;
    }
  }

  Future<Response> post(String url, Map<String, dynamic> payLoad) async {
    try {
      Response response = await _dio.post(url, data: payLoad);
      if (response.statusCode >= 200 && response.statusCode < 300) {
        _logger.info(TAG, "Got Response Code: " + response.statusCode.toString() + " and Got Response: " + response.data);
        return response;
      } else {
        _logger.error(TAG, "Error Status Code: " + response.statusCode.toString());
        return null;
      }
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occured: $error stackTrace: $stacktrace");
      return null;
    }
  }

  Future<Response> put(String url, Map<String, dynamic> payLoad) async {
    try {
      Response response = await _dio.get(url);
      if (response.statusCode >= 200 && response.statusCode < 300) {
        _logger.info(TAG, "Got Response Code: " + response.statusCode.toString() + " and Got Response: " + response.data);
        return response;
      } else {
        _logger.error(TAG, "Error Status Code: " + response.statusCode.toString());
        return null;
      }
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occured: $error stackTrace: $stacktrace");
      return null;
    }
  }

  Future<Response> delete(String url) async {
    try {
      Response response = await HttpClient._dio.get(url);
      if (response.statusCode >= 200 && response.statusCode < 300) {
        _logger.info(TAG, "Got Response Code: " + response.statusCode.toString() + " and Got Response: " + response.data);
        return response;
      } else {
        _logger.error(TAG, "Error Status Code: " + response.statusCode.toString());
        return null;
      }
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occured: $error stackTrace: $stacktrace");
      return null;
    }
  }
}
