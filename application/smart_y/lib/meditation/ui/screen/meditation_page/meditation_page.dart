import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/courses/bloc/courses_page/courses_page.bloc.dart';
import 'package:smarty/courses/model/course/course_list_item.model.dart';
import 'package:smarty/meditation/Meditation_module.dart';
import 'package:smarty/meditation/bloc/meditation_page/meditation_page.bloc.dart';
import 'package:smarty/shared/ui/widget/app_drawer/app_drawer.dart';
import 'package:smarty/shared/ui/widget/course_card/course_card.dart';
import 'package:smarty/shared/ui/widget/image_icon/image_icon.dart';
import 'package:smarty/shared/ui/widget/loading_indicator/loading_indicator.dart';
import 'package:smarty/shared/ui/widget/smart_app_bar/smarty_app_bar.dart';
import 'package:smarty/utils/logger/logger.dart';


@provide
class MeditationPage extends StatefulWidget {
  final String tag = "MeditationPage";

  final MeditationPageBloc _meditationPageBloc;
  final Logger _logger;
  final AppDrawerWidget _appDrawerWidget;


  MeditationPage(this._meditationPageBloc, this._logger,this._appDrawerWidget);

  @override
  _MeditationPageState createState() => _MeditationPageState();
}

class _MeditationPageState extends State<MeditationPage> {
  int currentState = CoursesPageBloc.STATUS_CODE_INIT;
  List<CourseModel> meditations;

  @override
  Widget build(BuildContext context) {
    widget._meditationPageBloc.meditationStateObservable.listen((stateChanged) {
      currentState = stateChanged.first;

      if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
        this.meditations = stateChanged.last;
      }

      if (this.mounted) {
        setState(() {
          //Your state change code goes here
        });
      }
    });

    if (currentState == CoursesPageBloc.STATUS_CODE_INIT) {
      widget._logger.info(widget.tag, "Meditation List Page Started");
      widget._meditationPageBloc.getMeditation();
    }

    if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA) {
      widget._logger.info(widget.tag, "Fetching data from the server");
      return LoadingIndicatorWidget();
    }

    if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
      widget._logger.info(widget.tag, "Fetching data SUCCESS");
      return getPageLayout();
    }

    if (currentState == CoursesPageBloc.STATUS_CODE_FETCHING_DATA_ERROR) {
      widget._logger.info(widget.tag, "Fetching data Error");
      return Scaffold(
          body: Center(
            child: Flex(
              direction: Axis.vertical,
              children: <Widget>[
                Text("Fetching data Error.."),
                RaisedButton(
                  child: Text('Refresh'),
                  onPressed: () {
                    widget._meditationPageBloc.getMeditation();
                  },
                )
              ],
            ),
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
        title: 'Meditation',
      ),
      drawer: widget._appDrawerWidget,
      body: Container(
        color: Color(0xffF4ECEC),
        child: Stack(
          children: <Widget>[
            ListView.builder(
                itemCount: meditations.length,
                padding: EdgeInsetsDirectional.fromSTEB(0,50 ,0, 0),
                itemBuilder: (BuildContext context, int index) {
                  return FlatButton(
                    onPressed: (){
                      print('${meditations[index].id}');
                      Navigator.pushNamed(context, MeditationModule.ROUTE_MEDITATION_DETAILS,arguments: meditations[index].id);

                    },
                    child: CourseCardWidget(
                      image: meditations[index].image,
                      price: meditations[index].price.toString(),
                      chapters: '42',
                      name: meditations[index].title.toString(),
                      description: '',
                    ),
                  );
                }),
            Positioned(
              left: 0.0,
              right: 0.0,
              top: 0.0,
              child: Container(
                color: Color(0xffF4ECEC),
                padding: EdgeInsetsDirectional.fromSTEB(10, 0, 12, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: <Widget>[
                    Row(
                      children: <Widget>[
                        IconButton(
                          onPressed: () {},
                          icon: ImageAsIconWidget(
                            img: 'assets/filter_icon.png',
                            width: 20,
                            height: 10,
                          ),
                        ),
                        Text('Filter')
                      ],
                    ),
                    Row(
                      children: <Widget>[
                        IconButton(
                          onPressed: () {},
                          icon: ImageAsIconWidget(
                            img: 'assets/filter_icon.png',
                            width: 20,
                            height: 10,
                          ),
                        ),
                        Text('Sort')
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
