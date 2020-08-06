import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:country_pickers/country.dart';
import 'package:country_pickers/country_pickers.dart';
import 'file:///D:/YesSoft%20projects/Smart%20Y/the%20flutter%20project/smarty/application/smart_y/lib/ui/widget/logo/logo.dart';

class SignUp3Page extends StatefulWidget {
  @override
  _SignUp3PageState createState() => _SignUp3PageState();
}

class _SignUp3PageState extends State<SignUp3Page> {
  Country _selectedDialogCountry =
  CountryPickerUtils.getCountryByPhoneCode('90');

  Country _selectedFilteredDialogCountry =
  CountryPickerUtils.getCountryByPhoneCode('90');

  Country _selectedCupertinoCountry =
  CountryPickerUtils.getCountryByIsoCode('tr');

  Country _selectedFilteredCupertinoCountry =
  CountryPickerUtils.getCountryByIsoCode('DE');

  @override

  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height,
      padding: EdgeInsetsDirectional.fromSTEB(10.0, 0, 10.0, 0),
      decoration: BoxDecoration(
          gradient: LinearGradient(
              colors: [Color(0xff5E239D),Color(0xffDCDCDE)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              stops: [0.6,1]
          )
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,

        children: <Widget>[
          Logo(),
          Row(
            mainAxisAlignment: MainAxisAlignment.start,
            children: <Widget>[
              Text(
                'Sign Up!',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 25,
                ),
              ),
            ],

          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.start,
            children: <Widget>[
              Text(
                'to start working',
                style: TextStyle(
                  color: Colors.white,

                ),
              ),],
          ),

                ListTile(
                    title: _buildCountryPickerDropdown(sortedByIsoCode: true)
                ),

          Padding(
            padding: const EdgeInsets.all(10.0),
            child: Row(

              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[

                Container(
                  margin: EdgeInsetsDirectional.fromSTEB(0, 0, 7.5, 0),
                  child: FlatButton(
                    padding: EdgeInsetsDirectional.fromSTEB(0, 15, 7.5, 15),

                    onPressed: (){},
                    color:Color(0xffDCDCDE),

                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: <Widget>[

                        Icon(
                          Icons.arrow_back,
                          color: Colors.white,
                        )
                      ],
                    ),

                  ),
                ),
                Container(
                  margin: EdgeInsetsDirectional.fromSTEB(7.5, 0, 0, 0),
                  child: FlatButton(
                    padding: EdgeInsetsDirectional.fromSTEB(7.5, 15, 0, 15),
                    onPressed: (){},
                    color:Color(0xffDCDCDE),

                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: <Widget>[
                        Text(
                          'Next',
                          style: TextStyle(

                            color: Colors.white,
                          ),
                        ),
                        Icon(
                          Icons.arrow_forward,
                          color: Colors.white,
                        )
                      ],
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


  _buildCountryPickerDropdown(
      {bool filtered = false,
        bool sortedByIsoCode = false,
        bool hasPriorityList = false,
        bool hasSelectedItemBuilder = false}) {
    double dropdownButtonWidth = MediaQuery.of(context).size.width * 0.5;
    //respect dropdown button icon size
    double dropdownItemWidth = dropdownButtonWidth - 30;
    double dropdownSelectedItemWidth = dropdownButtonWidth - 30;
    return Row(
      children: <Widget>[
        SizedBox(
          width: dropdownButtonWidth,
          child: CountryPickerDropdown(
            /* underline: Container(
              height: 2,
              color: Colors.red,
            ),*/
            //show'em (the text fields) you're in charge now
            onTap: () => FocusScope.of(context).requestFocus(FocusNode()),
            //if you have menu items of varying size, itemHeight being null respects
            //that, IntrinsicHeight under the hood ;).
            itemHeight: null,
            //itemHeight being null and isDense being true doesn't play along
            //well together. One is trying to limit size and other is saying
            //limit is the sky, therefore conflicts.
            //false is default but still keep that in mind.
            isDense: false,
            //if you want your dropdown button's selected item UI to be different
            //than itemBuilder's(dropdown menu item UI), then provide this selectedItemBuilder.
            selectedItemBuilder: hasSelectedItemBuilder == true
                ? (Country country) => _buildDropdownSelectedItemBuilder(
                country, dropdownSelectedItemWidth)
                : null,
            //initialValue: 'AR',
            itemBuilder: (Country country) => hasSelectedItemBuilder == true
                ? _buildDropdownItemWithLongText(country, dropdownItemWidth)
                : _buildDropdownItem(country, dropdownItemWidth),
            initialValue: 'AR',
            itemFilter: filtered
                ? (c) => ['AR', 'DE', 'GB', 'CN'].contains(c.isoCode)
                : null,
            //priorityList is shown at the beginning of list
            priorityList: hasPriorityList
                ? [
              CountryPickerUtils.getCountryByIsoCode('GB'),
              CountryPickerUtils.getCountryByIsoCode('CN'),
            ]
                : null,
            sortComparator: sortedByIsoCode
                ? (Country a, Country b) => a.isoCode.compareTo(b.isoCode)
                : null,
            onValuePicked: (Country country) {
              print("${country.name}");
            },
          ),
        ),
        SizedBox(
          width: 8.0,
        ),
        Expanded(
          child: TextField(
            decoration: InputDecoration(
              labelText: "Phone",
              isDense: true,
              contentPadding: EdgeInsets.zero,
            ),
            keyboardType: TextInputType.number,
          ),
        )
      ],
    );
  }
  Widget _buildDropdownItem(Country country, double dropdownItemWidth) =>
      SizedBox(
        width: dropdownItemWidth,
        child: Row(
          children: <Widget>[
            CountryPickerUtils.getDefaultFlagImage(country),
            SizedBox(
              width: 8.0,
            ),
            Expanded(child: Text("+${country.phoneCode}(${country.isoCode})")),
          ],
        ),
      );

  Widget _buildDropdownItemWithLongText(
      Country country, double dropdownItemWidth) =>
      SizedBox(
        width: dropdownItemWidth,
        child: Padding(
          padding: const EdgeInsets.all(8.0),
          child: Row(
            children: <Widget>[
              CountryPickerUtils.getDefaultFlagImage(country),
              SizedBox(
                width: 8.0,
              ),
              Expanded(child: Text("${country.name}")),
            ],
          ),
        ),
      );

  Widget _buildDropdownSelectedItemBuilder(
      Country country, double dropdownItemWidth) =>
      SizedBox(
          width: dropdownItemWidth,
          child: Padding(
              padding: const EdgeInsets.all(8),
              child: Row(
                children: <Widget>[
                  CountryPickerUtils.getDefaultFlagImage(country),
                  SizedBox(
                    width: 8.0,
                  ),
                  Expanded(
                      child: Text(
                        '${country.name}',
                        style: TextStyle(
                            color: Colors.red, fontWeight: FontWeight.bold),
                      )),
                ],
              )));

  Widget _buildDialogItem(Country country) => Row(
    children: <Widget>[
      CountryPickerUtils.getDefaultFlagImage(country),
      SizedBox(width: 8.0),
      Text("+${country.phoneCode}"),
      SizedBox(width: 8.0),
      Flexible(child: Text(country.name))
    ],
  );

  void _openCountryPickerDialog() => showDialog(
    context: context,
    builder: (context) => Theme(
      data: Theme.of(context).copyWith(primaryColor: Colors.pink),
      child: CountryPickerDialog(
        titlePadding: EdgeInsets.all(8.0),
        searchCursorColor: Colors.pinkAccent,
        searchInputDecoration: InputDecoration(hintText: 'Search...'),
        isSearchable: true,
        title: Text('Select your phone code'),
        onValuePicked: (Country country) =>
            setState(() => _selectedDialogCountry = country),
        itemBuilder: _buildDialogItem,
        priorityList: [
          CountryPickerUtils.getCountryByIsoCode('TR'),
          CountryPickerUtils.getCountryByIsoCode('US'),
        ],
      ),
    ),
  );

  void _openFilteredCountryPickerDialog() => showDialog(
    context: context,
    builder: (context) => Theme(
        data: Theme.of(context).copyWith(primaryColor: Colors.pink),
        child: CountryPickerDialog(
            titlePadding: EdgeInsets.all(8.0),
            searchCursorColor: Colors.pinkAccent,
            searchInputDecoration: InputDecoration(hintText: 'Search...'),
            isSearchable: true,
            title: Text('Select your phone code'),
            onValuePicked: (Country country) =>
                setState(() => _selectedFilteredDialogCountry = country),
            itemFilter: (c) => ['AR', 'DE', 'GB', 'CN'].contains(c.isoCode),
            itemBuilder: _buildDialogItem)),
  );

  void _openCupertinoCountryPicker() => showCupertinoModalPopup<void>(
      context: context,
      builder: (BuildContext context) {
        return CountryPickerCupertino(
          backgroundColor: Colors.black,
          itemBuilder: _buildCupertinoItem,
          pickerSheetHeight: 300.0,
          pickerItemHeight: 75,
          initialCountry: _selectedCupertinoCountry,
          onValuePicked: (Country country) =>
              setState(() => _selectedCupertinoCountry = country),
          priorityList: [
            CountryPickerUtils.getCountryByIsoCode('TR'),
            CountryPickerUtils.getCountryByIsoCode('US'),
          ],
        );
      });

  void _openFilteredCupertinoCountryPicker() => showCupertinoModalPopup<void>(
      context: context,
      builder: (BuildContext context) {
        return CountryPickerCupertino(
          backgroundColor: Colors.white,
          pickerSheetHeight: 200.0,
          initialCountry: _selectedFilteredCupertinoCountry,
          onValuePicked: (Country country) =>
              setState(() => _selectedFilteredCupertinoCountry = country),
          itemFilter: (c) => ['AR', 'DE', 'GB', 'CN'].contains(c.isoCode),
        );
      });

  Widget _buildCupertinoSelectedItem(Country country) {
    return Row(
      children: <Widget>[
        CountryPickerUtils.getDefaultFlagImage(country),
        SizedBox(width: 8.0),
        Text("+${country.phoneCode}"),
        SizedBox(width: 8.0),
        Flexible(child: Text(country.name))
      ],
    );
  }

  Widget _buildCupertinoItem(Country country) {
    return DefaultTextStyle(
      style: const TextStyle(
        color: CupertinoColors.white,
        fontSize: 16.0,
      ),
      child: Row(
        children: <Widget>[
          SizedBox(width: 8.0),
          CountryPickerUtils.getDefaultFlagImage(country),
          SizedBox(width: 8.0),
          Text("+${country.phoneCode}"),
          SizedBox(width: 8.0),
          Flexible(child: Text(country.name))
        ],
      ),
    );
  }
}
