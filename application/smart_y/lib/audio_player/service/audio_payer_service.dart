import 'package:inject/inject.dart';
import 'package:just_audio/just_audio.dart';
import 'package:rxdart/rxdart.dart';

@provide
class AudioPlayerService {
  final AudioPlayer _player = AudioPlayer();

  final PublishSubject _playerEventSubject = PublishSubject();
  Stream<String> get playerEventStream => _playerEventSubject.stream;

  String currentTrack;

  void play(String track) {
    if (track == null) {
      return;
    }
    currentTrack = track;
    _playerEventSubject.add(track);
    if (_player.playing) {
      _player.stop();
    }

    String editedTrackUrl = track.replaceAll('https', 'http');
    _player.load(AudioSource.uri(Uri.parse(editedTrackUrl)));
    _player.play();
  }

  void stop() {
    if (_player.playing) {
      _playerEventSubject.add(null);
      _player.stop();
    }
  }

  void pause() {
    if (_player.playing) {
      _player.pause();
    }
  }

  bool isPlaying(String track) {
    return currentTrack == track && _player.playing;
  }

  void dispose() {
    _playerEventSubject.close();
  }
}
