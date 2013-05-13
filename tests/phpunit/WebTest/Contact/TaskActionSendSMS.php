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
class WebTest_Contact_TaskActionSendSMS extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testSMSToContacts() {
    $this->open($this->sboxPath);
    $this->webtestLogin();

    // ADD a New Group
    $this->open($this->sboxPath . "civicrm/group/add?reset=1");
    $this->waitForElementPresent("_qf_Edit_upload");

    $smsGroupName = 'group_' . substr(sha1(rand()), 0, 7);

    $this->type("title", $smsGroupName);
    $this->type("description", "New sms group for Webtest");
    $this->select("visibility", "value=Public Pages");

    $this->click("_qf_Edit_upload");
    $this->waitForPageToLoad("30000");

    // ADD contact1
    $this->open($this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual");
    $this->waitForPageToLoad("30000");
    $firstName = substr(sha1(rand()), 0, 7);
    $this->type('first_name', $firstName);

    $lastName = substr(sha1(rand()), 0, 7);
    $this->type('last_name', $lastName);

    $this->waitForElementPresent('phone_1_phone');
    $this->type('phone_1_phone', "911234567890");

    $this->select('phone_1_phone_type_id', 'label=Mobile');

    $this->click("_qf_Contact_upload_view");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Your Individual contact record has been saved."));

    $this->click('css=li#tab_group a');
    $this->waitForElementPresent('_qf_GroupContact_next');
    $this->select('group_id', "label=$smsGroupName");
    $this->click('_qf_GroupContact_next');
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Contact has been added to the selected group "));

    // ADD contact2
    $this->open($this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual");
    $this->waitForPageToLoad("30000");
    $firstName = substr(sha1(rand()), 0, 7);
    $this->type('first_name', $firstName);

    $lastName = substr(sha1(rand()), 0, 7);
    $this->type('last_name', $lastName);

    $this->waitForElementPresent('phone_1_phone');
    $this->type('phone_1_phone', "911234567891");

    $this->select('phone_1_phone_type_id', 'label=Mobile');

    $this->click("_qf_Contact_upload_view");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Your Individual contact record has been saved."));

    $this->click('css=li#tab_group a');
    $this->waitForElementPresent('_qf_GroupContact_next');
    $this->select('group_id', "label=$smsGroupName");
    $this->click('_qf_GroupContact_next');
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Contact has been added to the selected group "));

    // Do an advanced search
    $this->click("css=ul#civicrm-menu li.crm-Search");
    $this->click("css=ul#civicrm-menu li.crm-Advanced_Search a");

    $this->waitForPageToLoad("30000");
    $this->waitForElementPresent("email");

    $this->select("crmasmSelect1", "label=$smsGroupName");

    $this->click("_qf_Advanced_refresh");
    $this->waitForPageToLoad("30000");

    $this->waitForElementPresent('CIVICRM_QFID_ts_all_12');
    $this->click('CIVICRM_QFID_ts_all_12');

    // Perform a task action
    $this->select("task", "label=Send SMS to Contacts");
    $this->click("Go");
    $this->waitForPageToLoad("30000");

    $this->waitForElementPresent('activity_subject');
    $this->type('activity_subject', "Send SMS to Contacts of {$smsGroupName}");
    $this->type('text_message', "Test SMS to Contacts of {$smsGroupName}");
    $this->click("_qf_SMS_upload-bottom");
    $this->waitForPageToLoad("30000");

    $this->assertTrue($this->isTextPresent('Your message has been sent.'), "Test SMS could not be sent!");
  }
}


