import 'package:flutter/material.dart';
import 'package:smarty/programs/model/program_details_model/program_details_model.dart';
import 'package:smarty/shared/ui/widget/article_card/article_card.dart';
import 'package:smarty/shared/ui/widget/events_tab_card/events_tab_card.dart';

class ArticlesTabPage extends StatefulWidget {
  final List<Article> articles;

  ArticlesTabPage(this.articles) {
    articles.insert(0, new Article(content: ' '));
  }

  @override
  _ArticlesTabPageState createState() => _ArticlesTabPageState();
}

class _ArticlesTabPageState extends State<ArticlesTabPage> {
  @override
  Widget build(BuildContext context) {
    return getPageLayout();
  }

  Widget getPageLayout() {
    return Scaffold(
      body: Container(
        padding: EdgeInsetsDirectional.fromSTEB(10, 10, 10, 10),
        child: ListView.builder(
            itemCount: widget.articles.length,
            padding: EdgeInsetsDirectional.fromSTEB(0, 50, 0, 0),
            itemBuilder: (BuildContext context, int index) {
              return (index == 0)
                  ? Column(
                      children: [
                        Row(
                          children: <Widget>[
                            Container(
                              padding:
                                  EdgeInsetsDirectional.fromSTEB(0, 0, 10, 0),
                              child: Image(
                                image: AssetImage('assets/profilePic.png'),
                                height: MediaQuery.of(context).size.width * 0.2,
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
                                    ))
                              ],
                            )
                          ],
                        ),

                        Container(
                          padding: const EdgeInsets.all(16.0),
                          width: MediaQuery.of(context).size.width * 0.85,
                          child: Text(
                              " We're intersted in your ideas and would be glade to build something bigger out of it, Share your ideas about features/design an we will bring them on to our full case of this product design",
                              textAlign: TextAlign.center),
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
                                  Icon(
                                    Icons.comment,
                                    color: Colors.grey,
                                  ),
                                  Text(
                                    '7 Comments',
                                    style: TextStyle(
                                      color: Colors.grey,
                                      fontSize: 10,
                                    ),
                                  )
                                ],
                              ),
                              Row(
                                children: <Widget>[
                                  Icon(
                                    Icons.favorite,
                                    color: Colors.grey,
                                  ),
                                  Text(
                                    '42 Likes',
                                    style: TextStyle(
                                      color: Colors.grey,
                                      fontSize: 10,
                                    ),
                                  )
                                ],
                              ),
                            ],
                          ),
                        ),

                        Divider(),

                        //comment field
                        Container(
                          padding:
                              EdgeInsetsDirectional.fromSTEB(20, 20, 20, 20),
                          child: Row(
                            children: <Widget>[
                              Container(
                                padding:
                                    EdgeInsetsDirectional.fromSTEB(0, 0, 10, 0),
                                child: Image(
                                  image: AssetImage('assets/profile_pic.png'),
                                  height:
                                      MediaQuery.of(context).size.width * 0.17,
                                ),
                              ),
                              Container(
                                width: MediaQuery.of(context).size.width * 0.6,
                                height:
                                    MediaQuery.of(context).size.width * 0.16,
                                child: TextField(
                                  textAlign: TextAlign.start,
                                  keyboardType: TextInputType.text,
                                  decoration: InputDecoration(
                                      suffixIcon: Icon(
                                        Icons.attach_file,
                                        color: Colors.grey,
                                      ),
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
                                      hintText: 'Write comment...'),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    )
                  : EventsTabCard(
                      content: widget.articles[index].content,
                      instructor: widget.articles[index].instructorName,
                      avatar: widget.articles[index].instructorAvatar);
            }),
      ),

//      ListView(
//        children: [
//          //previous comments
//          Container(
//            padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 20),
//            child: Row(
//              children: <Widget>[
//                Container(
//                  padding: EdgeInsetsDirectional.fromSTEB(0, 0, 10, 0),
//                  child: Image(
//
//                    image: AssetImage('assets/profilePic.png'
//                    ),
//                    height: MediaQuery.of(context).size.width*0.2,
//                  ),
//                ),
//                Column(
//                  mainAxisAlignment: MainAxisAlignment.start,
//                  children: <Widget>[
//                    Text('Alex Smith'),
//                    Text('20 April at 4:20 PM',
//                        style: TextStyle(
//                          color: Colors.grey,
//                          fontSize: 9,
//                        )
//                    )
//                  ],
//                )
//              ],
//            ),
//          ),
//
//          Container (
//            padding: const EdgeInsets.all(16.0),
//            width: MediaQuery.of(context).size.width*0.85,
//            child: Text (" We're intersted in your ideas and would be glade to build something bigger out of it, Share your ideas about features/design an we will bring them on to our full case of this product design", textAlign: TextAlign.center),
//          ),
//
//          Divider(),
//
//          //comments && likes number
//          Container(
//            padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 10),
//            child: Row(
//              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
//              children: <Widget>[
//
//                Row(
//                  children: <Widget>[
//                    Icon(Icons.comment,color: Colors.grey,),
//                    Text('7 Comments',
//                      style: TextStyle(
//                        color: Colors.grey,
//                        fontSize: 10,
//                      ),)
//                  ],
//                ),
//                Row(
//                  children: <Widget>[
//                    Icon(Icons.favorite,color: Colors.grey,),
//                    Text('42 Likes',
//                      style: TextStyle(
//                        color: Colors.grey,
//                        fontSize: 10,
//                      ),)
//                  ],
//                ),
//              ],
//            ),
//          ),
//          Divider(),
//
//          //comment field
//          Container(
//            padding: EdgeInsetsDirectional.fromSTEB(20, 20, 20, 20),
//            child: Row(
//              children: <Widget>[
//                Container(
//                  padding: EdgeInsetsDirectional.fromSTEB(0, 0, 10, 0),
//                  child: Image(
//
//                    image: AssetImage('assets/profile_pic.png'),
//                    height: MediaQuery.of(context).size.width*0.17,
//                  ),
//                ),
//
//                Container(
//                  width: MediaQuery.of(context).size.width*0.6,
//                  height: MediaQuery.of(context).size.width*0.16,
//                  child: TextField(
//                    textAlign: TextAlign.start,
//                    keyboardType: TextInputType.text,
//                    decoration: InputDecoration(
//                        suffixIcon: Icon(Icons.attach_file,color: Colors.grey,),
//                        hintStyle: TextStyle(fontSize: 10),
//                        border: OutlineInputBorder(
//                          borderRadius: BorderRadius.circular(15),
//                          borderSide: BorderSide(
//                            width: 0,
//                            style: BorderStyle.none,
//                          ),
//                        ),
//                        filled: true,
//
//                        fillColor: Color(0xffebecfd),
//                        hintText: 'Write comment...'
//                    ),
//                  ),
//                ),
//
//
//              ],
//            ),
//          ),
//                    Container(
//                          width: MediaQuery.of(context).size.width*0.7,
//                          child:  EventsTabCard()
//                    ),
//                    Container(
//                        width: MediaQuery.of(context).size.width*0.7,
//                        child:  EventsTabCard()
//                    ),
//        ],
//      ),
    );
  }
}
