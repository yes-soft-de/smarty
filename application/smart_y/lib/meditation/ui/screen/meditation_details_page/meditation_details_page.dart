import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/home/model/meditation/meditation_suggestions.dart';
import 'package:smarty/meditation/bloc/meditation_details_page/meditation_details_page.bloc.dart';
import 'package:smarty/meditation/model/meditation_details.dart';
import 'package:smarty/shared/ui/widget/app_drawer/app_drawer.dart';
import 'package:smarty/shared/ui/widget/loading_indicator/loading_indicator.dart';
import 'package:smarty/shared/ui/widget/smart_app_bar/smarty_app_bar.dart';
import 'package:smarty/shared/ui/widget/video_card/video_card.dart';
import 'package:smarty/utils/logger/logger.dart';

//fake data
final List<MeditationSuggestions> imgList = [
  MeditationSuggestions(
      title: 'Weelky Progress',
      content: 'erhr frgredg c dfbdfh dhgh  hdgh xge t',
      image: 'assets/course_image.png'),
  MeditationSuggestions(
      title: 'bla bla',
      content: 'shshrehe rher theth gh  h rh',
      image: 'assets/Rectangle 1.png'),
  MeditationSuggestions(
      title: 'go go',
      content: 'zcbvsf gh tt ghg   gfhfg  fghg  ur yt',
      image: 'assets/yoga.jpg'),
];

@provide
class MeditationDetailsPage extends StatefulWidget {
  final String tag = "MeditationDetailsPage";
  final AppDrawerWidget _appDrawerWidget;
  final MeditationDetailsBloc _meditationDetailsBloc;
  final Logger _logger;

  MeditationDetailsPage(
      this._appDrawerWidget, this._meditationDetailsBloc, this._logger);

  @override
  _MeditationDetailsPageState createState() => _MeditationDetailsPageState();
}

class _MeditationDetailsPageState extends State<MeditationDetailsPage> {
  int currentState = MeditationDetailsBloc.STATUS_CODE_INIT;
  int meditationId;
  MeditationDetails _meditationDetails;

  @override
  Widget build(BuildContext context) {
    meditationId = ModalRoute.of(context).settings.arguments;

    widget._meditationDetailsBloc.meditationDetailsStateObservable
        .listen((stateChanged) {
      currentState = stateChanged.first;

      if (currentState ==
          MeditationDetailsBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
        this._meditationDetails = stateChanged.last;
      }

      if (this.mounted) {
        setState(() {});
      }
    });

    if (currentState == MeditationDetailsBloc.STATUS_CODE_INIT) {
      widget._logger.info(widget.tag, "Meditation details Page Started");
      widget._meditationDetailsBloc.getMeditationDetails(meditationId);
    }

    if (currentState == MeditationDetailsBloc.STATUS_CODE_FETCHING_DATA) {
      widget._logger.info(widget.tag, "Fetching data from the server");
      return LoadingIndicatorWidget();
    }

    if (currentState ==
        MeditationDetailsBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
      widget._logger.info(widget.tag, "Fetching data SUCCESS");
      return getPageLayout();
    }

    if (currentState == MeditationDetailsBloc.STATUS_CODE_FETCHING_DATA_ERROR) {
      widget._logger.info(widget.tag, "Fetching data Error");
      return Scaffold(
          body: Center(
        child: Text("Fetching data Error"),
      ));
    }

    // Undefined State
    widget._logger.error(widget.tag, "Undefined State");
    return Scaffold(
      body: Center(
        child: Text("Undefined State?!!"),
      ),
    );
  }

  Widget getPageLayout() {
    return Scaffold(
        appBar: SmartyAppBarWidget(
          appBar: AppBar(),
          title: 'Meditattion',
        ),
        drawer: widget._appDrawerWidget,
        body: Container(
          child: Column(children: <Widget>[
            Container(
                height: MediaQuery.of(context).size.height * 0.30,
                child: CompilcatedImageDemo()),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: <Widget>[
                Text(
                  '${_meditationDetails.name}',
                  style: TextStyle(color: Colors.black87, fontSize: 12),
                ),
                Text(
                  '${_meditationDetails.audiosNumber} Audios',
                  style: TextStyle(color: Colors.black87, fontSize: 12),
                ),
              ],
            ),
            Container(
              padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 10),
              width: MediaQuery.of(context).size.width * 0.8,
              child: Text('${_meditationDetails.description}'),
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
//                VideoCardWidget(
//                  color: Color(0xff3dd598),
//                  backgroundColor: Color(0xff286053),
//                  text: 'Mindfulness',
//                  image: 'assets/Rectangle 2.png',
//                  isPaid: false,
//                ),

            Container(
              height: MediaQuery.of(context).size.height * 0.3,
              child: ListView.builder(
                  itemCount: _meditationDetails.audios.length,
                  padding: EdgeInsetsDirectional.fromSTEB(0, 50, 0, 0),
                  itemBuilder: (BuildContext context, int index) {
                    return VideoCardWidget(
                      color: Color(0xff9a4614),
                      backgroundColor: Color(0xff0a0219),
                      text: '${_meditationDetails.audios[index].name}',
                      image: 'assets/Rectangle 1.png',
                      isPaid: true,
                    );
                  }),
            ),
          ]),
        ));
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
