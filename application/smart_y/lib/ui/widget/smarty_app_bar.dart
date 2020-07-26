import 'package:flutter/material.dart';
import 'package:smarty/ui/widget/image_icon.dart';

class SmartyAppBar extends StatelessWidget implements PreferredSizeWidget {
  final AppBar appBar;
  const SmartyAppBar({this.appBar}):super();

  @override
  Size get preferredSize => new Size.fromHeight(appBar.preferredSize.height);

  @override
  Widget build(BuildContext context) {
    return  AppBar(
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

    );
  }
}
