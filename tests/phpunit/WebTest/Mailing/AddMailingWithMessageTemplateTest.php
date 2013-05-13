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
class WebTest_Mailing_AddMailingWithMessageTemplateTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testAddMailingWithMessageTemplate() {

    include_once ('WebTest/Mailing/AddMessageTemplateTest.php');
    $useTokens = TRUE;
    $msgTitle = 'msg_' . substr(sha1(rand()), 0, 7);
    WebTest_Mailing_AddMessageTemplateTest::testTemplateAdd($useTokens, $msgTitle);

    // Go directly to the URL of the screen that you will be testing (New Group).
    $this->open($this->sboxPath . "civicrm/group/add?reset=1");
    $this->waitForElementPresent("_qf_Edit_upload");

    // create new mailing group
    $groupName = 'group_' . substr(sha1(rand()), 0, 7);
    $this->type("title", $groupName);
    $this->type("description", "New mailing group for Webtest");
    $this->click("group_type[2]");
    $this->select("visibility", "value=Public Pages");

    // Clicking save.
    $this->click("_qf_Edit_upload");
    $this->waitForPageToLoad("30000");

    // Is status message correct?
    $this->assertTrue($this->isTextPresent("The Group '$groupName' has been saved."));

    //Create new contact and add to mailing Group
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Mailson", "mailino$firstName@mailson.co.in");
    $this->click("css=li#tab_group a");
    $this->waitForElementPresent("_qf_GroupContact_next");
    $this->select("group_id", "$groupName");
    $this->click("_qf_GroupContact_next");

    // configure default mail-box
    $this->open($this->sboxPath . "civicrm/admin/mailSettings?action=update&id=1&reset=1");
    $this->waitForElementPresent('_qf_MailSettings_cancel-bottom');
    $this->type('name', 'Test Domain');
    $this->type('domain', 'example.com');
    $this->select('protocol', 'value=1');
    $this->click('_qf_MailSettings_next-bottom');
    $this->waitForPageToLoad("30000");

    // Go directly to Schedule and Send Mailing form
    $this->open($this->sboxPath . "civicrm/mailing/send?reset=1");
    $this->waitForElementPresent("_qf_Group_cancel");

    // fill mailing name
    $mailingName = substr(sha1(rand()), 0, 7);
    $this->type("name", "Mailing $mailingName Webtest");

    // Add the test mailing group
    $this->select("includeGroups-f", "$groupName");
    $this->click("add");

    // click next
    $this->click("_qf_Group_next");
    $this->waitForElementPresent("_qf_Settings_cancel");
    // check for default settings options
    $this->assertChecked("url_tracking");
    $this->assertChecked("open_tracking");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));
    $this->click("_qf_Settings_next");
    $this->waitForElementPresent("_qf_Upload_cancel");

    $this->click("template");
    $this->select("template", "label=$msgTitle");
    sleep(5);
    $this->click("xpath=id('Upload')/div[2]/fieldset[@id='compose_id']/div[2]/div[1]");
    $this->click('subject');

    // check for default header and footer ( with label )
    $this->select('header_id', "label=Mailing Header");
    $this->select('footer_id', "label=Mailing Footer");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // click next with nominal content
    $this->click("_qf_Upload_upload");
    $this->waitForElementPresent("_qf_Test_cancel");

    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // click next
    $this->click("_qf_Test_next");
    $this->waitForElementPresent("_qf_Schedule_cancel");

    $this->assertChecked("now");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // finally schedule the mail by clicking submit
    $this->click("_qf_Schedule_next");
    $this->waitForPageToLoad("30000");

    //check redirected page to Scheduled and Sent Mailings and  verify for mailing name
    $this->assertTrue($this->isTextPresent("Scheduled and Sent Mailings"));
    $this->assertTrue($this->isTextPresent("Mailing $mailingName Webtest"));
    $this->open($this->sboxPath . "civicrm/mailing/queue?reset=1");
    $this->waitForPageToLoad("300000");

    // verify status
    $this->verifyText("xpath=id('Search')/table/tbody/tr[1]/td[2]", preg_quote("Complete"));

    //View Activity
    $this->open($this->sboxPath . "civicrm/activity/search?reset=1");
    $this->waitForElementPresent("_qf_Search_refresh");
    $this->type("sort_name", $firstName);
    $this->click("activity_type_id[19]");
    $this->click("_qf_Search_refresh");
    $this->waitForElementPresent("_qf_Search_next_print");

    $this->click("xpath=id('Search')/div[3]/div/div[2]/table/tbody/tr[2]/td[9]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_ActivityView_next");
    $this->assertTrue($this->isTextPresent("Bulk Email Sent."), "Status message didn't show up after saving!");
  }
}


