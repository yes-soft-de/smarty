import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:inject/inject.dart';
import 'package:smarty/home/model/program/program_model.dart';
import 'package:smarty/home/ui/widget/app_drawer/app_drawer.dart';
import 'package:smarty/home/ui/widget/smart_app_bar/smarty_app_bar.dart';

//fake data
final List<ProgramModel> programList = [
  ProgramModel(
  content:'It looks like you are on track. Please continue to follow your daily plan.' ,
  name:'Pre wellness' ,
  date: new DateTime(2020,8,15),
  participant:20 ,
  price:120,
  image:'assets/Bitmap2.png'
  ),
  ProgramModel(
      content:'It looks like you are on track. Please continue to follow your daily plan.' ,
      name:'Pre wellness' ,
      date: new DateTime(2020,8,30),
      participant:20 ,
      price:120,
    image: 'assets/Bitmap2.png'
  ),
  ProgramModel(
      content:'It looks like you are on track. Please continue to follow your daily plan.It looks like you are on track. Please continue to follow your daily plan.It looks like you are on track. Please continue to follow your daily plan.It looks like you are on track. Please continue to follow your daily plan.It looks like you are on track. Please continue to follow your daily plan.It looks like you are on track. Please continue to follow your daily plan.It looks like you are on track. Please continue to follow your daily plan.' ,
      name:'Pre wellness' ,
      date: new DateTime(2020,8,20),
      participant:20 ,
      price:120,
      image:'assets/Bitmap2.png'
  ),
];

@provide
class ProgramsPage extends StatefulWidget {
  final AppDrawerWidget _appDrawerWidget;
  ProgramsPage(this._appDrawerWidget);

  @override
  _ProgramsPageState createState() => _ProgramsPageState();
}

class _ProgramsPageState extends State<ProgramsPage> {
  @override
  Widget build(BuildContext context) {
    return getPageLayout();
  }

  Widget getPageLayout(){
    return Scaffold(
      appBar: SmartyAppBarWidget(
        appBar: AppBar(),
        title: 'Programs',
      ),
      drawer:widget._appDrawerWidget,
      body:  Container(
          color: Color(0xffF2F2F2),
          child: Container(
            height: MediaQuery.of(context).size.height ,
            child: ProgramSliderWidget( ),

        ),
      )
    );

  }



}


class ProgramSliderWidget extends StatelessWidget {


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body:  Container(
            child: Column(
              children: <Widget>[
                CarouselSlider(
                  options: CarouselOptions(

                    height: MediaQuery.of(context).size.height*0.80 ,
                    autoPlay: true,
                    aspectRatio: 2.0,
                    enlargeCenterPage: true,
                  ),
                  items: imageSliders,
                ),
              ],
            )),

    );


  }





}


final List<Widget> imageSliders = programList
    .map((item) =>
   ProgramCardWidget(item)
).toList();

class ProgramCardWidget extends StatelessWidget {
  final ProgramModel item;
  ProgramCardWidget(this.item);
  @override
  Widget build(BuildContext context) {
    return   Container(
      child: Container(
        margin: EdgeInsets.all(5.0),

        child: ClipRRect(
            borderRadius: BorderRadius.all(Radius.circular(25.0)),
            child: Stack(
              children: <Widget>[
                Image.asset(item.image, fit: BoxFit.cover, width: 1000.0),
                Positioned(
                  left: 0.0,
                  right: 0.0,
                  child: Container(
                    padding: EdgeInsets.symmetric(
                        vertical: 20.0, horizontal: 20.0),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: <Widget>[
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            Row(
                              children: [
                                Icon(
                                    Icons.person,
                                    color: Colors.white
                                ),
                                Text(
                                    '${item.participant} member',
                                  style: TextStyle(
                                      color: Colors.white
                                  ),
                                )
                              ],
                            ),
                            Text(
                              '\$${item.price}',
                              style: TextStyle(
                                  color: Colors.white
                              ),
                            )
                          ],
                        ),
                        SizedBox(
                          height: 10,
                        ),
                        Text(
                            'It will be on ${item.date.day} of the month',
                            style: TextStyle(
                            color: Colors.white
                        ),
                        ),
                        Container(
                             width: MediaQuery.of(context).size.width*0.9,
                             height:MediaQuery.of(context).size.height*0.4 ,
                          child: SingleChildScrollView(
                            child: Text(
                              item.content,
                              style: TextStyle(
                                  color: Colors.white
                              ),
                            ),
                          ),
                        ),
                        SizedBox(
                          height: 10,
                        ),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.start,
                           children: [
                             Text(
                               item.name,
                               style: TextStyle(
                                   color: Colors.white,
                                   fontSize: 15

                               ),
                             ),
                           ],
                        ),
                        FlatButton(
                          onPressed: (){

                            _showPaymentDialog(context);
                          },

                          color: Color(0xff5F06A6),
                          child:Container(
                            height: MediaQuery.of(context).size.height*0.09,
                            child: Row(
                              children: [
                                Text(
                                  'Yes i am intersted',
                                  style: TextStyle(
                                    fontSize: 10,
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
                        )
                      ],
                    ),
                  ),
                ),
              ],
            )),

      ),
    );


  }

  _showPaymentDialog(BuildContext context) {
    showDialog(
        context: context,
        builder: (_) => new SimpleDialog(

            backgroundColor: Colors.black54,
            children: [
              Container(
                height: MediaQuery.of(context).size.height*0.8,
                width: MediaQuery.of(context).size.width,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                  Row(

                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Image(
                      height: 75,
                      width: 75,
                      image: AssetImage('assets/Rectangle16.png'),
                    ),
                    Text(
                      /* _preferencesHelper.getUserEmail().toString()*/'Test@Test.com',
                      style: TextStyle(
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
                Row(
                  children: [
                    Text(
                        'The Program Coast',
                      style: TextStyle(
                        color: Colors.white,
                      ),
                    ),
                    Text(
                        '120\$',
                      style: TextStyle(
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),

                    Text(
                      'Please inter your card holder name',
                      style: TextStyle(
                        color: Colors.white,
                      ),
                    ),
                    TextField(

                    ),
                    Text(
                      'Card number',
                      style: TextStyle(
                        color: Colors.white,
                      ),
                    ),
                    TextField(
                      decoration: InputDecoration(
                          border: new UnderlineInputBorder(
                              borderSide: new BorderSide(
                                  color: Colors.red
                              )
                          )

                      ),
                    ),
                    FlatButton(
                        onPressed: (){


                        },

                        color: Color(0xff5F06A6),
                        child:Container(
                          height: MediaQuery.of(context).size.height*0.09,
                          width: MediaQuery.of(context).size.width*0.45,
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                'Pay now',
                                style: TextStyle(
                                    fontSize: 10,
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
              )
            ],
        ));
  }
}


