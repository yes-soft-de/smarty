import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';
import 'package:smarty/programs/bloc/programs_page/programs_page.bloc.dart';
import 'package:smarty/programs/model/program/program_model.dart';
import 'package:smarty/programs/programs_module.dart';
import 'package:smarty/shared/ui/widget/app_drawer/app_drawer.dart';
import 'package:smarty/shared/ui/widget/loading_indicator/loading_indicator.dart';
import 'package:smarty/shared/ui/widget/smart_app_bar/smarty_app_bar.dart';
import 'package:smarty/utils/logger/logger.dart';

List<Widget> imageSliders = [];

@provide
class ProgramsPage extends StatefulWidget {
  final String tag = "ProgramsPage";

  final ProgramsPageBloc _programsPageBloc;
  final Logger _logger;
  final AppDrawerWidget _appDrawerWidget;
  final SharedPreferencesHelper _prefsHelper;

  ProgramsPage(this._programsPageBloc, this._appDrawerWidget, this._logger,
      this._prefsHelper);

  @override
  _ProgramsPageState createState() => _ProgramsPageState();
}

class _ProgramsPageState extends State<ProgramsPage> {
  int currentState = ProgramsPageBloc.STATUS_CODE_INIT;
  List<ProgramModel> programs;

  int selectedProgramId = -1;

  @override
  Widget build(BuildContext context) {
    widget._programsPageBloc.programsStateObservable.listen((stateChanged) {
      currentState = stateChanged.first;

      if (currentState == ProgramsPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
        this.programs = stateChanged.last;
      }

      if (this.mounted) {
        setState(() {});
      }
    });

    if (currentState == ProgramsPageBloc.STATUS_CODE_INIT) {
      widget._logger.info(widget.tag, "Programs Page Started");

      widget._programsPageBloc.getPrograms();
    }

    if (currentState == ProgramsPageBloc.STATUS_CODE_FETCHING_DATA) {
      widget._logger.info(widget.tag, "Fetching data from the server");
      return LoadingIndicatorWidget();
    }

    if (currentState == ProgramsPageBloc.STATUS_CODE_FETCHING_DATA_SUCCESS) {
      widget._logger.info(widget.tag, "Fetching data SUCCESS");

      imageSliders = programs
          .map((item) => FutureBuilder(
                future: widget._prefsHelper.getUserEmail(),
                builder:
                    (BuildContext context, AsyncSnapshot<String> snapshot) {
                  return ProgramCardWidget(item, snapshot.data);
                },
              ))
          .toList();
      return getPageLayout();
    }

    if (currentState == ProgramsPageBloc.STATUS_CODE_FETCHING_DATA_ERROR) {
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
                widget._programsPageBloc.getPrograms();
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
          title: 'Programs',
        ),
        drawer: widget._appDrawerWidget,
        body: Container(
          color: Color(0xffF2F2F2),
          child: Column(
            children: [Expanded(child: ProgramSliderWidget())],
          ),
        ));
  }
}

class ProgramSliderWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
          child: ListView(
        children: imageSliders,
      )),
    );
  }
}

class ProgramCardWidget extends StatelessWidget {
  final ProgramModel item;
  final String userEmail;

  ProgramCardWidget(this.item, this.userEmail);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(8.0),
      child: Container(
        decoration: BoxDecoration(
            color: Colors.black12,
            borderRadius: BorderRadius.all(Radius.circular(8))),
        child: Padding(
          padding: const EdgeInsets.all(8.0),
          child: Flex(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            direction: Axis.horizontal,
            children: <Widget>[
              Container(
                  width: 48,
                  child: Image.network(item.image, fit: BoxFit.cover)),
              Row(
                children: [
                  Icon(Icons.person, color: Colors.grey),
                  Text(
                    '${(item.participant == false) ? 0 : item.participant} member',
                    style: TextStyle(color: Colors.black),
                  )
                ],
              ),
              Text(
                '\$${(item.price == false) ? 0 : item.price}',
                style: TextStyle(color: Colors.black),
              ),
              Text(
                'It will be on 12 of the month',
                style: TextStyle(color: Colors.black87),
              ),
              Text(
                item.content,
                style: TextStyle(color: Colors.black87),
              ),
              Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: <Widget>[
                  Text(
                    item.name,
                    style: TextStyle(color: Colors.black, fontSize: 15),
                  ),
                  FlatButton(
                      onPressed: () {
                        _showPaymentDialog(context, item.id);
                      },
                      color: Color(0xff5F06A6),
                      child: Container(
                        child: Icon(
                          Icons.arrow_forward,
                          color: Colors.white,
                        ),
                      ))
                ],
              )
            ],
          ),
        ),
      ),
    );
  }

  _showPaymentDialog(BuildContext context, int programId) {
    showDialog(
        context: context,
        builder: (_) => new SimpleDialog(
              backgroundColor: Colors.black54,
              children: [
                Container(
                  height: MediaQuery.of(context).size.height * 0.8,
                  width: MediaQuery.of(context).size.width,
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                            ),
                            child: Padding(
                              padding: const EdgeInsets.all(8.0),
                              child: Icon(
                                Icons.person,
                                color: Colors.white,
                              ),
                            ),
                          ),
                          Text(
                            userEmail,
                            style: TextStyle(
                              color: Colors.white,
                            ),
                          ),
                        ],
                      ),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            'Cost: ',
                            style: TextStyle(
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            item.price.toString() + '\$',
                            style: TextStyle(
                              color: Colors.white,
                            ),
                          ),
                        ],
                      ),
                      FlatButton(
                          onPressed: () {
                            Navigator.pushNamed(
                                context, ProgramsModule.ROUTE_PROGRAM_DETAILS,
                                arguments: programId);
                          },
                          color: Color(0xff5F06A6),
                          child: Container(
                            height: MediaQuery.of(context).size.height * 0.09,
                            width: MediaQuery.of(context).size.width * 0.45,
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text('Pay now',
                                    style: TextStyle(
                                        fontSize: 10, color: Colors.white)),
                                Icon(
                                  Icons.arrow_forward,
                                  color: Colors.white,
                                ),
                              ],
                            ),
                          )),
                    ],
                  ),
                )
              ],
            ));
  }
}
