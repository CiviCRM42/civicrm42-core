
// this sets the background color of the master UIView (when there are no windows/tab groups on it)
Titanium.UI.setBackgroundColor('#000');

// create tab group
var tabGroup = Titanium.UI.createTabGroup();

//
// create base UI tab and root window for Search Contact
//
var win1 = Titanium.UI.createWindow({  
    title:'Search Contacts',
    backgroundColor:'#fff',
    url: 'contact_search.js'
});
var tab1 = Titanium.UI.createTab({  
    icon:'KS_nav_views.png',
    title:'Search Contacts',
    window:win1
});


//
// create controls tab and root window for Add Contacts
//
var win2 = Titanium.UI.createWindow({  
    title:'Add Contact',
    backgroundColor:'#fff',
    url: 'contact_add.js'
});
var tab2 = Titanium.UI.createTab({  
    icon:'KS_nav_ui.png',
    title:'Add Contact',
    window:win2
});

//
// create controls tab and root window for Settings
//
var win3 = Titanium.UI.createWindow({  
    title:'Settings',
    backgroundColor:'#fff',
    url: 'settings.js'
});
var tab3 = Titanium.UI.createTab({  
    icon:'KS_nav_ui.png',
    title:'Settings',
    window:win3
});

//
//  add tabs
//
tabGroup.addTab(tab1);  
tabGroup.addTab(tab2);  
tabGroup.addTab(tab3); 

// open tab group
tabGroup.open();
