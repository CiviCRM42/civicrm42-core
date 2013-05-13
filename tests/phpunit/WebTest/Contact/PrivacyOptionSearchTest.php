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
class WebTest_Contact_PrivacyOptionSearchTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testPrivacyOptionSearch() {
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

    // Add new group.
    $this->open($this->sboxPath . "civicrm/group/add?&reset=1");
    $this->waitForElementPresent("_qf_Edit_upload");

    $groupName = 'group_' . substr(sha1(rand()), 0, 7);
    $this->type("title", $groupName);

    // Fill description.
    $this->type("description", "Adding new group.");

    // Check Access Control.
    $this->click("group_type[1]");

    // Clicking save.
    $this->click("_qf_Edit_upload");
    $this->waitForPageToLoad("30000");

    // Add Contact1.
    $fname1 = substr(sha1(rand()), 0, 7);
    $lname1 = substr(sha1(rand()), 0, 7);
    $this->open($this->sboxPath . 'civicrm/contact/add?reset=1&ct=Individual');
    $this->waitForElementPresent('_qf_Contact_upload_view-bottom');
    $this->type('first_name', $fname1);
    $this->type('last_name', $lname1);
    $email1 = $fname1 . '@example.org';
    $this->type('email_1_email', $email1);

    $this->click("commPrefs");
    $this->click("privacy[do_not_phone]");
    $this->click("privacy[do_not_email]");
    $this->click("privacy[do_not_mail]");
    $this->click("privacy[do_not_sms]");

    $this->click('_qf_Contact_upload_view-bottom');
    $this->waitForPageToLoad('30000');

    // Add contact to the group.
    $this->click("css=li#tab_group a");
    $this->waitForElementPresent("group_id");
    $this->select("group_id", "label=$groupName");
    $this->click("_qf_GroupContact_next");
    $this->waitForPageToLoad("30000");

    // Add Contact2.
    $fname2 = substr(sha1(rand()), 0, 7);
    $lname2 = substr(sha1(rand()), 0, 7);
    $this->open($this->sboxPath . 'civicrm/contact/add?reset=1&ct=Individual');
    $this->waitForElementPresent('_qf_Contact_upload_view-bottom');
    $this->type('first_name', $fname2);
    $this->type('last_name', $lname2);
    $email2 = $fname2 . '@example.org';
    $this->type('email_1_email', $email2);

    $this->click("commPrefs");
    $this->click("privacy[do_not_phone]");
    $this->click("privacy[do_not_email]");
    $this->click("privacy[do_not_trade]");

    $this->click('_qf_Contact_upload_view-bottom');
    $this->waitForPageToLoad('30000');

    // Add contact to the group.
    $this->click("css=li#tab_group a");
    $this->waitForElementPresent("group_id");
    $this->select("group_id", "label=$groupName");
    $this->click("_qf_GroupContact_next");
    $this->waitForPageToLoad("30000");

    // Go to advance search, check for 'Exclude' option.
    $this->open($this->sboxPath . "civicrm/contact/search/advanced?reset=1");
    $this->waitForPageToLoad('30000');

    $this->select("xpath=//form[@id='Advanced']//table[1]/tbody/tr[2]/td[2]//select[1]", "label={$groupName}");
    $this->waitForTextPresent($groupName);

    $this->select("xpath=//form[@id='Advanced']//table[1]/tbody/tr[5]/td[1]/table[1]/tbody/tr[2]/td[1]//select[1]", 'value=do_not_phone');
    $this->waitForTextPresent('Do not phone');

    $this->select("xpath=//form[@id='Advanced']//table[1]/tbody/tr[5]/td[1]/table[1]/tbody/tr[2]/td[1]//select[1]", 'value=do_not_email');
    $this->waitForTextPresent('Do not email');

    $this->click("_qf_Advanced_refresh");
    $this->waitForPageToLoad("60000");

    $this->assertTrue($this->isTextPresent("No matches found"));

    // Go to advance search, check for 'Include' + 'OR' options.
    $this->open($this->sboxPath . "civicrm/contact/search/advanced?reset=1");
    $this->waitForPageToLoad('30000');

    $this->select("xpath=//form[@id='Advanced']//table[1]/tbody/tr[2]/td[2]//select[1]", "label={$groupName}");
    $this->waitForTextPresent($groupName);

    $this->click("xpath=//form[@id='Advanced']//table[1]/tbody/tr[5]/td[1]/table[1]/tbody/tr[1]/td[1]//input[2]");

    $this->select("xpath=//form[@id='Advanced']//table[1]/tbody/tr[5]/td[1]/table[1]/tbody/tr[2]/td[1]//select[1]", 'value=do_not_phone');
    $this->waitForTextPresent('Do not phone');

    $this->select("xpath=//form[@id='Advanced']//table[1]/tbody/tr[5]/td[1]/table[1]/tbody/tr[2]/td[1]//select[1]", 'value=do_not_email');
    $this->waitForTextPresent('Do not email');

    $this->click("_qf_Advanced_refresh");
    $this->waitForPageToLoad("60000");
    $this->assertTrue($this->isTextPresent("2 Contacts"));
    $this->assertTrue($this->isTextPresent("$lname1, $fname1"));
    $this->assertTrue($this->isTextPresent("$lname2, $fname2"));

    // Go to advance search, check for 'Include' + 'AND' options.
    $this->open($this->sboxPath . "civicrm/contact/search/advanced?reset=1");
    $this->waitForPageToLoad('30000');

    $this->select("xpath=//form[@id='Advanced']//table[1]/tbody/tr[2]/td[2]//select[1]", "label={$groupName}");
    $this->waitForTextPresent($groupName);

    $this->click("xpath=//form[@id='Advanced']//table[1]/tbody/tr[5]/td[1]/table[1]/tbody/tr[1]/td[1]//input[2]");

    $this->select("xpath=//form[@id='Advanced']//table[1]/tbody/tr[5]/td[1]/table[1]/tbody/tr[2]/td[1]//select[1]", 'value=do_not_phone');
    $this->waitForTextPresent('Do not phone');

    $this->select("xpath=//form[@id='Advanced']//table[1]/tbody/tr[5]/td[1]/table[1]/tbody/tr[2]/td[1]//select[1]", 'value=do_not_trade');
    $this->waitForTextPresent('Do not trade');

    $this->select('privacy_operator', 'value=AND');

    $this->click("_qf_Advanced_refresh");
    $this->waitForPageToLoad("60000");
    $this->assertTrue($this->isTextPresent("1 Contact"));
    $this->assertTrue($this->isTextPresent("$lname2, $fname2"));
  }
}

