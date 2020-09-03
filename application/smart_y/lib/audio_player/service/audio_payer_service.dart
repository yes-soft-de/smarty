import 'package:audio_manager/audio_manager.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:inject/inject.dart';
import 'package:rxdart/rxdart.dart';

@provide
class AudioPlayerService {
  static PublishSubject<String> _playerEventSubject = PublishSubject<String>();

  static Stream<String> get playerEventStream => _playerEventSubject.stream;

  AudioManager player = AudioManager.instance;

  void play(MediaItem track) async {
    if (track == null) {
      return;
    }
    Fluttertoast.showToast(
        msg: 'Buffering ' + track.name, toastLength: Toast.LENGTH_LONG);
    _playerEventSubject.add(track.url);
    player.stop();
    player = AudioManager.instance;
    await player.start(track.url, track.name,
        desc: "Smart y", cover: 'assets/Logo.png');
    player.playOrPause();
  }

  void stop() {
    player.stop();
    player = AudioManager.instance;
    _playerEventSubject.add('null');
  }

  void pause() {
    player.stop();
    player = AudioManager.instance;
    _playerEventSubject.add('null');
  }

  bool isPlaying(MediaItem track) {
    return player.isPlaying;
  }

  void dispose() {
    _playerEventSubject.close();
  }
}

class MediaItem {
  String url;
  String name;

  MediaItem(this.name, this.url);
}
