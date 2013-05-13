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
class WebTest_Contact_TaskActionAddToGroupTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testAddContactsToGroup() {

    // Create a new group with a random name; included test provides login
    include_once ('WebTest/Contact/GroupAddTest.php');
    $newGroupName = 'Group_' . substr(sha1(rand()), 0, 7);
    WebTest_Contact_GroupAddTest::testGroupAdd(array('name' => $newGroupName));

    // Create two new contacts with a common random string in email address
    include_once ('WebTest/Contact/AddTest.php');
    $emailString = substr(sha1(rand()), 0, 7) . '@example.com_';
    $cids = array();
    for ($i = 0; $i < 2; $i++) {
      // logout before calling included test, to avoid impossible repeated login
      $this->open($this->sboxPath . "civicrm/logout?reset=1");

      // create new contact
      WebTest_Contact_AddTest::testIndividualAdd();

      // get cid of new contact
      $queryParams = $this->parseURL();
      $cids[] = $queryParams['queryString']['cid'];

      // update email of new contact
      $this->click("//ul[@id='actions']/li/a/span[text()='Edit']");
      $this->waitForPageToLoad("30000");
      $this->type("email_1_email", $emailString . $i);
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");
    }

    // Search for those two contacts
    $this->click("//ul[@id='civicrm-menu']/li[3]");
    $this->click("//div[@id='root-menu-div']/div[2]/ul/li[2]/div/a");

    // Use class names for menu items since li array can change based on which components are enabled
    $this->click("css=ul#civicrm-menu li.crm-Search");
    $this->click("css=ul#civicrm-menu li.crm-Advanced_Search a");

    $this->waitForPageToLoad("30000");
    $this->waitForElementPresent("email");
    $this->type("email", $emailString);
    $this->click("_qf_Advanced_refresh");
    $this->waitForPageToLoad("30000");

    // Verify exactly two contacts found
    $this->assertTrue($this->isTextPresent("2 Contacts"), 'Looking for 2 results with email like ' . $emailString);

    // Click "check all" box and act on "Add to group" action
    $this->click('toggleSelect');
    $this->select("task", "label=Add Contacts to Group");
    $this->click("Go");
    $this->waitForPageToLoad("30000");

    // Select the new group and click to add
    $this->click("group_id");
    $this->select("group_id", "label=" . $newGroupName);
    $this->click("_qf_AddToGroup_next-bottom");
    $this->waitForPageToLoad("30000");

    // Check status messages are as expected
    $this->assertTrue($this->isTextPresent("Added Contact(s) to " . $newGroupName));
    $this->assertTrue($this->isTextPresent("Total Selected Contact(s): 2"));
    $this->assertTrue($this->isTextPresent("Total Contact(s) added to group: 2"));

    // Search by group membership in newly created group
    // Use class names for menu items since li array can change based on which components are enabled
    $this->click("css=ul#civicrm-menu li.crm-Search");
    $this->click("css=ul#civicrm-menu li.crm-Advanced_Search a");
    $this->waitForPageToLoad("30000");
    $this->select("crmasmSelect1", "label=" . $newGroupName);
    $this->click("_qf_Advanced_refresh");
    $this->waitForPageToLoad("30000");

    // Verify those two contacts (and only those two) are in the group
    if (!$this->isTextPresent("2 Contacts")) {
      die("nothing found for group $newGroupName");
    }

    $this->assertTrue($this->isTextPresent("2 Contacts"), 'Looking for 2 results belonging to group: ' . $newGroupName);
    foreach ($cids as $cid) {
      $this->assertTrue($this->isElementPresent('rowid' . $cid));
    }
  
  }

  function testMultiplePageContactSearchAddContactsToGroup() {
    include_once ('WebTest/Contact/GroupAddTest.php');
    $newGroupName = 'Group_' . substr(sha1(rand()), 0, 7);
    WebTest_Contact_GroupAddTest::testGroupAdd(array('name' => $newGroupName));

    $this->open($this->sboxPath . 'civicrm/contact/search?reset=1');
    $this->click("css=ul#civicrm-menu li.crm-Search");
    $this->click("css=ul#civicrm-menu li.crm-Find_Contacts a");
    $this->waitForPageToLoad("30000");
    $this->click("_qf_Basic_refresh");
    $this->waitForPageToLoad("30000");
    
    $this->click("xpath=//div[@class='form-item float-right']/a[text()='25']");
    $this->waitForPageToLoad("30000");
    $this->click("toggleSelect");
    $this->click("xpath=//div[@class='crm-content-block']/div/div[2]/div/span[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("toggleSelect");
    $this->select("task", "label=Add Contacts to Group");
    $this->click("Go");
    $this->waitForPageToLoad("30000");
    
     // Select the new group and click to add
    $this->click("group_id");
    $this->select("group_id", "label=" . $newGroupName);
    $this->click("_qf_AddToGroup_next-bottom");
    $this->waitForPageToLoad("30000");

    // Check status messages are as expected
    $this->assertTrue($this->isTextPresent("Added Contact(s) to " . $newGroupName));
    $this->assertTrue($this->isTextPresent("Total Selected Contact(s): 50"));
    $this->assertTrue($this->isTextPresent("Total Contact(s) added to group: 50"));

    $this->click("css=ul#civicrm-menu li.crm-Search");
    $this->click("css=ul#civicrm-menu li.crm-Advanced_Search a");
    $this->waitForPageToLoad("30000");
    $this->select("crmasmSelect1", "label=" . $newGroupName);
    $this->click("_qf_Advanced_refresh");
    $this->waitForPageToLoad("30000");
    
    if (!$this->isTextPresent("50 Contacts")) {
       die("nothing found for group $newGroupName");
    }
    
    $this->assertTrue($this->isTextPresent("50 Contacts"), 'Looking for 50 results belonging to group: ' . $newGroupName);
  }
}


