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
class WebTest_Member_UpdateMembershipScriptTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testAddMembership() {
    // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    // Add a new membership type
    $memTypeParams = $this->addMembershipType();

    $firstName = substr(sha1(rand()), 0, 7);
    $email = "$firstName.Anderson@example.com";
    $this->webtestAddContact($firstName, 'Anderson', $email);

    $this->waitForElementPresent('css=li#tab_member a');
    $this->click('css=li#tab_member a');
    $this->waitForElementPresent('link=Add Membership');
    $this->click('link=Add Membership');

    $this->waitForElementPresent('_qf_Membership_cancel-bottom');
    $this->select('membership_type_id[0]', "label={$memTypeParams['member_org']}");
    $this->select('membership_type_id[1]', "label={$memTypeParams['membership_type']}");

    // Fill join date
    $this->webtestFillDate('join_date', "1 March 2008");

    // Override status
    $this->check('is_override');
    $this->select('status_id', "label=Current");

    // Clicking save.
    $this->click('_qf_Membership_upload');
    $this->waitForPageToLoad("30000");

    // Is status message correct?
    $this->assertTrue($this->isTextPresent("{$memTypeParams['membership_type']} membership for $firstName Anderson has been added."),
      "Status message didn't show up after saving!"
    );

    // click through to the membership view screen
    $this->waitForElementPresent("xpath=//div[@id='memberships']//table//tbody/tr[1]/td[8]");
    $this->click("xpath=//div[@id='memberships']//table//tbody/tr[1]/td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");

    $this->webtestVerifyTabularData(
      array(
        'Membership Type' => "{$memTypeParams['membership_type']}",
        'Status' => 'Current',
        'Member Since' => 'March 1st, 2008',
        'Start date' => 'March 1st, 2008',
        'End date' => 'February 28th, 2009',
      )
    );
  }

  function addMembershipType() {
    $membershipTitle = substr(sha1(rand()), 0, 7);
    $membershipOrg = $membershipTitle . ' memorg';
    $this->webtestAddOrganization($membershipOrg, TRUE);

    $title = "Membership Type " . substr(sha1(rand()), 0, 7);
    $memTypeParams = array(
      'membership_type' => $title,
      'member_org' => $membershipOrg,
      'contribution_type' => 2,
      'relationship_type' => '4_b_a',
    );

    $this->open($this->sboxPath . "civicrm/admin/member/membershipType?reset=1&action=browse");
    $this->waitForPageToLoad("30000");

    $this->click("link=Add Membership Type");
    $this->waitForElementPresent('_qf_MembershipType_cancel-bottom');

    // New membership type
    $this->type('name', $memTypeParams['membership_type']);
    $this->type('member_org', $membershipTitle);
    $this->click('_qf_MembershipType_refresh');
    $this->waitForElementPresent("xpath=//div[@id='membership_type_form']/fieldset/table[2]/tbody/tr[2]/td[2]");

    // Membership fees
    $this->type('minimum_fee', '100');
    $this->select('contribution_type_id', "value={$memTypeParams['contribution_type']}");

    // Duration for which the membership will be active
    $this->type('duration_interval', 1);
    $this->select('duration_unit', "label=year");

    // Membership period type
    $this->select('period_type', "label=rolling");
    $this->click('relationship_type_id', "value={$memTypeParams['relationship_type']}");

    // Clicking save
    $this->click('_qf_MembershipType_upload-bottom');
    $this->waitForElementPresent('link=Add Membership Type');
    $this->assertTrue($this->isTextPresent("The membership type '$title' has been saved."));

    return $memTypeParams;
  }
}

