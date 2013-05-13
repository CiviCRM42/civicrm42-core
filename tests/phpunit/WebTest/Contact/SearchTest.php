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
class WebTest_Contact_SearchTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testQuickSearch() {
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

    // Adding contact
    // We're using Quick Add block on the main page for this.
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Anderson", "$firstName.anderson@example.org");

    $sortName = "Anderson, $firstName";
    $displayName = "$firstName Anderson";

    // Go directly to the URL of the screen that you will be testing (Home dashboard).
    $this->open($this->sboxPath . "civicrm/dashboard?reset=1");
    $this->waitForPageToLoad("30000");

    // type sortname in autocomplete
    $this->click("css=input#sort_name_navigation");
    $this->type("css=input#sort_name_navigation", $sortName);
    $this->typeKeys("css=input#sort_name_navigation", $sortName);

    // wait for result list
    $this->waitForElementPresent("css=div.ac_results-inner li");

    // visit contact summary page
    $this->click("css=div.ac_results-inner li");
    $this->waitForPageToLoad("30000");

    // Is contact present?
    $this->assertTrue($this->isTextPresent("$displayName"), "Contact did not find!");
  }

  function testQuickSearchPartial() {
    $this->open($this->sboxPath);

    $this->webtestLogin();

    // Adding contact
    // We're using Quick Add block on the main page for this.
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Adams", "{$firstName}.adams@example.org");

    $sortName = "Adams, {$firstName}";
    // Go directly to the URL of the screen that you will be testing (Home dashboard).
    $this->open($this->sboxPath . "civicrm/dashboard?reset=1");
    $this->waitForPageToLoad("30000");

    // type partial sortname in autocomplete
    $this->click("css=input#sort_name_navigation");
    $this->type("css=input#sort_name_navigation", 'ada');
    $this->typeKeys("css=input#sort_name_navigation", 'ada');
    
    $this->click("_qf_Basic_refresh");

    // wait for result list
    $this->waitForPageToLoad("30000");
    // make sure we're on search results page
    $this->waitForElementPresent("alpha-filter");
    // wait for bottom of page to load (access is in footer)
    $this->waitForElementPresent("access");

    // Is contact present in search result?
    $this->assertTrue($this->isTextPresent("$sortName"), "Contact not found in search result (QuickSearchPartial).");
  }

  function testContactSearch() {
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

    // Create new tag.
    $tagName = 'tag_' . substr(sha1(rand()), 0, 7);
    $this->addTag($tagName);

    // Create new group
    $groupName = 'group_' . substr(sha1(rand()), 0, 7);
    $this->addGroup($groupName);

    // Adding contact
    // We're using Quick Add block on the main page for this.
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Smith", "$firstName.smith@example.org");

    $sortName = "Smith, $firstName";
    $displayName = "$firstName Smith";

    // add contact to group
    // visit group tab
    $this->click("css=li#tab_group a");
    $this->waitForElementPresent("group_id");

    // add to group
    $this->select("group_id", "label=$groupName");
    $this->click("_qf_GroupContact_next");
    $this->waitForPageToLoad("30000");

    // tag a contact
    // visit tag tab
    $this->click("css=li#tab_tag a");
    $this->waitForElementPresent("css=div#tagtree");

    // select tag
    $this->click("xpath=//ul/li/label[text()=\"$tagName\"]");
    $this->waitForElementPresent("css=.msgok");

    // visit contact search page
    $this->open($this->sboxPath . "civicrm/contact/search?reset=1");
    $this->waitForPageToLoad("30000");

    // fill name as first_name
    $this->type("css=.crm-basic-criteria-form-block input#sort_name", $firstName);

    // select contact type as Indiividual
    $this->select("contact_type", "value=Individual");

    // select group
    $this->select("group", "label=$groupName");

    // select tag
    $this->select("tag", "label=$tagName");

    // click to search
    $this->click("_qf_Basic_refresh");
    $this->waitForPageToLoad("30000");

    // Is contact present in search result?
    $this->assertTrue($this->isTextPresent("$sortName"), "Contact did not found in search result!");
  }

  function addTag($tagName = 'New Tag') {

    $this->open($this->sboxPath . "civicrm/admin/tag?action=add&reset=1");

    // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
    // button at the end of this page to show up, to make sure it's fully loaded.
    $this->waitForElementPresent("_qf_Tag_next");

    // fill tag name
    $this->type("name", $tagName);

    // fill description
    $this->type("description", "Adding new tag.");

    // select used for contact
    $this->select("used_for", "value=civicrm_contact");

    // check reserved
    $this->click("is_reserved");

    // Clicking save.
    $this->click("_qf_Tag_next");
    $this->waitForPageToLoad("30000");

    // Is status message correct?
    $this->assertTrue($this->isTextPresent("The tag '$tagName' has been saved."));
  }

  function addGroup($groupName = 'New Group', $parentGroupName = "- select -") {
    $this->open($this->sboxPath . "civicrm/group/add?reset=1");

    // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
    // button at the end of this page to show up, to make sure it's fully loaded.
    $this->waitForElementPresent("_qf_Edit_upload");

    // fill group name
    $this->type("title", $groupName);

    // fill description
    $this->type("description", "Adding new group.");

    // check Access Control
    $this->click("group_type[1]");

    // check Mailing List
    $this->click("group_type[2]");

    // select Visibility as Public Pages
    $this->select("visibility", "value=Public Pages");

    // select parent group
    $this->select("parents", "label=$parentGroupName");

    // Clicking save.
    $this->click("_qf_Edit_upload");
    $this->waitForPageToLoad("30000");

    // Is status message correct?
    $this->assertTrue($this->isTextPresent("The Group '$groupName' has been saved."));
  }

  // CRM-6586
  function testContactSearchExport() {
    $this->open($this->sboxPath);

    // Logging in. Remember to wait for page to load. In most cases,
    // you can rely on 30000 as the value that allows your test to pass, however,
    // sometimes your test might fail because of this. In such cases, it's better to pick one element
    // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
    // page contents loaded and you can continue your test execution.
    $this->webtestLogin();

    // Create new  group
    $parentGroupName = 'Parentgroup_' . substr(sha1(rand()), 0, 7);
    $this->addGroup($parentGroupName);

    // Create new group and select the previously selected group as parent group for this new group.
    $childGroupName = 'Childgroup_' . substr(sha1(rand()), 0, 7);
    $this->addGroup($childGroupName, $parentGroupName);


    // Adding Parent group contact
    // We're using Quick Add block on the main page for this.
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Smith", "$firstName.smith@example.org");

    $sortName = "Smith, $firstName";
    $displayName = "$firstName Smith";

    // add contact to parent  group
    // visit group tab
    $this->click("css=li#tab_group a");
    $this->waitForElementPresent("group_id");

    // add to group
    $this->select("group_id", "label=$parentGroupName");
    $this->click("_qf_GroupContact_next");
    $this->waitForPageToLoad("30000");

    // Adding child group contact
    // We're using Quick Add block on the main page for this.
    $childName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($childName, "John", "$childName.john@example.org");

    $childSortName = "John, $childName";
    $childDisplayName = "$childName John";

    // add contact to child group
    // visit group tab
    $this->click("css=li#tab_group a");
    $this->waitForElementPresent("group_id");

    // add to child group
    $this->select("group_id", "label=$childGroupName");
    $this->click("_qf_GroupContact_next");
    $this->waitForPageToLoad("30000");


    // visit contact search page
    $this->open($this->sboxPath . "civicrm/contact/search?reset=1");
    $this->waitForPageToLoad("30000");


    // select contact type as Indiividual
    $this->select("contact_type", "value=Individual");

    // select group
    $this->select("group", "label=$parentGroupName");

    // click to search
    $this->click("_qf_Basic_refresh");
    $this->waitForPageToLoad("30000");

    // Is contact present in search result?
    $this->assertTrue($this->isTextPresent("$sortName"), "Contact did not found in search result!");

    // Is contact present in search result?
    $this->assertTrue($this->isTextPresent("$childSortName"), "Contact did not found in search result!");

    // select to export all the contasct from search result
    $this->click("CIVICRM_QFID_ts_all_4");

    // Select the task action to export
    $this->click("task");
    $this->select("task", "label=Export Contacts");
    $this->click("Go");
    $this->waitForPageToLoad("30000");

    $this->click("_qf_Select_next-bottom");
  }
}


