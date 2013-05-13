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
class WebTest_Profile_ProfileGroupSubscriptionTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testProfileGroupSubscription() {
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

    // Go directly to the URL of the screen that you will be
    // testing (Add new profile ).
    $this->open($this->sboxPath . 'civicrm/admin/uf/group?reset=1');

    $this->waitForPageToLoad('30000');

    $this->click('newCiviCRMProfile-top');

    $this->waitForElementPresent('_qf_Group_next-bottom');

    //Name of profile
    $profileTitle = 'profile_' . substr(sha1(rand()), 0, 7);
    $this->type('title', $profileTitle);

    //Drupal user account registration option
    $this->click('CIVICRM_QFID_0_8');

    //What to do upon duplicate match
    $this->click('CIVICRM_QFID_0_2');

    //Proximity search options
    $this->click('CIVICRM_QFID_0_14');

    // enable maping for contact
    $this->click('is_map');

    // include a link in the listings to Edit profile fields
    $this->click('is_edit_link');

    //to view contacts' Drupal user account information
    $this->click('is_uf_link');

    //click on save
    $this->click('_qf_Group_next');
    $this->waitForPageToLoad('30000');

    //check for  profile create
    $this->assertTrue($this->isTextPresent("Your CiviCRM Profile '$profileTitle' has been added. You can add fields to this profile now"));

    //Add email field to profile
    $this->click('field_name[0]');
    $this->select('field_name[0]', 'value=Contact');
    $this->click("//option[@value='Contact']");

    $this->select('field_name[1]', 'value=email');
    $this->click("//option[@value='email']");

    //click on save
    $this->click('_qf_Field_next_new-top');
    $this->waitForPageToLoad('30000');

    //Add email field to profile
    $this->click('field_name[0]');
    $this->select('field_name[0]', 'value=Contact');
    $this->click("//option[@value='Contact']");

    $this->select('field_name[1]', 'value=group');
    $this->click("//option[@value='group']");

    //click on save
    $this->click('_qf_Field_next');

    $this->waitForPageToLoad('30000');

    //now use profile create mode for group subscription
    $this->click("xpath=id('field_page')/div[1]/a[4]/span");

    $this->waitForElementPresent('email-Primary');

    //check for group field
    $this->assertTrue($this->isTextPresent('Group(s)'), "Groups field was not found.");

    //fill the subscription form
    $radomEmail = substr(sha1(rand()), 0, 7) . "@example.com";

    $this->type("email-Primary", $radomEmail);

    // check advisory group ( may be we should create a separate group to test this)
    $this->click("group[4]");

    $this->click('_qf_Edit_next');

    $this->waitForPageToLoad('30000');

    // assert for subscription message
    $this->assertTrue($this->isTextPresent("Your subscription request has been submitted for group "), "Subscription message is not shown");

    //check if profile is saved
    $this->assertTrue($this->isTextPresent("Your information has been saved."), "Profile is not saved");

    // delete the profile
    $this->open($this->sboxPath . 'civicrm/admin/uf/group?reset=1');
    $this->_testdeleteProfile($profileTitle);
  }

  function _testdeleteProfile($profileTitle) {
    //$this->click("xpath=//div[@id='user-profiles']/div/div/table/tbody//tr/td[1]/span[text() = '$profileTitle']/../following-sibling::td[4]/span[2][text()='more']");
    $this->waitForElementPresent("xpath=//div[@id='user-profiles']/div/div/table/tbody//tr/td[1]/span[text() = '$profileTitle']/../following-sibling::td[4]/span[2][text()='more']/ul/li[4]/a[text()='Delete']");
    $this->click("xpath=//div[@id='user-profiles']/div/div/table/tbody//tr/td[1]/span[text() = '$profileTitle']/../following-sibling::td[4]/span[2][text()='more']/ul/li[4]/a[text()='Delete']");
    $this->waitForElementPresent('_qf_Group_next-bottom');
    $this->click('_qf_Group_next-bottom');
    $this->waitForElementPresent('newCiviCRMProfile-bottom');
    $this->assertTrue($this->isTextPresent("Your CiviCRM Profile '$profileTitle' has been deleted."));
  }
}


