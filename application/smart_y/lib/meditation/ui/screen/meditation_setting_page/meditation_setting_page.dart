import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/courses/model/course/course_list_item.model.dart';
import 'package:smarty/shared/project_colors/project_colors.dart';
import 'package:smarty/shared/ui/widget/meditiation_setting_card/meditation_setting_card.dart';

import '../../../Meditation_module.dart';

@provide
class MeditationSettingPage extends StatefulWidget {
  @override
  _MeditationSettingPageState createState() => _MeditationSettingPageState();
}

class _MeditationSettingPageState extends State<MeditationSettingPage> {
  CourseModel meditation  = new CourseModel();
  int _radioValue = 0;

  @override
  Widget build(BuildContext context) {
    meditation = ModalRoute.of(context).settings.arguments;

    return Scaffold(
      backgroundColor: ProjectColors.Color3,

      appBar: AppBar(
        leading: IconButton(icon: Icon(Icons.arrow_back, color: Colors.white,),onPressed: (){
          Navigator.of(context).pop();
        },),
        backgroundColor: ProjectColors.Color3,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
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
                            SizedBox(
                              width: 10,
                            ),
                            Text(
                              '${"email"}',
                              style: TextStyle(

                              ),
                            ),
                          ],
                        ),



                      ),
                    ),
                  ],
                ),

               Container(
                 height: MediaQuery.of(context).size.height*0.55,
                 child: Column(
                   children: [
                     SizedBox(height: 20,),
                     Row(
                       mainAxisAlignment: MainAxisAlignment.spaceBetween,
                       children: <Widget>[

                         Flexible(child: Padding(
                           padding: const EdgeInsets.symmetric(horizontal: 6),
                           child: CardMeditationSetting(
                             name: meditation.title,
                             image: meditation.image,
                             size: MediaQuery.of(context).size.width/5,),
                         )),

                         Flexible(child: Padding(
                           padding: const EdgeInsets.symmetric(horizontal: 6),
                           child: Text(
                             "10 Audio",
                             style: TextStyle(fontSize: 14),
                           ),
                         ),),

                       ],
                     ),

                     SizedBox(height: 10,),
                     Row(
                       children: <Widget>[
                         Expanded(
                           child: Text("Do you prefere with music or without music?",style: TextStyle(
                             fontSize: 12,
                           ),textAlign: TextAlign.center,
                           ),
                         )
                       ],
                     ),

                     SizedBox(height: 20,),
                     Row(
                       mainAxisAlignment: MainAxisAlignment.center,
                       children: <Widget>[
                         new Radio(
                           value: 0,
                           groupValue: _radioValue,
                           onChanged: _radioValue == 0 ? null : (int value) {
                             setState(() {
                               _radioValue = value;
                             });
                           },
                         ),
                         new Text(
                           'Music on',
                           style: new TextStyle(
                               fontSize: 16.0,
                               color: (_radioValue==0)
                                   ? ProjectColors.color9
                                   : ProjectColors.Color5
                               ,
                           ),
                         ),
                         SizedBox(width: 10,),
                         new Radio(
                           value: 1,
                           groupValue: _radioValue,
                           onChanged: _radioValue == 1 ? null : (int value) {
                             setState(() {
                               _radioValue = value;
                             });
                           },
                         ),
                         new Text(
                           'Music off',
                           style: new TextStyle(
                             fontSize: 16.0,
                             color: (_radioValue==1)
                                 ? ProjectColors.color9
                                 : ProjectColors.Color5
                             ,
                           ),
                         ),

                       ],
                     ),


                   ],
                 ),
               ),

                SizedBox(height: 10,),
                FlatButton(
                    onPressed:  ()=>  Navigator.pushNamed(context, MeditationModule.ROUTE_MEDITATION_DETAILS,arguments:meditation.id)
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
                )

              ],
            ),
          ),
        ),
      ),
    );

  }
}

