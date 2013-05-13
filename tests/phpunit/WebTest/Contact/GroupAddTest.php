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
class WebTest_Contact_GroupAddTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testGroupAdd($params = array(
    )) {
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

    // create a new group with given parameters

    // Go directly to the URL of the screen that you will be testing (New Group).
    $this->open($this->sboxPath . "civicrm/group/add?&reset=1");

    // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
    // button at the end of this page to show up, to make sure it's fully loaded.
    $this->waitForElementPresent("_qf_Edit_upload");

    // take group name
    if (empty($params['name'])) {
      $params['name'] = 'group_' . substr(sha1(rand()), 0, 7);
    }

    // fill group name
    $this->type("title", $params['name']);

    // fill description
    $this->type("description", "Adding new group.");

    // check Access Control
    if (isset($params['type1']) && $params['type1'] !== FALSE) {
      $this->click("group_type[1]");
    }

    // check Mailing List
    if (isset($params['type2']) && $params['type2'] !== FALSE) {
      $this->click("group_type[2]");
    }

    // select Visibility as Public Pages
    if (empty($params['visibility'])) {
      $params['visibility'] = 'Public Pages';
    }

    $this->select("visibility", "value={$params['visibility']}");

    // Clicking save.
    $this->click("_qf_Edit_upload");
    $this->waitForPageToLoad("30000");

    // Is status message correct?
    $this->assertTrue($this->isTextPresent("The Group '{$params['name']}' has been saved."));
  }
  function testGroupReserved($params = array(
    )) {
    // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);

    $this->webtestLogin(true);

    // create a new group with given parameters

    // Go directly to the URL of the screen that you will be testing (New Group).
    $this->open($this->sboxPath . "civicrm/group/add?&reset=1");

    // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
    // button at the end of this page to show up, to make sure it's fully loaded.
    $this->waitForElementPresent("_qf_Edit_upload");

    // take group name
    if (empty($params['name'])) {
      $params['name'] = 'reserved_group_' . substr(sha1(rand()), 0, 7);
    }

    // fill group name
    $this->type("title", $params['name']);

    // fill description
    $this->type("description", "Adding new reserved group.");

    // check Access Control
    if (isset($params['type1']) && $params['type1'] !== FALSE) {
      $this->click("group_type[1]");
    }

    // check Mailing List
    if (isset($params['type2']) && $params['type2'] !== FALSE) {
      $this->click("group_type[2]");
    }

    // select Visibility as Public Pages
    if (empty($params['visibility'])) {
      $params['visibility'] = 'Public Pages';
    }

    $this->select("visibility", "value={$params['visibility']}");

    // Check Reserved box
    $this->click("is_reserved");
    
    // Clicking save.
    $this->click("_qf_Edit_upload");
    $this->waitForPageToLoad("30000");

    // Is status message correct?
    $this->assertTrue($this->isTextPresent("The Group '{$params['name']}' has been saved."));
    
    // Create a new role w/o reserved group permissions
    $role = 'role' . substr(sha1(rand()), 0, 7);
    $this->open($this->sboxPath . "admin/people/permissions/roles");

    $this->waitForElementPresent("edit-add");
    $this->type("edit-name", $role);
    $this->click("edit-add");
    $this->waitForPageToLoad("30000");
    
    $this->open($this->sboxPath . "admin/people/permissions/roles");
    $this->waitForElementPresent("xpath=//table[@id='user-roles']/tbody//tr/td[1][text()='{$role}']");
    $roleId = explode('/', $this->getAttribute("xpath=//table[@id='user-roles']/tbody//tr/td[1][text()='{$role}']/../td[4]/a[text()='edit permissions']/@href"));
    $roleId = end($roleId);
    $user = $this->_testCreateUser($roleId);
    $permissions = array(
      "edit-{$roleId}-view-all-contacts",
      "edit-{$roleId}-access-civicrm",
    );
    $this->changePermissions($permissions);
    
    // Now logout as admin, login as regular user and verify that Group settings, delete and disable links are not available
    $this->open($this->sboxPath . "civicrm/logout?reset=1");
    $this->open($this->sboxPath);
    $this->waitForElementPresent('edit-submit');
    $this->type('edit-name', $user);
    $this->type('edit-pass', 'Test12345');
    $this->click('edit-submit');
    $this->waitForPageToLoad('30000');
    
    $this->open($this->sboxPath . "civicrm/group?&reset=1");
    $this->type('title', $params['name']);
    $this->click('_qf_Search_refresh');
    $this->waitForTextPresent("Adding new reserved group.");
    // Settings link should NOT be included in selector after search returns with only the reserved group.
    $this->assertFalse($this->isTextPresent("Settings"));

    //login as admin and delete the role
    $this->open($this->sboxPath . "civicrm/logout?reset=1");
    $this->open($this->sboxPath);
    $this->webtestLogin(TRUE);
    $this->open($this->sboxPath . "admin/people/permissions/roles");
    $this->_roleDelete($role);

  }

  function _testCreateUser($roleid) {
    // Go directly to the URL of the screen that will Create User Authentically.
    $this->open($this->sboxPath . "admin/people/create");

    $this->waitForElementPresent("edit-submit");

    $name = "TestUser" . substr(sha1(rand()), 0, 4);
    $this->type("edit-name", $name);

    $emailId = substr(sha1(rand()), 0, 7) . '@web.com';
    $this->type("edit-mail", $emailId);
    $this->type("edit-pass-pass1", "Test12345");
    $this->type("edit-pass-pass2", "Test12345");
    $role = "edit-roles-" . $roleid;
    $this->check("name=roles[$roleid] value={$roleid}");

    //Add profile Details
    $firstName = 'Ma' . substr(sha1(rand()), 0, 4);
    $lastName = 'An' . substr(sha1(rand()), 0, 7);

    $this->type("first_name", $firstName);
    $this->type("last_name", $lastName);

    //Address Details
    $this->type("street_address-1", "902C El Camino Way SW");
    $this->type("city-1", "Dumfries");
    $this->type("postal_code-1", "1234");
    $this->select("state_province-1", "value=1019");

    $this->click("edit-submit");
    $this->waitForPageToLoad("30000");
    return $name;
  }
  
  function _roleDelete($role) {
    $this->waitForElementPresent("xpath=//table[@id='user-roles']/tbody//tr/td[text()='{$role}']/..//td/a[text()='edit role']");
    $this->click("xpath=//table[@id='user-roles']/tbody//tr/td[text()='{$role}']/..//td/a[text()='edit role']");
    $this->waitForElementPresent('edit-delete');
    $this->click('edit-delete');
    $this->waitForPageToLoad('30000');
    $this->click("edit-submit");
    $this->waitForTextPresent("The role has been deleted.");
  }
  
}


