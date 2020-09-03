import 'package:flutter/material.dart';
import '../image_icon/image_icon.dart';

class OfferCardWidget extends StatelessWidget {

  final Color color;
  final String description;
  OfferCardWidget({ @required this.color, @required this.description})
      :assert( color != null &&   description != null   );
  @override
  Widget build(BuildContext context) {
    return Container(

      child: Card(

        color: color,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(0)),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[

                IconButton(
                  onPressed: (){},
                  iconSize: MediaQuery.of(context).size.height*0.20,
                  icon: ImageAsIconWidget(

                    img: 'assets/Play5.png' ,
                    width:  MediaQuery.of(context).size.height*0.09,
                    height: MediaQuery.of(context).size.height*0.09,
                  ),
                ),

            SizedBox(width: 10,),

            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                Row(
                  children: <Widget>[
                    ImageAsIconWidget(
                      img: 'assets/Bullet.png',
                      width: MediaQuery.of(context).size.width*0.05,
                      height: MediaQuery.of(context).size.height*0.05,
                    ),
                    Text('Offer for you',
                      style: TextStyle(
                        color: Colors.blueGrey,
                        fontSize: 10,
                      ),
                    ),
                  ],
                ),

                Text(description,
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 8,
                  ),),
                Text('For free',
                  style: TextStyle(
                    color: Color(0xff3ed598),
                    fontSize: 8,
                  ),),
              ],
            ),
            IconButton(
              icon: ImageAsIconWidget(
                img: 'assets/Group 14.png',
                height: 20,
                width: 20,
              ),
              onPressed: (){},
            ),
          ],
        ),
      ),
    );
  }
}
