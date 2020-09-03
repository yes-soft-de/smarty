import 'package:flutter/material.dart';
import 'package:smarty/shared/ui/widget/events_tab_card/events_tab_card.dart';
import 'package:smarty/shared/ui/widget/events_tab_card2/events_tab_card2.dart';

class EventsTabPage extends StatefulWidget {
  @override
  _EventsTabPageState createState() => _EventsTabPageState();
}

class _EventsTabPageState extends State<EventsTabPage> {
  @override
  Widget build(BuildContext context) {
    return getPageLayout();
  }

  Widget getPageLayout(){
    return Scaffold(
      body:  ListView(

         padding: EdgeInsetsDirectional.fromSTEB(20 , 30, 20, 20),
         children:<Widget> [
             EventsTabCard2(),
             EventsTabCard2() ,
             EventsTabCard2(),

         ],
       ),

    );
  }
}
