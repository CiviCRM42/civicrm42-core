<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/


require_once 'CiviTest/CiviSeleniumTestCase.php';
class WebTest_Contact_CustomDataAddTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testCustomDataAdd() {
    // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);

    // Logging in. Remember to wait for page to load. In most cases,
    // you can rely on 30000 as the value that allows your test to pass, however,
    // sometimes your test might fail because of this. In such cases, it's better to pick one element
    // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
    // page contents loaded and you can continue your test execution.
    $this->webtestLogin();

    // Go directly to the URL of the screen that you will be testing (New Custom Group).
    $this->open($this->sboxPath . "civicrm/admin/custom/group?action=add&reset=1");

    $this->waitForPageToLoad("30000");

    //fill custom group title
    $customGroupTitle = 'custom_' . substr(sha1(rand()), 0, 7);
    $this->click("title");
    $this->type("title", $customGroupTitle);

    //custom group extends
    $this->click("extends[0]");
    $this->select("extends[0]", "value=Contact");
    $this->click("//option[@value='Contact']");
    $this->click("_qf_Group_next-bottom");
    $this->waitForElementPresent("_qf_Field_cancel-bottom");

    //Is custom group created?
    $this->assertTrue($this->isTextPresent("Your custom field set '{$customGroupTitle}' has been added. You can add custom fields now."));

    //add custom field - alphanumeric checkbox
    $checkboxFieldLabel = 'custom_field' . substr(sha1(rand()), 0, 4);
    $this->click("label");
    $this->type("label", $checkboxFieldLabel);
    $this->click("data_type[1]");
    $this->select("data_type[1]", "value=CheckBox");
    $this->click("//option[@value='CheckBox']");
    $checkboxOptionLabel1 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
    $this->type("option_label_1", $checkboxOptionLabel1);
    $this->type("option_value_1", "1");
    $checkboxOptionLabel2 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
    $this->type("option_label_2", $checkboxOptionLabel2);
    $this->type("option_value_2", "2");
    $this->click("link=another choice");
    $checkboxOptionLabel3 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
    $this->type("option_label_3", $checkboxOptionLabel3);
    $this->type("option_value_3", "3");


    //enter options per line
    $this->type("options_per_line", "2");

    //enter pre help message
    $this->type("help_pre", "this is field pre help");

    //enter post help message
    $this->type("help_post", "this field post help");

    //Is searchable?
    $this->click("is_searchable");

    //clicking save
    $this->click("_qf_Field_next");
    $this->waitForPageToLoad("30000");

    //Is custom field created?
    $this->assertTrue($this->isTextPresent("Your custom field '$checkboxFieldLabel' has been saved."));

    //create another custom field - Integer Radio
    $this->click("//a[@id='newCustomField']/span");
    $this->waitForPageToLoad("30000");
    $this->click("data_type[0]");
    $this->select("data_type[0]", "value=1");
    $this->click("//option[@value='1']");
    $this->click("data_type[1]");
    $this->select("data_type[1]", "value=Radio");
    $this->click("//option[@value='Radio']");

    $radioFieldLabel = 'custom_field' . substr(sha1(rand()), 0, 4);
    $this->type("label", $radioFieldLabel);
    $radioOptionLabel1 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
    $this->type("option_label_1", $radioOptionLabel1);
    $this->type("option_value_1", "1");
    $radioOptionLabel2 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
    $this->type("option_label_2", $radioOptionLabel2);
    $this->type("option_value_2", "2");
    $this->click("link=another choice");
    $radioOptionLabel3 = 'optionLabel_' . substr(sha1(rand()), 0, 5);
    $this->type("option_label_3", $radioOptionLabel3);
    $this->type("option_value_3", "3");

    //select options per line
    $this->type("options_per_line", "3");

    //enter pre help msg
    $this->type("help_pre", "this is field pre help");

    //enter post help msg
    $this->type("help_post", "this is field post help");

    //Is searchable?
    $this->click("is_searchable");

    //clicking save
    $this->click("_qf_Field_next");
    $this->waitForPageToLoad("30000");

    //Is custom field created
    $this->assertTrue($this->isTextPresent("Your custom field '$radioFieldLabel' has been saved."));

    //create Individual contact
    $this->click("//ul[@id='civicrm-menu']/li[4]");
    $this->click("//div[@id='root-menu-div']/div[5]/ul/li[1]/div/a");
    $this->waitForPageToLoad("30000");

    //expand all tabs
    $this->click("expand");
    $this->waitForElementPresent("address_1_street_address");

    //fill first name, last name, email id
    $firstName = 'Ma' . substr(sha1(rand()), 0, 4);
    $lastName  = 'An' . substr(sha1(rand()), 0, 7);
    $emailId   = substr(sha1(rand()), 0, 7) . '@web.com';
    $this->click("first_name");
    $this->type("first_name", $firstName);
    $this->type("last_name", $lastName);
    $this->type("email_1_email", $emailId);

    //fill custom values for the contact
    $this->click("xpath=//table//tr/td/label[text()=\"$checkboxOptionLabel2\"]");
    $this->click("xpath=//table//tr/td/label[text()=\"$radioOptionLabel3\"]");
    $this->click("_qf_Contact_upload_view");
    $this->waitForPageToLoad("30000");
  }

  function testCustomDataMoneyAdd() {
    // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);

    // Logging in. Remember to wait for page to load. In most cases,
    // you can rely on 30000 as the value that allows your test to pass, however,
    // sometimes your test might fail because of this. In such cases, it's better to pick one element
    // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
    // page contents loaded and you can continue your test execution.
    $this->webtestLogin();
    $this->waitForPageToLoad("30000");

    // Go directly to the URL of the screen that you will be testing (New Custom Group).
    $this->open($this->sboxPath . "civicrm/admin/custom/group?action=add&reset=1");

    $this->waitForPageToLoad("30000");

    //fill custom group title
    $customGroupTitle = 'custom_' . substr(sha1(rand()), 0, 7);
    $this->waitForElementPresent("title");
    $this->click("title");
    $this->type("title", $customGroupTitle);

    //custom group extends
    $this->click("extends[0]");
    $this->select("extends[0]", "value=Contact");
    $this->click("//option[@value='Contact']");
    $this->click("_qf_Group_next-bottom");
    $this->waitForElementPresent("_qf_Field_cancel-bottom");
    
    //Is custom group created?
    $this->assertTrue($this->isTextPresent("Your custom field set '{$customGroupTitle}' has been added. You can add custom fields now."));

    //add custom field - money text
    $moneyTextFieldLabel = 'money' . substr(sha1(rand()), 0, 4);
    $this->click("label");
    $this->type("label", $moneyTextFieldLabel);
    $this->waitForElementPresent("data_type[0]");
    $this->click("data_type[0]");
    $this->select("data_type[0]", "label=Money");

    $this->click("data_type[1]");
    $this->select("data_type[1]", "value=Text");

    //enter pre help message
    $this->type("help_pre", "this is field pre help");

    //enter post help message
    $this->type("help_post", "this field post help");

    //Is searchable?
    $this->click("is_searchable");

    //clicking save
    $this->click("_qf_Field_next");
    $this->waitForPageToLoad("30000");

    //Is custom field created?
    $this->assertTrue($this->isTextPresent("Your custom field '$moneyTextFieldLabel' has been saved."));
    
    //Get the customFieldsetID
    $this->open($this->sboxPath . "civicrm/admin/custom/group?reset=1");
    $this->waitForPageToLoad("30000");
    $customFieldsetId = explode('&gid=', $this->getAttribute("xpath=//div[@id='custom_group']//table/tbody//tr/td/span[text()='$customGroupTitle']/../../td[7]/span/a@href"));
    $customFieldsetId =  $customFieldsetId[1];

    //create Individual contact
    $this->click("//ul[@id='civicrm-menu']/li[4]");
    $this->click("//div[@id='root-menu-div']/div[5]/ul/li[1]/div/a");
    $this->waitForPageToLoad("30000");

    //expand all tabs
    $this->click("expand");
    $this->waitForElementPresent("address_1_street_address");

    //fill first name, last name, email id
    $firstName = 'Ma' . substr(sha1(rand()), 0, 4);
    $lastName  = 'An' . substr(sha1(rand()), 0, 7);
    $emailId   = substr(sha1(rand()), 0, 7) . '@web.com';
    $this->click("first_name");
    $this->type("first_name", $firstName);
    $this->type("last_name", $lastName);
    $this->type("email_1_email", $emailId);

    //fill custom values for the contact
    $this->click("xpath=//table//tr/td/label[text()=\"$moneyTextFieldLabel\"]");    
    $this->type("xpath=//table//tr/td/label[text()=\"$moneyTextFieldLabel\"]/../following-sibling::td/input", "12345678.98");
    $this->click("_qf_Contact_upload_view");
    $this->waitForPageToLoad("30000");
   
    //verify the money custom field value in the proper format
    $this->assertTrue($this->isElementPresent("xpath=//div[@class='customFieldGroup ui-corner-all $customGroupTitle crm-custom-set-block-{$customFieldsetId}']/table/tbody/tr[2]/td/div/div/div/div[3]"));
    $this->verifyText("xpath=//div[@class='customFieldGroup ui-corner-all $customGroupTitle crm-custom-set-block-{$customFieldsetId}']/table/tbody/tr[2]/td/div/div/div/div[3]", '12,345,678.98');
  }
  
  function testCustomDataChangeLog(){
     // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);

    // Logging in. Remember to wait for page to load. In most cases,
    // you can rely on 30000 as the value that allows your test to pass, however,
    // sometimes your test might fail because of this. In such cases, it's better to pick one element
    // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
    // page contents loaded and you can continue your test execution.
    $this->webtestLogin();
    
    //enable logging
    $this->open($this->sboxPath . "civicrm/admin/setting/misc?reset=1");
    $this->click("CIVICRM_QFID_1_6");
    $this->click("_qf_Miscellaneous_next-top");
    //adding sleep her since enabling logging takes lot of time
    sleep(40);
    $this->waitForTextPresent("Your changes have been saved");

    // Create new Custom Field Set
    $this->open($this->sboxPath . 'civicrm/admin/custom/group?reset=1');
    $this->waitForPageToLoad("30000");
    $this->click("css=#newCustomDataGroup > span");
    $this->waitForElementPresent('_qf_Group_next-bottom');
    $customFieldSet = 'Fieldset' . rand();
    $this->type("id=title", $customFieldSet);
    $this->select("id=extends[0]", "label=Individual");
    $this->click("id=collapse_display");
    $this->click("id=_qf_Group_next-bottom");
    $this->waitForElementPresent('_qf_Field_next-bottom');
    $this->assertTrue($this->isTextPresent("Your custom field set '$customFieldSet' has been added."));

    // Add field to fieldset
    $customField = 'CustomField' . rand();
    $this->type("id=label", $customField);
    $this->select("id=data_type[0]", "value=0");
    $this->click("id=_qf_Field_next-bottom");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Your custom field '$customField' has been saved."));

    // Go directly to the URL of the screen that you will be testing (New Individual).
    $this->open($this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual");

    //contact details section
    //fill in first name
    $firstName = 'Jimmy'.substr(sha1(rand()), 0, 7);
    $this->type('first_name', $firstName);
    
    //fill in last name
    $lastName = 'Page'.substr(sha1(rand()), 0, 7);
    $this->type('last_name', $lastName);

    //fill in email id
    $this->type('email_1_email', "{$firstName}.{$lastName}@example.com");

    //fill in phone
    $this->type("phone_1_phone", "2222-4444");
    
    $this->click("xpath=//table//tr/td/label[text()=\"$customField\"]");
    $value = "custom".rand();
    $this->type("xpath=//table//tr/td/label[text()=\"$customField\"]/../following-sibling::td/input",$value);

    //check for matching contact
    $this->click("_qf_Contact_refresh_dedupe");
    $this->waitForPageToLoad("30000");

    //address section
    $this->click("addressBlock");
    $this->waitForElementPresent("address_1_street_address");
    //fill in address 1
    $this->type("address_1_street_address", "902C El Camino Way SW");
    $this->type("address_1_city", "Dumfries");
    $this->type("address_1_postal_code", "1234");

    $this->click("address_1_country_id");
    $this->select("address_1_country_id", "value=1228");

    if ($this->isTextPresent("Latitude")) {
      $this->type("address_1_geo_code_1", "1234");
      $this->type("address_1_geo_code_2", "5678");
    }
    
    // Clicking save.
    $this->click("_qf_Contact_upload_view");
    $this->waitForPageToLoad("30000");
    
    $this->assertTrue($this->isTextPresent("Your Individual contact record has been saved."));

    //Update the custom field
    $this->click("xpath=//div[@class='crm-actions-ribbon']/ul/li[2]/a/span[contains(text(), 'Edit')]");
    $this->waitForPageToLoad("30000");
    $this->click("xpath=//table//tr/td/label[text()=\"$customField\"]");
    $value1 = "custom_1".rand();
    $this->type("xpath=//table//tr/td/label[text()=\"$customField\"]/../following-sibling::td/input",$value1);
    $this->click("_qf_Contact_upload_view-bottom");
    $this->waitForPageToLoad("30000");
    $this->click("xpath=//li[@id='tab_log']/a");
    
    //check the changed log
    $this->waitForElementPresent("xpath=//div[@id='instance_data']/div[2]/table/tbody/tr[1]/td[3]/a[contains(text(), '$firstName $lastName')]");
    $this->waitForElementPresent("xpath=//div[@id='instance_data']/div[2]/table/tbody/tr[1]/td[4]/a");
    $this->click("xpath=//div[@id='instance_data']/div[2]/table/tbody/tr[1]/td[4]/a");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isElementPresent("xpath=//form[@id='LoggingDetail']/div[2]/table/tbody/tr/td[2][contains(text(), '$value')]"));
    $this->assertTrue($this->isElementPresent("xpath=//form[@id='LoggingDetail']/div[2]/table/tbody/tr/td[3][contains(text(), '$value1')]"));

    //disable logging
    $this->open($this->sboxPath . "civicrm/admin/setting/misc?reset=1");
    $this->click("CIVICRM_QFID_0_8");
    $this->click("_qf_Miscellaneous_next-top");
    //adding sleep here since disabling logging takes lot of time
    sleep(40);
    $this->waitForTextPresent("Your changes have been saved");
  }
  
}


