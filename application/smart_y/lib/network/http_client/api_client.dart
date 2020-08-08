import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:inject/inject.dart';
import 'package:smarty/utils/logger/logger.dart';

@singleton
@provide
class ApiClient {
  static const String TAG = "HttpClient";

  final Logger _logger;

  ApiClient(this._logger);

  static Dio _dio = Dio();

  Future<List<Map<String, dynamic>>> get(String url,
      [Map<String, String> queryMap, Map<String, String> headers]) async {
    this._logger.info(TAG, 'Requesting GET: ' + url);

    try {
      Response response = await _dio.get<String>(url,
          queryParameters: queryMap, options: Options(headers: headers));

      return _extractResponse(response);
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occurred: $error stackTrace: $stacktrace");
      return null;
    }
  }

  Future<List<Map<String, dynamic>>> post(
      String url, Map<String, dynamic> payLoad,
      [Map<String, String> queryMap, Map<String, String> headers]) async {
    this._logger.info(TAG, 'Requesting POST: ' + url);

    try {
      Response response = await _dio.post<String>(url,
          data: payLoad, options: Options(headers: headers));

      return _extractResponse(response);
    } catch (error, stacktrace) {
      _logger.error(
          TAG, "Post: Exception occurred: $error stackTrace: $stacktrace");
      return null;
    }
  }

  Future<List<Map<String, dynamic>>> put(
      String url, Map<String, dynamic> payLoad,
      [Map<String, String> queryMap, Map<String, String> headers]) async {
    this._logger.info(TAG, 'Requesting PUT: ' + url);

    try {
      Response response = await _dio.put(url,
          data: payLoad, options: Options(headers: headers));

      return _extractResponse(response);
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occurred: $error stackTrace: $stacktrace");

      return null;
    }
  }

  Future<List<Map<String, dynamic>>> delete(String url,
      [Map<String, String> queryMap, Map<String, String> headers]) async {
    this._logger.info(TAG, 'Requesting DELETE: ' + url);

    try {
      Response response =
          await _dio.delete(url, options: Options(headers: headers));

      return _extractResponse(response);
    } catch (error, stacktrace) {
      _logger.error(TAG, "Exception occurred: $error stackTrace: $stacktrace");
      return null;
    }
  }

  List<Map<String, dynamic>> _extractJson(String stringResponse) {
    if (stringResponse == null) {
      return null;
    }

    int possibleIndex1 = stringResponse.indexOf('[');
    int possibleIndex2 = stringResponse.indexOf('{');

    int startIndex = -1;

    if (possibleIndex1 == -1 && possibleIndex2 == -1) {
      return null;
    }

    if (possibleIndex1 == -1 && possibleIndex2 != -1) {
      startIndex = possibleIndex2;
    }

    if (possibleIndex1 != -1 && possibleIndex2 == -1) {
      startIndex = possibleIndex1;
    }

    if (possibleIndex1 != -1 && possibleIndex2 != -1) {
      startIndex =
          possibleIndex1 > possibleIndex2 ? possibleIndex2 : possibleIndex1;
    }

    String response = stringResponse.substring(startIndex);
    this._logger.info(TAG, response);

    return jsonDecode(response) is Map
        ? [jsonDecode(response)]
        : jsonDecode(response);
  }

  List<Map<String, dynamic>> _extractResponse(Response response) {
    _logger.info(TAG, "Got Response Code: " + response.statusCode.toString());
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return _extractJson(response.data.toString());
    } else {
      _logger.error(
          TAG, "Error Status Code: " + response.statusCode.toString());
      return null;
    }
  }
}
