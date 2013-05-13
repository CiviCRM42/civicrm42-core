
var win = Ti.UI.currentWindow;

var v1 = Ti.UI.createView({
	top:10,
	left:10,
	width:300,
	height:30
});
var l1 = Ti.UI.createLabel({
	text:'Module Path:',
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

var l2 = Ti.UI.createLabel({
	text:'eg: http://civicrm.org/sites/all/modules/',
	top:60,
	left:0
});

v1.add(l1);
v1.add(l2);
v1.add(f1);

var v2 = Ti.UI.createView({
	top:80,
	left:10,
	width:300,
	height:30
});
var l2 = Ti.UI.createLabel({
	text:'Site Key:',
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

var v31 = Ti.UI.createView({
	top:120,
	left:10,
	width:300,
	height:30
});
var l31 = Ti.UI.createLabel({
	text:'Username:',
	top:0,
	left:0
});
var f31 = Ti.UI.createTextField({
	text:'',
	borderStyle:Titanium.UI.INPUT_BORDERSTYLE_ROUNDED,
	top:0,
	right:0,
	width:200
});
v31.add(l31);
v31.add(f31);

var v4 = Ti.UI.createView({
	top:160,
	left:10,
	width:300,
	height:30
});
var l4 = Ti.UI.createLabel({
	text:'Password:',
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

var b1 = Ti.UI.createButton({
	title:'Validate & Save',
	width:130,
	height:40,
	bottom:100,
	left:10
});

b1.addEventListener('click', function() {  
    
    if ( !f1.value || !f2.value  || !f31.value || !f4.value ) {
        Titanium.UI.createAlertDialog({ title: "All fields are required."}).show( );
        return;
    }
    
    var url = f1.value + 'civicrm/extern/rest.php?q=civicrm/login&name=' + f31.value  +'&pass=' + f4.value +'&key=' + f2.value +'&json=1';

    var xhr = Titanium.Network.createHTTPClient();

    xhr.onload = function() {
        var response = JSON.parse(this.responseText);
        //Ti.API.info(this.responseText);
        
        if ( response.PHPSESSID ) {
            //open db for storing settings
            var db = Titanium.Database.open('civicrm');

            db.execute('DELETE FROM CIVICRM_SETTINGS');
            
            var restURL = f1.value + 'civicrm/extern/rest.php?name=' + f31.value  +'&pass=' + f4.value +'&key=' + f2.value +'&json=1&PHPSESSID=' + response.PHPSESSID ;
            
            db.execute('INSERT INTO CIVICRM_SETTINGS (ID, SITE_URL, SITE_KEY, USERNAME, PASSWORD, REST_URL ) VALUES(?,?,?,?,?,?)',1,f1.value, f2.value, f31.value, f4.value, restURL );

            db.close(); // close db when you're done to save resources
            
            Titanium.UI.createAlertDialog({ title: "Settings has been saved."}).show( );
        } else {
            Titanium.UI.createAlertDialog({ title: "There was some error, make sure you have entered correct values."}).show( );
        }
    };

    xhr.open("GET", url);
    xhr.send( );
});

var b2 = Ti.UI.createButton({
	title:'Clear',
	width:100,
	height:40,
	bottom:100,
	left: 150
});

b2.addEventListener('click', function() {
	f31.value =	f4.value = f1.value = f2.value = '';
});

win.add(v1);
win.add(v2);
//win.add(v3);
win.add(v31);
win.add(v4);

win.add(b1);
win.add(b2);

//create db for storing settings
var db = Titanium.Database.open('civicrm');

//create table
//db.execute('DROP TABLE CIVICRM_SETTINGS');

//create table
//db.execute('CREATE TABLE IF NOT EXISTS CIVICRM_SETTINGS  (ID INTEGER, SITE_URL TEXT, SITE_KEY TEXT, USERNAME TEXT, PASSWORD TEXT, REST_URL TEXT )');

var rows = db.execute('SELECT *  FROM CIVICRM_SETTINGS');

while (rows.isValidRow())
{
 f1.value  = rows.fieldByName('site_url');
 f2.value  = rows.fieldByName('site_key');
 f31.value = rows.fieldByName('username');
 f4.value  = rows.fieldByName('password');
  
 rows.next();
}
rows.close();

db.close(); // close db when you're done to save resources

