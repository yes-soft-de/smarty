import 'package:flutter/material.dart';

class ArticlesTabPage extends StatefulWidget {
  @override
  _ArticlesTabPageState createState() => _ArticlesTabPageState();
}

class _ArticlesTabPageState extends State<ArticlesTabPage> {
  @override
  Widget build(BuildContext context) {
    return Container();
  }

  Widget getPageLayout(){
    return Scaffold(
      body: ListView(
        children: [
          //previous comments
          Container(
            padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 20),
            child: Row(
              children: <Widget>[
                Container(
                  padding: EdgeInsetsDirectional.fromSTEB(0, 0, 10, 0),
                  child: Image(

                    image: AssetImage('assets/profilePic.png'
                    ),
                    height: MediaQuery.of(context).size.width*0.2,
                  ),
                ),
                Column(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: <Widget>[
                    Text('Alex Smith'),
                    Text('20 April at 4:20 PM',
                        style: TextStyle(
                          color: Colors.grey,
                          fontSize: 9,
                        )
                    )
                  ],
                )
              ],
            ),
          ),

          Container (
            padding: const EdgeInsets.all(16.0),
            width: MediaQuery.of(context).size.width*0.85,
            child: Text ("Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2, Long Text 2", textAlign: TextAlign.center),
          ),

          Divider(),

          //comments && likes number
          Container(
            padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 10),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: <Widget>[

                Row(
                  children: <Widget>[
                    Icon(Icons.comment,color: Colors.grey,),
                    Text('7 Comments',
                      style: TextStyle(
                        color: Colors.grey,
                        fontSize: 10,
                      ),)
                  ],
                ),
                Row(
                  children: <Widget>[
                    Icon(Icons.favorite,color: Colors.grey,),
                    Text('42 Likes',
                      style: TextStyle(
                        color: Colors.grey,
                        fontSize: 10,
                      ),)
                  ],
                ),
              ],
            ),
          ),
          Divider(),

          //comment field
          Container(
            padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 20),
            child: Row(
              children: <Widget>[
                Container(
                  padding: EdgeInsetsDirectional.fromSTEB(0, 0, 10, 0),
                  child: Image(

                    image: AssetImage('assets/profile_pic.png'),
                    height: MediaQuery.of(context).size.width*0.17,
                  ),
                ),

                Container(
                  width: MediaQuery.of(context).size.width*0.6,
                  height: MediaQuery.of(context).size.width*0.16,
                  child: TextField(
                    textAlign: TextAlign.start,
                    keyboardType: TextInputType.text,
                    decoration: InputDecoration(
                        suffixIcon: Icon(Icons.attach_file,color: Colors.grey,),
                        hintStyle: TextStyle(fontSize: 10),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(15),
                          borderSide: BorderSide(
                            width: 0,
                            style: BorderStyle.none,
                          ),
                        ),
                        filled: true,

                        fillColor: Color(0xffebecfd),
                        hintText: 'Write comment...'
                    ),
                  ),
                ),


              ],
            ),
          ),

        ],
      ),
    );
  }

}
