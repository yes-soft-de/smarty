class MediaExtractor {
  static String extractMedia(String payload) {
    if (payload == null) {
      return payload;
    }

    int start = payload.indexOf('src="');
    if (start == -1 ) {
      return payload;
    }
    payload = payload.substring(start + 5);

    int end = payload.indexOf('"');
    if (end == -1 ) {
      return payload;
    }

    return payload.substring(0, end);
  }
}