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
class WebTest_Mailing_MailingTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testAddMailing() {

    $this->open($this->sboxPath);
    $this->webtestLogin();

    //----do create test mailing group

    // Go directly to the URL of the screen that you will be testing (New Group).
    $this->open($this->sboxPath . "civicrm/group/add?reset=1");
    $this->waitForElementPresent("_qf_Edit_upload");

    // make group name
    $groupName = 'group_' . substr(sha1(rand()), 0, 7);

    // fill group name
    $this->type("title", $groupName);

    // fill description
    $this->type("description", "New mailing group for Webtest");

    // enable Mailing List
    $this->click("group_type[2]");

    // select Visibility as Public Pages
    $this->select("visibility", "value=Public Pages");

    // Clicking save.
    $this->click("_qf_Edit_upload");
    $this->waitForPageToLoad("30000");

    // Is status message correct?
    $this->assertTrue($this->isTextPresent("The Group '$groupName' has been saved."));

    //---- create mailing contact and add to mailing Group
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Mailson", "mailino$firstName@mailson.co.in");

    // Get contact id from url.
    $matches = array();
    preg_match('/cid=([0-9]+)/', $this->getLocation(), $matches);
    $contactId = $matches[1];

    // go to group tab and add to mailing group
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

    //-------select recipients----------

    // fill mailing name
    $mailingName = substr(sha1(rand()), 0, 7);
    $this->type("name", "Mailing $mailingName Webtest");

    // Add the test mailing group
    $this->select("includeGroups-f", "$groupName");
    $this->click("add");

    // click next
    $this->click("_qf_Group_next");
    $this->waitForElementPresent("_qf_Settings_cancel");

    //--------track and respond----------

    // check for default settings options
    $this->assertChecked("url_tracking");
    $this->assertChecked("open_tracking");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // no need tracking for this test

    // click next with default settings
    $this->click("_qf_Settings_next");
    $this->waitForElementPresent("_qf_Upload_cancel");


    //--------Mailing content------------
    // let from email address be default

    // fill subject for mailing
    $this->type("subject", "Test subject {$mailingName} for Webtest");

    // check for default option enabled
    $this->assertChecked("CIVICRM_QFID_1_4");

    // HTML format message
    $HTMLMessage = "This is HTML formatted content for Mailing {$mailingName} Webtest.";
    $this->fillRichTextField("html_message", $HTMLMessage);

    // Open Plain-text Format pane and type text format msg
    $this->click("//fieldset[@id='compose_id']/div[2]/div[1]");
    $this->type("text_message", "This is text formatted content for Mailing {$mailingName} Webtest.");


    // select default header and footer ( with label )
    $this->select("header_id", "label=Mailing Header");
    $this->select("footer_id", "label=Mailing Footer");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // click next with nominal content
    $this->click("_qf_Upload_upload");
    $this->waitForElementPresent("_qf_Test_cancel");

    //---------------Test------------------

    ////////--Commenting test mailing and mailing preview (test mailing and preview not presently working).

    // send test mailing
    //$this->type("test_email", "mailino@mailson.co.in");
    //$this->click("sendtest");

    // verify status message
    //$this->assertTrue($this->isTextPresent("Your test message has been sent. Click 'Next' when you are ready to Schedule or Send your live mailing (you will still have a chance to confirm or cancel sending this mailing on the next page)."));

    // check mailing preview
    //$this->click("//form[@id='Test']/div[2]/div[4]/div[1]");
    //$this->assertTrue($this->isTextPresent("this is test content for Mailing $mailingName Webtest"));

    ////////

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // click next
    $this->click("_qf_Test_next");
    $this->waitForElementPresent("_qf_Schedule_cancel");

    //----------Schedule or Send------------

    // do check for default option enabled
    $this->assertChecked("now");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // finally schedule the mail by clicking submit
    $this->click("_qf_Schedule_next");
    $this->waitForPageToLoad("30000");

    //----------end New Mailing-------------

    //check redirected page to Scheduled and Sent Mailings and  verify for mailing name
    $this->assertTrue($this->isTextPresent("Scheduled and Sent Mailings"));
    $this->assertTrue($this->isTextPresent("Mailing $mailingName Webtest"));


    //--------- mail delivery verification---------

    // test undelivered report

    // click report link of created mailing
    $this->click("xpath=//table//tbody/tr[td[1]/text()='Mailing $mailingName Webtest']/descendant::a[text()='Report']");
    $this->waitForPageToLoad("30000");

    // verify undelivered status message
    $this->assertTrue($this->isTextPresent("Delivery has not yet begun for this mailing. If the scheduled delivery date and time is past, ask the system administrator or technical support contact for your site to verify that the automated mailer task ('cron job') is running - and how frequently."));

    // do check for recipient group
    $this->assertTrue($this->isTextPresent("Members of $groupName"));

    // directly send schedule mailing -- not working right now
    $this->open($this->sboxPath . "civicrm/mailing/queue?reset=1");
    $this->waitForPageToLoad("300000");

    //click report link of created mailing
    $this->click("xpath=//table//tbody/tr[td[1]/text()='Mailing $mailingName Webtest']/descendant::a[text()='Report']");
    $this->waitForPageToLoad("30000");

    // do check again for recipient group
    $this->assertTrue($this->isTextPresent("Members of $groupName"));

    // check for 100% delivery
    $this->assertTrue($this->isTextPresent("1 (100.00%)"));

    // verify intended recipients
    $this->verifyText("xpath=//table//tr[td/a[text()='Intended Recipients']]/descendant::td[2]", preg_quote("1"));

    // verify successful deliveries
    $this->verifyText("xpath=//table//tr[td/a[text()='Successful Deliveries']]/descendant::td[2]", preg_quote("1 (100.00%)"));

    // verify status
    $this->verifyText("xpath=//table//tr[td[1]/text()='Status']/descendant::td[2]", preg_quote("Complete"));

    // verify mailing name
    $this->verifyText("xpath=//table//tr[td[1]/text()='Mailing Name']/descendant::td[2]", preg_quote("Mailing $mailingName Webtest"));

    // verify mailing subject
    $this->verifyText("xpath=//table//tr[td[1]/text()='Subject']/descendant::td[2]", preg_quote("Test subject $mailingName for Webtest"));

    //---- check for delivery detail--

    $this->click("link=Successful Deliveries");
    $this->waitForPageToLoad("30000");

    // check for open page
    $this->assertTrue($this->isTextPresent("Successful Deliveries"));

    // verify email
    $this->assertTrue($this->isTextPresent("mailino$firstName@mailson.co.in"));

    require_once 'CRM/Mailing/Event/DAO/Queue.php';
    $eventQueue = new CRM_Mailing_Event_DAO_Queue();
    $eventQueue->contact_id = $contactId;
    $eventQueue->find(TRUE);

    $permission = array('edit-1-access-civimail-subscribeunsubscribe-pages');
    $this->changePermissions($permission);
    $this->open($this->sboxPath . "civicrm/logout?reset=1");

    // build forward url
    $forwardUrl = "civicrm/mailing/forward?reset=1&jid={$eventQueue->job_id}&qid={$eventQueue->id}&h={$eventQueue->hash}";
    $this->open($this->sboxPath . $forwardUrl);
    $this->waitForPageToLoad('30000');

    $this->type("email_0", substr(sha1(rand()), 0, 7) . '@example.com');
    $this->type("email_1", substr(sha1(rand()), 0, 7) . '@example.com');

    $this->click("comment_show");
    $this->type("forward_comment", "Test Message");

    $this->click("_qf_ForwardMailing_next-bottom");
    $this->waitForPageToLoad('30000');

    $this->assertTrue($this->isTextPresent('Mailing is forwarded successfully to 2 email addresses'));
    $this->open($this->sboxPath);
    $this->waitForPageToLoad('30000');
    $this->webtestLogin();

    $this->open($this->sboxPath . "civicrm/mailing/browse/scheduled?reset=1&scheduled=true");

    //click report link of created mailing
    $this->click("xpath=//table//tbody/tr[td[1]/text()='Mailing $mailingName Webtest']/descendant::a[text()='Report']");
    $this->waitForPageToLoad("30000");

    // verify successful forwards
    $this->verifyText("xpath=//table//tr[td/a[text()='Forwards']]/descendant::td[2]", "2");
    
    // Mailing is forwarded successfully to 2 email addresses.
    //------end delivery verification---------

    // //------ check with unsubscribe -------
    // // FIX ME: there is an issue with DSN setting for Webtest, need to handle by seperate DSN setting for Webtests
    // // build unsubscribe link
    // require_once 'CRM/Mailing/Event/DAO/Queue.php';
    // $eventQueue = new CRM_Mailing_Event_DAO_Queue( );
    // $eventQueue->contact_id = $contactId;
    // $eventQueue->find(true);

    // // unsubscribe link
    // $unsubscribeUrl = "civicrm/mailing/optout?reset=1&jid={$eventQueue->job_id}&qid={$eventQueue->id}&h={$eventQueue->hash}&confirm=1";

    // // logout to unsubscribe
    // $this->open($this->sboxPath . 'civicrm/logout?reset=1');
    // $this->waitForPageToLoad('30000');

    // // click(visit) unsubscribe path
    // $this->open($this->sboxPath . $unsubscribeUrl);
    // $this->waitForPageToLoad('30000');

    // $this->assertTrue($this->isTextPresent('Optout'));
    // $this->assertTrue($this->isTextPresent("mailino$firstName@mailson.co.in"));

    // // unsubscribe
    // $this->click('_qf_optout_next');
    // $this->waitForPageToLoad('30000');

    // $this->assertTrue($this->isTextPresent('Optout'));
    // $this->assertTrue($this->isTextPresent("mailino$firstName@mailson.co.in"));
    // $this->assertTrue($this->isTextPresent('has been successfully opted out.'));

    // //------ end unsubscribe -------
  }
  
  function testAdvanceSearchAndReportCheck() {

    $this->open($this->sboxPath);
    $this->webtestLogin();

    // Go directly to the URL of the screen that you will be testing (New Group).
    $this->open($this->sboxPath . "civicrm/group/add?reset=1");
    $this->waitForElementPresent("_qf_Edit_upload");

    // make group name
    $groupName = 'group_' . substr(sha1(rand()), 0, 7);

    // fill group name
    $this->type("title", $groupName);

    // fill description
    $this->type("description", "New mailing group for Webtest");

    // enable Mailing List
    $this->click("group_type[2]");

    // select Visibility as Public Pages
    $this->select("visibility", "value=Public Pages");

    // Clicking save.
    $this->click("_qf_Edit_upload");
    $this->waitForPageToLoad("30000");

    // Is status message correct?
    $this->assertTrue($this->isTextPresent("The Group '$groupName' has been saved."));

    //---- create mailing contact and add to mailing Group
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Mailson", "mailino$firstName@mailson.co.in");

    // Get contact id from url.
    $matches = array();
    preg_match('/cid=([0-9]+)/', $this->getLocation(), $matches);
    $contactId = $matches[1];

    // go to group tab and add to mailing group
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

    //-------select recipients----------

    // fill mailing name
    $mailingName = substr(sha1(rand()), 0, 7);
    $this->type("name", "Mailing $mailingName Webtest");

    // Add the test mailing group
    $this->select("includeGroups-f", "$groupName");
    $this->click("add");

    // click next
    $this->click("_qf_Group_next");
    $this->waitForElementPresent("_qf_Settings_cancel");

    //--------track and respond----------

    // check for default settings options
    $this->assertChecked("url_tracking");
    $this->assertChecked("open_tracking");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // click next with default settings
    $this->click("_qf_Settings_next");
    $this->waitForElementPresent("_qf_Upload_cancel");

    // fill subject for mailing
    $this->type("subject", "Test subject {$mailingName} for Webtest");
    
    // check for default option enabled
    $this->assertChecked("CIVICRM_QFID_1_4");

    // HTML format message
    $HTMLMessage = "This is HTML formatted content for Mailing {$mailingName} Webtest.";
    $this->fillRichTextField("html_message", $HTMLMessage);

    // Open Plain-text Format pane and type text format msg
    $this->click("//fieldset[@id='compose_id']/div[2]/div[1]");
    $this->type("text_message", "This is text formatted content for Mailing {$mailingName} Webtest.");

    // select default header and footer ( with label )
    $this->select("header_id", "label=Mailing Header");
    $this->select("footer_id", "label=Mailing Footer");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // click next with nominal content
    $this->click("_qf_Upload_upload");
    $this->waitForElementPresent("_qf_Test_cancel");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // click next
    $this->click("_qf_Test_next");
    $this->waitForElementPresent("_qf_Schedule_cancel");

    //----------Schedule or Send------------

    // do check for default option enabled
    $this->assertChecked("now");

    // do check count for Recipient
    $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

    // finally schedule the mail by clicking submit
    $this->click("_qf_Schedule_next");
    $this->waitForPageToLoad("30000");

    //----------end New Mailing-------------

    //check redirected page to Scheduled and Sent Mailings and  verify for mailing name
    $this->assertTrue($this->isTextPresent("Scheduled and Sent Mailings"));
    $this->assertTrue($this->isTextPresent("Mailing $mailingName Webtest"));

    // directly send schedule mailing -- not working right now
    $this->open($this->sboxPath . "civicrm/mailing/queue?reset=1");
    $this->waitForPageToLoad("300000");

    //click report link of created mailing
    $this->click("xpath=//table//tbody/tr[td[1]/text()='Mailing $mailingName Webtest']/descendant::a[text()='Report']");
    $this->waitForPageToLoad("30000");
   
    $mailingReportUrl = $this->getLocation();
    // do check again for recipient group
    $this->assertTrue($this->isTextPresent("Members of $groupName"));

    // check for 100% delivery
    $this->assertTrue($this->isTextPresent("1 (100.00%)"));

    $summaryInfoLinks = array('Intended Recipients', 'Successful Deliveries', 'Tracked Opens', 'Click-throughs', 'Forwards', 'Replies', 'Bounces', 'Unsubscribe Requests','Opt-out Requests');
    
    //check for report and adv search links
    foreach($summaryInfoLinks as $value) {
      $this->assertTrue($this->isElementPresent("xpath=//fieldset/legend[text()='Delivery Summary']/../table//tr[td/a[text()='{$value}']]/descendant::td[3]/span/a[1][text()='Report']"), "Report link missing for {$value}");
      $this->assertTrue($this->isElementPresent("xpath=//fieldset/legend[text()='Delivery Summary']/../table//tr[td/a[text()='{$value}']]/descendant::td[3]/span/a[2][text()='Advanced Search']"), "Advance Search link missing for {$value}");
    }
    // verify mailing name
    $this->verifyText("xpath=//table//tr[td[1]/text()='Mailing Name']/descendant::td[2]", preg_quote("Mailing $mailingName Webtest"));

    // verify mailing subject
    $this->verifyText("xpath=//table//tr[td[1]/text()='Subject']/descendant::td[2]", preg_quote("Test subject $mailingName for Webtest"));    
  
    // after asserts do clicks and confirm filters 
    $criteriaCheck = 
      array(
        'Intended Recipients' => 
        array(
          'report' => array('report_name' => 'Mailing Detail Report', 'Mailing' => "Mailing $mailingName Webtest"),
          'search' => array('Mailing Name IN' => "\"Mailing {$mailingName} Webtest")
        ),
        'Successful Deliveries' =>
        array(
          'report' => array('report_name' => 'Mailing Detail Report', 'Mailing' => "Mailing $mailingName Webtest",
                            "Delivery Status" => " Successful"),
          'search' => array('Mailing Name IN' => "\"Mailing {$mailingName} Webtest", 'Mailing Delivery -' => "Successful")
        ),
        'Tracked Opens' =>
        array(
          'report' => array('report_name' => 'Mailing Detail Report', 'Mailing' => "Mailing $mailingName Webtest"),
          'search' => array('Mailing Name IN' => "\"Mailing {$mailingName} Webtest", 'Mailing: Trackable Opens -' => "Opened")
        ),
        'Click-throughs' =>
        array(
          'report' => array('report_name' => 'Mail Clickthrough Report', 'Mailing' => "Mailing $mailingName Webtest"),
          'search' => array('Mailing Name IN' => "\"Mailing {$mailingName} Webtest", 'Mailing: Trackable URL Clicks -' => "Clicked")
        ),
        'Forwards' =>
        array(
          'report' => array('report_name' => 'Mailing Detail Report', 'Mailing' => "Mailing $mailingName Webtest",
                            'Forwarded' => 'Is equal to Yes'),
          'search' => array('Mailing Name IN' => "\"Mailing {$mailingName} Webtest", 'Mailing: -' => "Forwards")
        ),
        'Replies' =>
        array(
          'report' => array('report_name' => 'Mailing Detail Report', 'Mailing' => "Mailing $mailingName Webtest",
                            'Replied' => 'Is equal to Yes'),
          'search' => array('Mailing Name IN' => "\"Mailing {$mailingName} Webtest", 'Mailing: Trackable Replies -' => "Replied")
        ),
        'Bounces' =>
        array(
          'report' => array('report_name' => 'Mail Bounce Report', 'Mailing' => "Mailing $mailingName Webtest"),
          'search' => array('Mailing Name IN' => "\"Mailing {$mailingName} Webtest", 'Mailing Delivery -' => "Bounced")
        ),
        'Unsubscribe Requests' =>
        array(
          'report' => array('report_name' => 'Mailing Detail Report', 'Mailing' => "Mailing $mailingName Webtest",
                            'Unsubscribed' => 'Is equal to Yes'),
          'search' => array('Mailing Name IN' => "\"Mailing {$mailingName} Webtest", 'Mailing: -' => "Unsubscribe Requests")
        ),
        'Opt-out Requests' =>
        array(
          'report' => array('report_name' => 'Mailing Detail Report', 'Mailing' => "Mailing $mailingName Webtest",
                            'Opted-out' => 'Is equal to Yes'),
          'search' => array('Mailing Name IN' => "\"Mailing {$mailingName} Webtest", 'Mailing: -' => "Opt-out Requests")
        ),
      );
    $this->criteriaCheck($criteriaCheck, $mailingReportUrl);
  }
  
  function criteriaCheck($criteriaCheck, $mailingReportUrl) {
    foreach($criteriaCheck as $key => $infoFilter) {
      foreach($infoFilter as $entity => $dataToCheck) {
        $this->open($mailingReportUrl);
        if ($entity == "report") {
          $this->click("xpath=//fieldset/legend[text()='Delivery Summary']/../table//tr[td/a[text()='{$key}']]/descendant::td[3]/span/a[1][text()='Report']");
        } else {
          $this->click("xpath=//fieldset/legend[text()='Delivery Summary']/../table//tr[td/a[text()='{$key}']]/descendant::td[3]/span/a[2][text()='Advanced Search']");
        }
        $this->waitForPageToLoad("30000");
        $this-> _verifyCriteria($key, $dataToCheck, $entity);
      }
    }
  }
  
  function _verifyCriteria($summaryInfo, $dataToCheck, $entity) {
    foreach($dataToCheck as $key => $value) {
      if ($entity == 'report') {
        if ($key == 'report_name') {
          $this->assertTrue($this->isTextPresent("{$value}"));
          continue;
        }
        $this->assertTrue($this->isElementPresent("xpath=//form//div[3]/table/tbody//tr/th[contains(text(),'{$key}')]/../td[contains(text(),'{$value}')]"),"Criteria check for {$key} failed for Report for {$summaryInfo}");
      } else {
        $this->assertTrue($this->isTextPresent("Advanced Search"));
        $assertedValue = $this->isElementPresent("xpath=//div[@class='crm-results-block']//div[@class='qill'][contains(text(),'{$key} {$value}')]");
        if (!$assertedValue) {
          $assertedValue = $this->isTextPresent("{$key} {$value}");
        }
       $this->assertTrue($assertedValue,"Criteria check for {$key} failed for Advance Search for {$summaryInfo}");
      }
    }
  } 
}
