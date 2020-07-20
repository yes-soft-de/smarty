import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

class CourseCard extends StatelessWidget {
  final String image;
  final int chapters;
  final int price;
  final String name;
  final String description;
  CourseCard({@required this.image,@required this.chapters,@required this.price,@required this.name,@required this.description})
  :assert(image!=null && chapters!=null && price!=null && name!=null && description!=null);
  @override
  Widget build(BuildContext context) {
    return Container(

      child: Card(
        child: Column(
          children: <Widget>[
            SizedBox(height: MediaQuery.of(context).size.height*0.05,),
            Image(
              width: MediaQuery.of(context).size.width*0.7,

              image: AssetImage(image),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: <Widget>[
                Text(
                    name,
                    style: TextStyle(fontSize: 10.0),
                ),
                Row(

                  children: <Widget>[
                    Icon(Icons.favorite, color: Colors.blueGrey,size: 15.0,),
                    Text('$chapters chapters',
                    style: TextStyle(
                      color: Colors.blueGrey,
                      fontSize: 8.0
                    ),
                    )
                  ],
                )
              ],
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: <Widget>[
                Text(
                  description,
                  style: TextStyle(
                    fontSize: 8.0,
                    color: Colors.blueGrey,
                  ),
                ),
                Text(
                    'For $price \$',
                     style: TextStyle(
                       fontSize: 8.0,
                     ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
