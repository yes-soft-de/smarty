class DecodeHtml {
  static String decode(String payload) {
    // Protection Against infinite loop
    for (int i = 0; i < 100; i++) {
      var start = payload.indexOf('<');
      if (start == -1) {
        break;
      }
      var end = payload.indexOf('>');
      payload = payload.replaceRange(start, end, '');
    }

    return payload;
  }
}
