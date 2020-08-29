import 'package:inject/inject.dart';
import 'package:just_audio/just_audio.dart';

@provide
class AudioPlayerService {
  final AudioPlayer _player = AudioPlayer();
  String currentTrack;

  void play(String track) {
    if (track == null) {
      return;
    }
    currentTrack = track;
    if (_player.playing) {
      _player.stop();
    }

    _player.load(AudioSource.uri(Uri.parse(track)));
    _player.play();
  }

  void stop() {
    if (_player.playing) {
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
}
