import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/home/ui/screen/news_and_events_page/events_tab/events_tab_page.dart';
import 'package:smarty/home/ui/screen/news_and_events_page/news_tab/news_tab_page.dart';
import 'package:smarty/home/ui/widget/app_drawer/app_drawer.dart';
import 'package:smarty/home/ui/widget/smart_app_bar/smarty_app_bar.dart';

@provide
class NewsAndEventsPAge extends StatefulWidget {
  final AppDrawerWidget _appDrawerWidget;

  NewsAndEventsPAge(this._appDrawerWidget);
  @override
  _NewsAndEventsPAgeState createState() => _NewsAndEventsPAgeState();
}

class _NewsAndEventsPAgeState extends State<NewsAndEventsPAge> {
  @override
  Widget build(BuildContext context) {
    return getPageLayout();
  }

  Widget getPageLayout(){
    return DefaultTabController(
      length:2 ,
      child: Scaffold(
        appBar: SmartyAppBarWidget(
          appBar: AppBar(),
          title: 'Events-News',
        ),
        drawer: widget._appDrawerWidget,
        body:Column(
          children: [
            Container(
              height: MediaQuery.of(context).size.height*0.07,
              child: TabBar(
                indicatorColor: Color(0xff5E239D),
                  tabs:
                  [
                    Tab(child: Text('Events',style:TextStyle(color: Color(0xff5E239D),)),),
                    Tab(child: Text('News',style:TextStyle(color:Color(0xff5E239D),)),),
                  ]
              ),
            ),
            Container(
              height:MediaQuery.of(context).size.height*0.777 ,
              child: TabBarView(
                children: [
                  EventsTabPage(),
                  NewsTabPage(),
                ],
              ),
            ),
          ],
        )
      ),
    );
  }
}
