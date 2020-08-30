import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/audio_player/service/audio_payer_service.dart';
import 'package:smarty/shared/ui/widget/app_drawer/app_drawer.dart';
import 'package:smarty/shared/ui/widget/article_card/article_card.dart';
import 'package:smarty/shared/ui/widget/course_card/course_card.dart';
import 'package:smarty/shared/ui/widget/event_card/event_card.dart';
import 'package:smarty/shared/ui/widget/image_icon/image_icon.dart';
import 'package:smarty/shared/ui/widget/offer_card/offer_card.dart';
import 'package:smarty/shared/ui/widget/smart_app_bar/smarty_app_bar.dart';
import 'package:smarty/shared/ui/widget/video_card/video_card.dart';

@provide
class HomePage extends StatefulWidget {
  final AppDrawerWidget _appDrawerWidget;
  final AudioPlayerService audioPlayerService;

  HomePage(this._appDrawerWidget, this.audioPlayerService);

  @override
  _HomePageState createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: SmartyAppBarWidget(
        appBar: AppBar(),
        title: 'Home',
      ),
      drawer: widget._appDrawerWidget,
      body: Container(
        color: Color(0xffF4ECEC),
        child: ListView(
          padding: EdgeInsetsDirectional.fromSTEB(15, 10, 15, 10),
          children: <Widget>[
            Card(
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(0),
              ),
              color: Color(0xff5E239D),
              child: Container(
                padding: EdgeInsetsDirectional.fromSTEB(15, 15, 15, 15),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: <Widget>[
                    Row(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: <Widget>[
                        Text(
                          'For 50\$',
                          style: TextStyle(
                            color: Colors.white,
                          ),
                        )
                      ],
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: <Widget>[
                        Container(
                          height: 40,
                          width: 40,
                          decoration: BoxDecoration(
                              color: Colors.white30,
                              borderRadius: new BorderRadius.only(
                                  topLeft: const Radius.circular(10.0),
                                  topRight: const Radius.circular(10.0),
                                  bottomLeft: const Radius.circular(10.0),
                                  bottomRight: const Radius.circular(10.0))),
                          child: Icon(
                            Icons.star_border,
                            color: Colors.white,
                            size: 30,
                          ),
                        ),
                        SizedBox(
                          width: 20.0,
                        ),
                        Text(
                          'Live Video',
                          style: TextStyle(
                            fontSize: 25.0,
                            color: Colors.white,
                          ),
                        )
                      ],
                    ),
                    Row(
                      children: <Widget>[
                        Column(
                          mainAxisAlignment: MainAxisAlignment.start,
                          children: <Widget>[
                            Text(
                              'About the new program',
                              style: TextStyle(
                                fontSize: 10.0,
                                color: Colors.white,
                              ),
                            ),
                            Text(
                              'Today',
                              style: TextStyle(
                                fontSize: 25.0,
                                color: Colors.white,
                              ),
                            ),
                            Text(
                              'at 9.00pm',
                              style: TextStyle(
                                fontSize: 25.0,
                                color: Colors.white,
                              ),
                            ),
                            Text(
                              'with Mr Firas',
                              style: TextStyle(
                                fontSize: 15.0,
                                color: Colors.white,
                              ),
                            ),
                          ],
                        ),
                        Column(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: <Widget>[
                            IconButton(
                              icon: ImageAsIconWidget(
                                img: 'assets/Ok.png',
                                height: 500.0,
                                width: 500.0,
                              ),
                              onPressed: () {},
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                Text(
                  'Let\'s get started with 3 minutes meditation',
                  style: TextStyle(
                    fontSize: 8.0,
                  ),
                ),
                IconButton(
                  onPressed: () {},
                  icon: Icon(Icons.arrow_forward_ios),
                )
              ],
            ),
            VideoCardWidget(
              playerService: widget.audioPlayerService,
              track:
                  'http://www.freemindfulness.org/FreeMindfulness3MinuteBreathing.mp3',
              color: Color(0xff3dd598),
              backgroundColor: Color(0xff286053),
              text: 'Mindfulness',
              image: 'assets/Rectangle 2.png',
              isPaid: false,
            ),
            VideoCardWidget(
              playerService: widget.audioPlayerService,
              track:
                  'http://www.freemindfulness.org/FreeMindfulness3MinuteBreathing.mp3',
              color: Color(0xff9a4614),
              backgroundColor: Color(0xff0a0219),
              text: 'Mindfulness',
              image: 'assets/Rectangle 1.png',
              isPaid: true,
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                Text(
                  'Cources recomended for you',
                  style: TextStyle(
                    fontSize: 8.0,
                  ),
                ),
                IconButton(
                  onPressed: () {},
                  icon: Icon(Icons.arrow_forward_ios),
                )
              ],
            ),
            CourseCardWidget(
              image:
                  'https://wow-ae.com/wp-content/uploads/2014/02/img5-460x276.jpg',
              price: '50',
              chapters: '42',
              name: 'Weekly progress',
              description: 'Weekly progress on dieting',
            ),
            Text(
              'Latest opened',
              style: TextStyle(
                fontSize: 10.0,
              ),
            ),
            Row(
              children: <Widget>[
                ArticleCardWidget(
                  icon: Icon(
                    Icons.settings,
                    color: Colors.white,
                  ),
                  name: 'Introduce',
                  duration: 20,
                  color: Colors.greenAccent,
                ),
                ArticleCardWidget(
                  icon: Icon(
                    Icons.settings,
                    color: Colors.white,
                  ),
                  name: 'Introduce',
                  duration: 20,
                  color: Colors.pink,
                ),
              ],
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: <Widget>[
                IconButton(
                  onPressed: () {},
                  icon: Icon(Icons.arrow_forward_ios),
                )
              ],
            ),
            EventCardWidget(
              image: 'assets/Rectangle7.png',
              color: Color(0xff0A0219),
              title: 'Boked for 8 PM',
              description: 'asdf ',
            ),
            OfferCardWidget(
              color: Color(0xff5F06A6),
              description: 'lorem opsem',
            ),
          ],
        ),
      ),
    );
  }
}
