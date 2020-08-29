class DecodeHtml {
  static String decode(String payload) {
    if (payload == null) return ' ';
    // Protection Against infinite loop
    for (int i = 0; i < 100; i++) {
      var start = payload.indexOf('<');
      if (start == -1) {
        break;
      }
      var end = payload.indexOf('>');
      payload = payload.replaceRange(start, end + 1, '');
    }

    return payload;
  }
}
