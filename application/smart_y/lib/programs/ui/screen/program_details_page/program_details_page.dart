import 'package:flutter/material.dart';
import 'package:smarty/programs/ui/screen/program_details_page/articles_tab_page/articles_tab_page.dart';
import 'package:smarty/programs/ui/screen/program_details_page/videos_tab_page/videos_tap_page.dart';

import 'audios_tab_page/audios_tab_page.dart';

class ProgramDetailsPage extends StatefulWidget {
  @override
  _ProgramDetailsPageState createState() => _ProgramDetailsPageState();
}

class _ProgramDetailsPageState extends State<ProgramDetailsPage> {
  @override
  Widget build(BuildContext context) {
    return Container();


  }

  Widget getPageLayout(){
    return DefaultTabController(
      length:3 ,
      child: Scaffold(
            appBar: AppBar(),

          body:Column(
            children: [
              Container(
                height: MediaQuery.of(context).size.height*0.07,
                child: TabBar(
                    indicatorColor: Color(0xff5E239D),
                    tabs:
                    [
//                      Tab(child: Text('About',style:TextStyle(color: Color(0xff5E239D),)),),
                      Tab(child: Text('Videos',style:TextStyle(color:Color(0xff5E239D),)),),
                      Tab(child: Text('Audio',style:TextStyle(color:Color(0xff5E239D),)),),
                      Tab(child: Text('Articles',style:TextStyle(color:Color(0xff5E239D),)),),
                    ]
                ),
              ),
              Container(
                height:MediaQuery.of(context).size.height*0.777 ,
                child: TabBarView(
                  children: [
                    VideosTabPage(),
                    AudiosTabPage(),
                    ArticlesTabPage()
                  ],
                ),
              ),
            ],
          )
      ),
    );
  }
}
