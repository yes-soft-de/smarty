import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/shared/ui/widget/app_drawer/app_drawer.dart';
import 'package:smarty/shared/ui/widget/smart_app_bar/smarty_app_bar.dart';

@provide
class ConsultingPage extends StatefulWidget {
  final AppDrawerWidget _appDrawerWidget;


  ConsultingPage(this._appDrawerWidget);
  @override
  _ConsultingPageState createState() => _ConsultingPageState();
}

class _ConsultingPageState extends State<ConsultingPage> {
  @override
  Widget build(BuildContext context) {
    return getPageLayout();
  }

  Widget getPageLayout(){
    return Scaffold(
      appBar: SmartyAppBarWidget(
        appBar: new AppBar(),
        title: 'Consulting',
      ),
      drawer: widget._appDrawerWidget,
      body: SingleChildScrollView(
        child: Container(
          padding: EdgeInsetsDirectional.fromSTEB(0, 0, 0, 20),
          color: Color(0xffF2F2F3),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 10),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10.0),
                      child: Container(

                        padding: EdgeInsetsDirectional.fromSTEB(20, 10, 20, 10),
                        color: Color(0xff998AE5),
                        child: Text(
                          'Kind 1',
                          style: TextStyle(
                              fontSize: 8
                          ),
                        ),
                      ),
                    ),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10.0),
                      child: Container(

                        padding: EdgeInsetsDirectional.fromSTEB(20, 10, 20, 10),
                        color: Color(0xff998AE5),
                        child: Text(
                          'Kind 1',
                          style: TextStyle(
                              fontSize: 8
                          ),
                        ),
                      ),
                    ),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10.0),
                      child: Container(

                        padding: EdgeInsetsDirectional.fromSTEB(20, 10, 20, 10),
                        color: Color(0xff998AE5),
                        child: Text(
                          'Kind 1',
                          style: TextStyle(
                              fontSize: 8
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: EdgeInsetsDirectional.fromSTEB(0, 0, 0, 10),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10.0),
                      child: Container(

                        padding: EdgeInsetsDirectional.fromSTEB(20, 10, 20, 10),
                        color: Color(0xff998AE5),
                        child: Text(
                          'Kind 1',
                          style: TextStyle(
                              fontSize: 8
                          ),
                        ),
                      ),
                    ),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10.0),
                      child: Container(

                        padding: EdgeInsetsDirectional.fromSTEB(20, 10, 20, 10),
                        color: Color(0xff998AE5),
                        child: Text(
                          'Kind 1',
                          style: TextStyle(
                              fontSize: 8
                          ),
                        ),
                      ),
                    ),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10.0),
                      child: Container(

                        padding: EdgeInsetsDirectional.fromSTEB(20, 10, 20, 10),
                        color: Color(0xff998AE5),
                        child: Text(
                          'Kind 1',
                          style: TextStyle(
                              fontSize: 8
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                width: MediaQuery.of(context).size.width*0.8,
                color: Colors.white,
                child: TextFormField(
                  keyboardType: TextInputType.multiline,
                  maxLines: 8,

                  decoration: InputDecoration.collapsed(hintText: "Write the problem"),
                ),
              ),

              Container(
                padding: EdgeInsetsDirectional.fromSTEB(0, 10, 0, 10),
                height: MediaQuery.of(context).size.height*0.15,
                child: Text(
                  'The Price is 20\$'
                ),
              ),

              FlatButton(
                  onPressed: (){


                  },

                  color: Color(0xff5F06A6),
                  child:Container(
                    height: MediaQuery.of(context).size.height*0.09,
                    width: MediaQuery.of(context).size.width*0.4,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          'Send',

                          style: TextStyle(
                              fontSize: 8,
                              color: Colors.white
                          ),
                        ),
                        Icon(
                          Icons.arrow_forward,
                          color: Colors.white,
                        ),
                      ],
                    ),
                  )
              ),
            ],
          ),
        ),
      ),
    );
  }
}
