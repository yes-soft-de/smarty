import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/home/model/meditation/meditation_suggestions.dart';
import 'package:smarty/home/ui/widget/app_drawer/app_drawer.dart';
import 'package:smarty/home/ui/widget/smart_app_bar/smarty_app_bar.dart';
import 'package:smarty/home/ui/widget/video_card/video_card.dart';

//fake data
final List<MeditationSuggestions> imgList = [
  MeditationSuggestions(
      title: 'Weelky Progress',
      content: 'erhr frgredg c dfbdfh dhgh  hdgh xge t',
      image: 'assets/BG.png'),
  MeditationSuggestions(
      title: 'bla bla',
      content: 'shshrehe rher theth gh  h rh',
      image: 'assets/BG2.jpg'),
  MeditationSuggestions(
      title: 'go go',
      content: 'zcbvsf gh tt ghg   gfhfg  fghg  ur yt',
      image: 'assets/BG3.jpg'),
];

@provide
class MeditationPage extends StatefulWidget {
  @override
  _MeditationPageState createState() => _MeditationPageState();
}

class _MeditationPageState extends State<MeditationPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: SmartyAppBarWidget(
        appBar: AppBar(),
        title: 'Meditattion',
      ),
      drawer: AppDrawerWidget(),
      body: SingleChildScrollView(
        child: Column(
          children: <Widget>[
            Container(
                height: MediaQuery.of(context).size.height * 0.30,
                child: CompilcatedImageDemo()),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: <Widget>[
                Text(
                  'Mindfulness Plan',
                  style: TextStyle(color: Colors.black87, fontSize: 12),
                ),
                Text(
                  '10 Audios',
                  style: TextStyle(color: Colors.black87, fontSize: 12),
                ),
              ],
            ),
            Container(
              padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 10),
              width: MediaQuery.of(context).size.width * 0.8,
              child: Text(
                  'aaa aaaaa aaaa aaaaaaa aaaaaaaaaaaaa aaaaaaaaa aaaaa aaaaa aa'),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: <Widget>[
                Row(
                  children: <Widget>[
                    IconButton(onPressed: () {}, icon: Icon(Icons.settings)),
                    Text('Settings')
                  ],
                ),
                Text(
                  'Edit',
                  style: TextStyle(color: Color(0xff5E239D)),
                ),
              ],
            ),
            VideoCardWidget(
              color: Color(0xff3dd598),
              backgroundColor: Color(0xff286053),
              text: 'Mindfulness',
              image: 'assets/Rectangle 2.png',
              isPaid: false,
            ),
            VideoCardWidget(
              color: Color(0xff9a4614),
              backgroundColor: Color(0xff0a0219),
              text: 'Mindfulness',
              image: 'assets/Rectangle 1.png',
              isPaid: true,
            ),
            VideoCardWidget(
              color: Color(0xff9a4614),
              backgroundColor: Color(0xff0a0219),
              text: 'Mindfulness',
              image: 'assets/Rectangle 1.png',
              isPaid: true,
            ),
            VideoCardWidget(
              color: Color(0xff9a4614),
              backgroundColor: Color(0xff0a0219),
              text: 'Mindfulness',
              image: 'assets/Rectangle 1.png',
              isPaid: true,
            ),
          ],
        ),
      ),
    );
  }
}

class CompilcatedImageDemo extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
          child: Column(
        children: <Widget>[
          CarouselSlider(
            options: CarouselOptions(
              autoPlay: true,
              aspectRatio: 2.0,
              enlargeCenterPage: true,
            ),
            items: imageSliders,
          ),
        ],
      )),
    );
  }
}

final List<Widget> imageSliders = imgList
    .map((item) => Container(
          child: Container(
            margin: EdgeInsets.all(5.0),
            child: ClipRRect(
                borderRadius: BorderRadius.all(Radius.circular(0.0)),
                child: Stack(
                  children: <Widget>[
                    Image.asset(item.image, fit: BoxFit.cover, width: 1000.0),
                    Positioned(
                      left: 0.0,
                      right: 0.0,
                      child: Container(
                        padding: EdgeInsets.symmetric(
                            vertical: 20.0, horizontal: 20.0),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: <Widget>[
                            Icon(
                              Icons.card_giftcard,
                              color: Colors.white,
                            ),
                            Text(
                              item.title,
                              style: TextStyle(color: Colors.white),
                            ),
                            Container(
                              padding:
                                  EdgeInsetsDirectional.fromSTEB(0, 10, 0, 0),
                              width: 500,
                              child: Text(
                                item.content,
                                style: TextStyle(color: Colors.white),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                )),
          ),
        ))
    .toList();
