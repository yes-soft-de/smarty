import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/shared/ui/widget/app_drawer/app_drawer.dart';
import 'package:smarty/shared/ui/widget/image_icon/image_icon.dart';
import 'package:smarty/shared/ui/widget/notification_row/notification_row.dart';
import 'package:smarty/shared/ui/widget/smart_app_bar/smarty_app_bar.dart';

@provide
class NotificationPage extends StatefulWidget {
  final AppDrawerWidget _appDrawerWidget;


  NotificationPage(this._appDrawerWidget);

  @override
  _NotificationPageState createState() => _NotificationPageState();
}

class _NotificationPageState extends State<NotificationPage> {
  @override
  Widget build(BuildContext context) {
    return getPageLayout();
  }

  getPageLayout(){
    return Scaffold(
      appBar: SmartyAppBarWidget(
        appBar: new AppBar(),
        title: 'Notification',
      ),
      drawer: widget._appDrawerWidget,
      body: Container(


        child: Stack(
          children: <Widget>[
            ListView(
              padding: EdgeInsetsDirectional.fromSTEB(15 , 50, 15, 10),
              children: <Widget>[
                NotificationRow(
                  senderImage: 'assets/profile_pic.png',
                  content:'Great I\'ll have a look' ,
                  sender:'Alice Smith' ,
                  time: new TimeOfDay(hour: 4, minute: 20),
                ),
                NotificationRow(
                  senderImage: 'assets/profilePic.png',
                  content:'Great I\'ll have a look' ,
                  sender:'Alice Smith' ,
                  time: new TimeOfDay(hour: 4, minute: 20),
                ),
                NotificationRow(
                  senderImage: 'assets/profile_pic.png',
                  content:'Great I\'ll have a look' ,
                  sender:'Alice Smith' ,
                  time: new TimeOfDay(hour: 4, minute: 20),
                ),
                NotificationRow(
                  senderImage: 'assets/profilePic.png',
                  content:'Great I\'ll have a look' ,
                  sender:'Alice Smith' ,
                  time: new TimeOfDay(hour: 4, minute: 20),
                ),
                NotificationRow(
                  senderImage: 'assets/profile_pic.png',
                  content:'Great I\'ll have a look' ,
                  sender:'Alice Smith' ,
                  time: new TimeOfDay(hour: 4, minute: 20),
                ),
                NotificationRow(
                  senderImage: 'assets/profilePic.png',
                  content:'Great I\'ll have a look' ,
                  sender:'Alice Smith' ,
                  time: new TimeOfDay(hour: 4, minute: 20),
                ),
                NotificationRow(
                  senderImage: 'assets/profile_pic.png',
                  content:'Great I\'ll have a look' ,
                  sender:'Alice Smith' ,
                  time: new TimeOfDay(hour: 4, minute: 20),
                ),
                NotificationRow(
                  senderImage: 'assets/profilePic.png',
                  content:'Great I\'ll have a look' ,
                  sender:'Alice Smith' ,
                  time: new TimeOfDay(hour: 4, minute: 20),
                ),

              ],
            ),
            Positioned(
              left: 0.0,
              right: 0.0,
              top: 0.0,
              child: Container(
                color: Colors.white,
                padding: EdgeInsetsDirectional.fromSTEB(10, 0, 12, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: <Widget>[
                    Row(
                      children: <Widget>[
                        IconButton(
                          onPressed: (){},
                          icon: ImageAsIconWidget(
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
                          icon: ImageAsIconWidget(
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
            ),
          ],
        ),


      ),
    );
  }
}
