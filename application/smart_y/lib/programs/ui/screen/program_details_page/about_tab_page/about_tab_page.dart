import 'package:flutter/material.dart';
import 'package:smarty/programs/model/program_details_model/program_details_model.dart';

class AboutTabPage extends StatefulWidget {
  final List<About> about;

  AboutTabPage(this.about);
  @override
  _AboutTabPageState createState() => _AboutTabPageState();
}

class _AboutTabPageState extends State<AboutTabPage> {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsetsDirectional.fromSTEB(20, 10, 20, 10),
      child: ListView.builder(
          itemCount: widget.about.length,
          padding: EdgeInsetsDirectional.fromSTEB(0,50 ,0, 0),
          itemBuilder: (BuildContext context, int index) {
            return   Text(widget.about[index].content);
          }),

    );
  }
}
