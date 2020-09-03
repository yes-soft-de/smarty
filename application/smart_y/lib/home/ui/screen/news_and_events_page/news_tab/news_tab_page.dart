import 'package:flutter/material.dart';
import 'package:smarty/shared/ui/widget/events_tab_card/events_tab_card.dart';
import 'package:smarty/shared/ui/widget/news_tab_card/news_tab_card.dart';


class NewsTabPage extends StatefulWidget {
  @override
  _NewsTabPageState createState() => _NewsTabPageState();
}

class _NewsTabPageState extends State<NewsTabPage> {
  @override
  Widget build(BuildContext context) {
    return getPageLayout();
  }

  Widget getPageLayout(){
    return Scaffold(
      body:  ListView(

        padding: EdgeInsetsDirectional.fromSTEB(20 , 30, 20, 20),
        children:<Widget> [
          NewsTabCard(),
          NewsTabCard() ,
          NewsTabCard(),

        ],
      ),

    );
  }
}
