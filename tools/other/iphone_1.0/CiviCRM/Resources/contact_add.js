
var win = Ti.UI.currentWindow;

var v1 = Ti.UI.createView({
	top:10,
	left:10,
	width:300,
	height:30
});
var l1 = Ti.UI.createLabel({
	text:'First:',
	top:0,
	left:0
});
var f1 = Ti.UI.createTextField({
	text:'',
	borderStyle:Titanium.UI.INPUT_BORDERSTYLE_ROUNDED,
	top:0,
	right:0,
	width:200
});
v1.add(l1);
v1.add(f1);

var v2 = Ti.UI.createView({
	top:50,
	left:10,
	width:300,
	height:30
});
var l2 = Ti.UI.createLabel({
	text:'Last:',
	top:0,
	left:0
});
var f2 = Ti.UI.createTextField({
	text:'',
	borderStyle:Titanium.UI.INPUT_BORDERSTYLE_ROUNDED,
	top:0,
	right:0,
	width:200
});
v2.add(l2);
v2.add(f2);

var v21 = Ti.UI.createView({
	top:90,
	left:10,
	width:300,
	height:30
});
var l21 = Ti.UI.createLabel({
	text:'Email:',
	top:0,
	left:0
});
var f21 = Ti.UI.createTextField({
	text:'',
	borderStyle:Titanium.UI.INPUT_BORDERSTYLE_ROUNDED,
	top:0,
	right:0,
	width:200
});
v21.add(l21);
v21.add(f21);
/*
var v3 = Ti.UI.createView({
	top:130,
	left:10,
	width:300,
	height:30
});
var l3 = Ti.UI.createLabel({
	text:'Street:',
	top:0,
	left:0
});
var f3 = Ti.UI.createTextField({
	text:'',
	borderStyle:Titanium.UI.INPUT_BORDERSTYLE_ROUNDED,
	top:0,
	right:0,
	width:200
});
v3.add(l3);
v3.add(f3);

var v4 = Ti.UI.createView({
	top:170,
	left:10,
	width:300,
	height:30
});
var l4 = Ti.UI.createLabel({
	text:'City:',
	top:0,
	left:0
});
var f4 = Ti.UI.createTextField({
	text:'',
	borderStyle:Titanium.UI.INPUT_BORDERSTYLE_ROUNDED,
	top:0,
	right:0,
	width:200
});
v4.add(l4);
v4.add(f4);

var v5 = Ti.UI.createView({
	top:210,
	left:10,
	width:300,
	height:30
});
var l5 = Ti.UI.createLabel({
	text:'State:',
	top:0,
	left:0
});
var f5 = Ti.UI.createTextField({
	text:'',
	borderStyle:Titanium.UI.INPUT_BORDERSTYLE_ROUNDED,
	top:0,
	right:0,
	width:200
});
v5.add(l5);
v5.add(f5);

var v6 = Ti.UI.createView({
	top:250,
	left:10,
	width:300,
	height:30
});
var l6 = Ti.UI.createLabel({
	text:'Postal Code:',
	top:0,
	left:0
});
var f6 = Ti.UI.createTextField({
	text:'',
	borderStyle:Titanium.UI.INPUT_BORDERSTYLE_ROUNDED,
	top:0,
	right:0,
	width:200
});
v6.add(l6);
v6.add(f6);
*/
var b1 = Ti.UI.createButton({
	title:'Save',
	width:100,
	height:40,
	bottom:180,
	left:10
});

b1.addEventListener('click', function() {    
    if ( !f1.value || !f2.value ) {
        Titanium.UI.createAlertDialog({ title: "Please enter first name and last name"}).show( );
        return;
    }
    
    //open db for storing settings
    var db = Titanium.Database.open('civicrm');

    var rows = db.execute('SELECT REST_URL FROM CIVICRM_SETTINGS');

    var restURL = '';
    while (rows.isValidRow()) {
        restURL = rows.fieldByName('rest_url');
        rows.next();
    }
    rows.close();
    
    db.close(); // close db when you're done to save resources
    
    if ( !restURL ) {
        Titanium.UI.createAlertDialog({ title: "Please check your settings tab."}).show( );
        return;
    }
    
    var url = restURL + '&q=civicrm/contact/create';

    var xhr = Titanium.Network.createHTTPClient();

    xhr.onload = function() {
        var response = JSON.parse(this.responseText);
        //Ti.API.info(this.responseText);

        Titanium.UI.createAlertDialog({ title: "Your Individual contact record has been saved."}).show( );
        //f3.value = f4.value = f5.value = f6.value = f1.value = f2.value = f21.value = '';
        f1.value = f2.value = f21.value = '';
    };

    xhr.open("POST", url);

	var params  = {};

	params.contact_type = 'Individual';
	params.first_name   = f1.value;
	params.last_name    = f2.value;
	params.email        = f21.value;

    //params.address[1] = { street_address: f3.value, city: f4.value, state_province: f5.value, postal_code: f6.value, location_type_id: 1, is_primary: 1};
    
    //     var params.address = {};
    // params.address.street_address = f3.value;
    // params.address.city = f4.value;
    // params.address.state_province = f5.value;
    // params.address.postal_code = f6.value;
    //     params.address.location_type_id = 1;
    //     params.address.is_primary = 1;
    
    //params[address] = [];
    //params[address] = { street_address: f3.value, city: f4.value, state_province: f5.value, postal_code: f6.value, location_type_id: 1, is_primary: 1 };
    //params[address] = "[ [street_address: f3.value], [city: f4.value], [state_province: f5.value], [postal_code: f6.value], [location_type_id: 1], [is_primary: 1] ]";

    
    // params[address][street_address] = f3.value;
    // params[address][city] = f4.value;
    // params[address][state_province] = f5.value;
    // params[address][postal_code] = f6.value;
    // params[address][location_type_id] = 1;
    // params[address][is_primary] = 1;
    
    //Ti.API.info( params );
    xhr.send( params );
    
});

var b2 = Ti.UI.createButton({
	title:'Clear',
	width:100,
	height:40,
	bottom:180,
	left: 120
});

b2.addEventListener('click', function() {
	//f3.value = f4.value = f5.value = f6.value = f1.value = f2.value = '';
	f21.value = f1.value = f2.value = '';
});

win.add(v1);
win.add(v2);
win.add(v21);
/*
win.add(v3);
win.add(v4);
win.add(v5);
win.add(v6);
*/
win.add(b1);
win.add(b2);

//open db for storing settings
var db = Titanium.Database.open('civicrm');

var rows = db.execute('SELECT REST_URL FROM CIVICRM_SETTINGS');

var restURL = '';
while (rows.isValidRow()) {
    restURL = rows.fieldByName('rest_url');
    rows.next();
}
rows.close();

db.close(); // close db when you're done to save resources

if ( !restURL ) {
    Titanium.UI.createAlertDialog({ title: "Please check your settings tab."}).show( );
}
