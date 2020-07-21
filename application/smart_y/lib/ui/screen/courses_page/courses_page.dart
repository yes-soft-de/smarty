import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:smarty/ui/widget/app_drawer.dart';
import 'package:smarty/ui/widget/course_card.dart';
import 'package:smarty/ui/widget/image_icon.dart';

class CoursesPage extends StatefulWidget {
  @override
  _CoursesPageState createState() => _CoursesPageState();
}

class _CoursesPageState extends State<CoursesPage> {
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
      body: Container(

          color: Color(0xffF4ECEC),
          child: Stack(
                  children: <Widget>[
                    ListView(
                      padding: EdgeInsetsDirectional.fromSTEB(15 , 50, 15, 10),
                      children: <Widget>[

                        CourseCard(
                          image: 'assets/yoga.jpg',
                          price: 50,
                          chapters: 42,
                          name: 'Weekly progress',
                          description: 'Weekly progress on dieting',
                        ),

                        CourseCard(
                          image: 'assets/yoga.jpg',
                          price: 50,
                          chapters: 42,
                          name: 'Weekly progress',
                          description: 'Weekly progress on dieting',
                        ),
                        CourseCard(
                          image: 'assets/yoga.jpg',
                          price: 50,
                          chapters: 42,
                          name: 'Weekly progress',
                          description: 'Weekly progress on dieting',
                        ),

                      ],
                    ),
                    Positioned(
                        left: 0.0,
                        right: 0.0,
                        top: 0.0,
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: <Widget>[
                            Row(
                              children: <Widget>[
                                IconButton(
                                  onPressed: (){},
                                  icon: ImageAsIcon(
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
                                  onPressed: (){},
                                  icon: ImageAsIcon(
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
                  ],
          ),


      ),
    );
  }
}
