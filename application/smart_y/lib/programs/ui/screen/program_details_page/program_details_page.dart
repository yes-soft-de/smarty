import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/programs/bloc/program_details_page/program_details_page.bloc.dart';
import 'package:smarty/programs/model/program_details_model/program_details_model.dart';
import 'package:smarty/programs/ui/screen/program_details_page/articles_tab_page/articles_tab_page.dart';
import 'package:smarty/programs/ui/screen/program_details_page/videos_tab_page/videos_tap_page.dart';
import 'package:smarty/shared/ui/widget/loading_indicator/loading_indicator.dart';
import 'package:smarty/utils/logger/logger.dart';

import 'audios_tab_page/audios_tab_page.dart';

@provide
class ProgramDetailsPage extends StatefulWidget {
  final String tag = "ProgramDetailsPage";

  final ProgramDetailsPageBloc _programDetailsPageBloc;
  final Logger _logger;

  ProgramDetailsPage(this._programDetailsPageBloc,this._logger);


  @override
  _ProgramDetailsPageState createState() => _ProgramDetailsPageState();
}

class _ProgramDetailsPageState extends State<ProgramDetailsPage> {
  int currentState = ProgramDetailsPageBloc.STATUS_CODE_INIT;

  int _programId;
  ProgramDetailsModel _programDetailsModel;

  @override
  Widget build(BuildContext context) {
    _programId = ModalRoute.of(context).settings.arguments;

    widget._programDetailsPageBloc.programdetailsStateObservable.listen((state) {
        currentState = state.first;

        if(currentState == ProgramDetailsPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS){
          _programDetailsModel = state.last;
        }
        if(this.mounted){
          setState(() {

          });
        }

    });

    if(currentState == ProgramDetailsPageBloc.STATUS_CODE_INIT){
      widget._logger.info(widget.tag, "Program details Page Started");
      widget._programDetailsPageBloc.getProgramDetails(_programId);
    }

    if (currentState == ProgramDetailsPageBloc.STATUS_CODE_FETCHING_DATA) {
      widget._logger.info(widget.tag, "Fetching data from the server");
      return LoadingIndicatorWidget();
    }

    if (currentState == ProgramDetailsPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
      widget._logger.info(widget.tag, "Fetching data SUCCESS");
      return getPageLayout();
    }

    if (currentState == ProgramDetailsPageBloc.STATUS_CODE_FETCHING_DATA_ERROR) {
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

  Widget getPageLayout(){
    return DefaultTabController(
      length:3 ,
      child: Scaffold(
            appBar: AppBar(
              title: Text(
                  'Prewelness',
                style: TextStyle(
                  color: Colors.white
                ),
              ),
              backgroundColor:Color(0xff5E239D) ,
            ),

          body:Column(
            children: [
              Container(
                height: MediaQuery.of(context).size.height*0.07,
                child: TabBar(
                    indicatorColor: Color(0xff5E239D),
                    tabs:
                    [
//                      Tab(child: Text('About',style:TextStyle(color: Color(0xff5E239D),)),),
                      Tab(
                        child: Row(
                          children: [
                            Icon(Icons.videocam),
                            Text(
                                'Videos',
                                style:TextStyle(
                                    color:Color(0xff5E239D),
                                    fontSize: 10
                            )
                            ),
                          ],
                        )
                      ),
                      Tab(child: Row(
                        children: [
                          Icon(Icons.mic),
                          Text(
                              'Audio',
                              style:TextStyle(
                                color:Color(0xff5E239D),
                                  fontSize: 10
                              )),
                        ],
                      )
                      ),
                      Tab(child: Row(
                        children: [
                          Icon(Icons.library_books),
                          Text(
                              'Articles',
                              style:TextStyle(
                                color:Color(0xff5E239D),
                                  fontSize: 10
                              )
                          ),
                        ],

                      )
                      ),
                    ]
                ),
              ),
              Container(
                height:MediaQuery.of(context).size.height*0.777 ,
                child: TabBarView(
                  children: [
                    VideosTabPage(
                      _programDetailsModel.videos
                    ),
                    AudiosTabPage(
                      _programDetailsModel.audios
                    ),
                    ArticlesTabPage(
                      _programDetailsModel.articles
                    )
                  ],
                ),
              ),
            ],
          )
      ),
    );
  }
}
