import 'package:flutter/material.dart';
import 'package:smarty/home/ui/widget/image_icon/image_icon.dart';


class SmartyAppBarWidget extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final AppBar appBar;
  const SmartyAppBarWidget({this.appBar,this.title}):super();

  @override
  Size get preferredSize => new Size.fromHeight(appBar.preferredSize.height);

  @override
  Widget build(BuildContext context) {
    return  AppBar(
      backgroundColor: Color(0xff5E239D),
      centerTitle: true,
      title:Text(title,style: TextStyle(fontSize: 10.0),),
      actions: <Widget>[
        IconButton(
          icon: ImageAsIconWidget(
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
            icon: ImageAsIconWidget(
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

    );
  }
}
