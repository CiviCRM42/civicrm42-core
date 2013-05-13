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
class WebTest_Contact_PrevNextTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testPrevNext() {
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


    /* add new group */

    $this->open($this->sboxPath . "civicrm/group/add?&reset=1");
    $this->waitForElementPresent("_qf_Edit_upload");

    $groupName = 'group_' . substr(sha1(rand()), 0, 7);
    $this->type("title", $groupName);

    // fill description
    $this->type("description", "Adding new group.");

    // check Access Control
    $this->click("group_type[1]");

    // Clicking save.
    $this->click("_qf_Edit_upload");
    $this->waitForPageToLoad("30000");

    /* add contacts */

    // Individual 1
    $contact1 = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($contact1, "AAA", "{$contact1}@example.com");

    // Add Individual 1 to group
    $this->click('css=li#tab_group a');
    $this->waitForElementPresent('_qf_GroupContact_next');
    $this->select('group_id', "label={$groupName}");
    $this->click('_qf_GroupContact_next');
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Contact has been added to the selected group "));

    // Individual 2
    $contact2 = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($contact2, "BBB", "{$contact2}@example.com");

    // Add Individual 2 to group
    $this->click('css=li#tab_group a');
    $this->waitForElementPresent('_qf_GroupContact_next');
    $this->select('group_id', "label={$groupName}");
    $this->click('_qf_GroupContact_next');
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Contact has been added to the selected group "));

    // Individual 3
    $contact3 = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($contact3, "CCC", "{$contact3}@example.com");

    // Add Individual 3 to group
    $this->click('css=li#tab_group a');
    $this->waitForElementPresent('_qf_GroupContact_next');
    $this->select('group_id', "label={$groupName}");
    $this->click('_qf_GroupContact_next');
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Contact has been added to the selected group "));

    // Search contacts
    $this->open($this->sboxPath . "civicrm/contact/search?reset=1");
    $this->waitForElementPresent("_qf_Basic_refresh");

    $this->select('group', "label={$groupName}");
    $this->click("_qf_Basic_refresh");
    $this->waitForPageToLoad("60000");
    $this->assertTrue($this->isTextPresent("3 Contacts"));

    $this->click("xpath=//div[@class='crm-search-results']//table/tbody/tr[1]/td[3]/a");
    $this->waitForPageToLoad("30000");

    $this->assertTrue($this->isTextPresent("{$contact1} AAA"));
    $this->assertTrue($this->isTextPresent("Next"));

    $this->click("xpath=//ul[@id='actions']/li[@class='crm-next-action']/a");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("{$contact2} BBB"));
    $this->assertTrue($this->isTextPresent("Next"));
    $this->assertTrue($this->isTextPresent("Previous"));

    $this->click("xpath=//ul[@id='actions']/li[@class='crm-next-action']/a");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("{$contact3} CCC"));
    $this->assertTrue($this->isTextPresent("Previous"));

    $this->click("xpath=//ul[@id='actions']/li[@class='crm-previous-action']/a");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("{$contact2} BBB"));
    $this->assertTrue($this->isTextPresent("Next"));
    $this->assertTrue($this->isTextPresent("Previous"));

    $this->click("xpath=//ul[@id='actions']/li[@class='crm-previous-action']/a");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("{$contact1} AAA"));
    $this->assertTrue($this->isTextPresent("Next"));
  }
}

