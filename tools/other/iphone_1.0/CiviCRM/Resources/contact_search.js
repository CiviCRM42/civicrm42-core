
var win = Ti.UI.currentWindow;

/*
var search = Titanium.UI.createSearchBar({
    barColor:'#000', 
    showCancel:true,
    height:43,
    top:0
});

win1.add( search );
*/
var search = Titanium.UI.createSearchBar({
	barColor:'#385292', 
	showCancel:false,
	hintText:'search'
});


search.addEventListener('change', function(e)
{
   //Ti.API.info(e.value); 
   if ( e.value ) {
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

       var url = restURL + '&q=civicrm/contact/search';

       var xhr = Titanium.Network.createHTTPClient();

       xhr.onload = function() {
           var response = JSON.parse(this.responseText);
           //Ti.API.info(this.responseText);
           var data = [];
           if ( response.length ) {
               for (var i = 0; i < response.length; i++) { 
                  //tableview.appendRow({title: response[i].sort_name});
                  data[ i ] = Titanium.UI.createTableViewRow({title: response[i].sort_name});
               }
           } else {
               data[0] = Ti.UI.createTableViewRow({title:'No match found'});               
           }
       
           tableview.setData( data );
       };

       xhr.open("POST", url);
       xhr.send({sort_name:e.value});
   } else {
      var data = [];
      data[0] = Ti.UI.createTableViewRow({title:'Enter contact name'});
      tableview.setData( data );
   } 
});

search.addEventListener('return', function(e)
{
   search.blur();
});

search.addEventListener('cancel', function(e)
{
   search.blur();
});

// create table view data object
var data = [];
data[0] = Ti.UI.createTableViewRow({title:'Enter contact name'});


// create table view
var tableview = Titanium.UI.createTableView({
    data:data,
	search:search,
	searchHidden:true
});

/*
// create table view event listener
tableview.addEventListener('click', function(e)
{
	// event data
	var index = e.index;
	var section = e.section;
	var row = e.row;
	var rowdata = e.rowData;
	var className = e.className;
	Titanium.UI.createAlertDialog({title:'Table View',message:'row ' + row + ' index ' + index + ' section ' + section  + ' row data ' + rowdata + 'ddd' + className }).show();
});
*/

var hide = Titanium.UI.createButtonBar({
	labels:['Hide', 'Show'],
	backgroundColor:'#336699',
	height:25,
	width:120
});

// add table view to the window
win.add(tableview);

hide.addEventListener('click', function(e)
{
	if (e.index == 0)
	{
		tableview.searchHidden = true;
	}
	else if (e.index == 1)
	{
	    tableview.searchHidden = false;
		tableview.scrollToTop(0,{animated:true});
	}
});

win.setRightNavButton(hide);


//open db for storing settings
var db = Titanium.Database.open('civicrm');

// create table 
db.execute('CREATE TABLE IF NOT EXISTS CIVICRM_SETTINGS  (ID INTEGER, SITE_URL TEXT, SITE_KEY TEXT, USERNAME TEXT, PASSWORD TEXT, REST_URL TEXT )');

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