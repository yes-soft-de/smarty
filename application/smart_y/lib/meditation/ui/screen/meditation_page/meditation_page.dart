import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/courses/bloc/courses_page/courses_page.bloc.dart';
import 'package:smarty/courses/model/course/course_list_item.model.dart';
import 'package:smarty/meditation/Meditation_module.dart';
import 'package:smarty/meditation/bloc/meditation_page/meditation_page.bloc.dart';
import 'package:smarty/persistence/shared_preferences/shared_preferences_helper.dart';
import 'package:smarty/shared/project_colors/project_colors.dart';
import 'package:smarty/shared/ui/widget/circle_image/circle_iamge.dart';
import 'package:smarty/shared/ui/widget/loading_indicator/loading_indicator.dart';
import 'package:smarty/utils/logger/logger.dart';


@provide
class MeditationPage extends StatefulWidget {
  final String tag = "MeditationPage";

  final MeditationPageBloc _meditationPageBloc;
  final Logger _logger;
  final SharedPreferencesHelper _preferencesHelper;

  MeditationPage(this._meditationPageBloc, this._logger ,this._preferencesHelper);

  @override
  _MeditationPageState createState() => _MeditationPageState();
}

class _MeditationPageState extends State<MeditationPage> {
  int currentState = CoursesPageBloc.STATUS_CODE_INIT;
  List<CourseModel> meditations;
  int selectedTabId = -1;
  CourseModel selectedMeditation  = new CourseModel();

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
  Widget getPageLayout(){
    return  Scaffold(
      backgroundColor: ProjectColors.Color3,

      appBar: AppBar(
        backgroundColor: ProjectColors.Color3,
      ),
      body: SingleChildScrollView(
        child: Container(
          padding: EdgeInsetsDirectional.fromSTEB(15, 10, 15, 10),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: <Widget>[
              Row(
                children: <Widget>[
                  Expanded(
                    child: Container(

                      child: Row(

                        mainAxisAlignment: MainAxisAlignment.start,
                        children: [
                          Image(
                            height: 100,
                            width: 100,
                            image: AssetImage('assets/Rectangle16.png'),
                          ),
                          Text(
                              '${ widget._preferencesHelper.getUserEmail()}',
                            style: TextStyle(
                              color: Colors.blue,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              SizedBox(
                height: 30,
              ),
               Flexible(
                 
                  child: GridView.builder(itemBuilder: (BuildContext context, int index){

                    return

                      GestureDetector(
                        onTap:(){
                          setState(() {
                            selectedTabId = meditations[index].id;
                            selectedMeditation = meditations[index];
                          });

                        },
                        child: Container(
                          decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(100),
                              color:(selectedTabId == meditations[index].id)?
                              ProjectColors.color9:
                              ProjectColors.color8,

                          ),

                          child:Column(
                            children: <Widget>[
                              SizedBox(height: 8,),
                              MyCircularImage(MediaQuery.of(context).size.width/5,MediaQuery.of(context).size.width/5,
                                linkImg: meditations[index].image,),
                              SizedBox(height: 8,),
                              Row(
                                children: <Widget>[Expanded(child: Padding(
                                    padding: EdgeInsets.all(4),
                                    child: Text(
                                      meditations[index].title.toString(),
                                      maxLines: 2,
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontSize: 14,
                                      ),
                                      textDirection: TextDirection.ltr,
                                      textAlign: TextAlign.center,
                                    )
                                )
                                )
                                ],
                              ),


                            ],
                          ),
                        ),
                      );



                  },
                    padding: EdgeInsets.symmetric(horizontal: 10),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 3,
                        mainAxisSpacing: 10,
                        crossAxisSpacing: 30,
                        childAspectRatio: (2.3/4)
                    ),
                    itemCount:meditations.length,
                    physics: NeverScrollableScrollPhysics(),
                    shrinkWrap: true,),
                ),



              Container(
                padding: EdgeInsetsDirectional.fromSTEB(0, 30, 0, 0),
                child: FlatButton(
                    onPressed:
                  (selectedTabId==-1)
                      ? null
                      :  ()=>  Navigator.pushNamed(context, MeditationModule.ROUTE_MEDITATION_SETTING,arguments:selectedMeditation)
                    ,


                 color: Color(0xff5F06A6),
                    child:Container(
                      width: MediaQuery.of(context).size.width*0.6,
                      height: MediaQuery.of(context).size.height*0.09,
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            'Next',

                            style: TextStyle(
                                fontSize: 10,
                                color: Colors.white
                            ),
                          ),
                          Icon(
                            Icons.arrow_forward,
                            color: Colors.white,
                          ),
                        ],
                      ),
                    )
                ),
              )



            ],
          ),
        ),
      ),
    );
  }

}
