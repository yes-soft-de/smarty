import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:smarty/ui/widget/app_drawer.dart';
import 'package:smarty/ui/widget/image_icon.dart';
import 'package:smarty/ui/widget/video_card.dart';


class HomePage extends StatefulWidget {
  @override
  _HomePageState createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Color(0xff5E239D),
        actions: <Widget>[
          IconButton(
            icon: ImageAsIcon(
              img:'assets/profilePic.png',
              height: 32.0,
              width: 32.0,
            ),
            onPressed: () {
              // do something
            },
          )
        ],
        leading: Builder(
          builder: (BuildContext context) {
            return IconButton(
              icon: ImageAsIcon(
                img:'assets/drawer.png',
                height: 20.0,
                width: 30.0,
              ),
              onPressed: () {
                Scaffold.of(context).openDrawer();
              },
              tooltip: MaterialLocalizations.of(context).openAppDrawerTooltip,
            );
          },
        ),

      ),

      drawer: AppDrawer(),
      body: ListView(
        padding: EdgeInsetsDirectional.fromSTEB(15 , 10, 15, 10),
        children: <Widget>[
          Card(
            color: Color(0xff5E239D),
            child:Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: <Widget>[
                    Text(
                      'For 50\$',
                      style: TextStyle(
                        color: Colors.white,
                      ),
                    )
                  ],
                ),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: <Widget>[
                    Container(
                      height: 40,
                      width: 40,
                      decoration: BoxDecoration(
                          color: Colors.white30,
                          borderRadius: new BorderRadius.only(
                              topLeft:  const  Radius.circular(10.0),
                              topRight: const  Radius.circular(10.0),
                              bottomLeft:  const  Radius.circular(10.0),
                              bottomRight: const  Radius.circular(10.0)
                          )
                      ),
                      child: Icon(
                        Icons.star_border,
                        color: Colors.white,
                        size: 30,
                      ),
                    ),
                    SizedBox(width: 20.0,),
                    Text(
                      'Live Video',
                      style: TextStyle(
                        fontSize: 25.0,
                        color: Colors.white,
                      ),
                    )
                  ],
                ),

                Row(
                  children: <Widget>[
                    Column(
                      mainAxisAlignment: MainAxisAlignment.start,
                      children: <Widget>[
                        Text(
                          'About the new program',
                          style: TextStyle(
                            fontSize: 10.0,
                            color: Colors.white,
                          ),
                        ),
                        Text(
                          'Today',
                          style: TextStyle(
                            fontSize: 25.0,
                            color: Colors.white,
                          ),
                        ),
                        Text(
                          'at 9.00pm',
                          style: TextStyle(
                            fontSize: 25.0,
                            color: Colors.white,
                          ),
                        ),
                        Text(
                          'with Mr Firas',
                          style: TextStyle(
                            fontSize: 15.0,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ),

                    Column(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: <Widget>[
                        IconButton(

                          icon: ImageAsIcon(
                            img: 'assets/Ok.png',
                            height: 500.0,
                            width: 500.0,
                          ),
                          onPressed: (){},
                        ),
                      ],
                    ),

                  ],
                ),
              ],
            ),
          ),

          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: <Widget>[
              Text(
                'Let\'s get started with 3 minutes meditation',
                style: TextStyle(
                  fontSize: 8.0,
                ),
              ),
              IconButton(
                onPressed: (){},
                icon: Icon(Icons.arrow_forward_ios),
              )
            ],
          ),

          VideoCard(
            color: Color(0xff3dd598),
            backgroundColor: Color(0xff286053),
            text: 'Mindfulness',
            image: 'assets/Rectangle 2.png',
            isPaid: false,
          ),
          VideoCard(
            color: Color(0xff9a4614),
            backgroundColor: Color(0xff0a0219),
            text: 'Mindfulness',
            image: 'assets/Rectangle 1.png',
            isPaid: true,
          ),




        ],
      ),
    );
  }
}