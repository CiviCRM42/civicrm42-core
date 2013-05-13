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
class WebTest_Member_OfflineMembershipAddPricesetTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testAddPriceSet() {
    // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    $title            = substr(sha1(rand()), 0, 7);
    $setTitle         = "Membership Fees - $title";
    $usedFor          = 'Membership';
    $contributionType = 'Donation';
    $setHelp          = 'Select your membership options.';
    $this->_testAddSet($setTitle, $usedFor, $contributionType, $setHelp);

    // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
    // which is where we are after adding Price Set.
    $elements = $this->parseURL();
    $sid = $elements['queryString']['sid'];
    $this->assertType('numeric', $sid);

    $fields = array(
      "National Membership $title" => 'Radio',
      "Local Chapter $title" => 'CheckBox',
    );

    list($memTypeTitle1, $memTypeTitle2) = $this->_testAddPriceFields($fields, $validateStrings, FALSE, $title, $sid);
    //var_dump($validateStrings);

    // load the Price Set Preview and check for expected values
    $this->_testVerifyPriceSet($validateStrings, $sid);

    // Sign up for membership
    $firstName     = 'John_' . substr(sha1(rand()), 0, 7);
    $lastName      = 'Anderson_' . substr(sha1(rand()), 0, 7);
    $email         = "{$firstName}.{$lastName}@example.com";
    $contactParams = array(
      'first_name' => $firstName,
      'last_name' => $lastName,
      'email-5' => $email,
    );

    // Add a contact from the quick add block
    $this->webtestAddContact($firstName, $lastName, $email);

    $this->_testSignUpOrRenewMembership($sid, $contactParams, $memTypeTitle1, $memTypeTitle2);

    // Renew this membership
    $this->_testSignUpOrRenewMembership($sid, $contactParams, $memTypeTitle1, $memTypeTitle2, $renew = TRUE);
  }

  function _testAddSet($setTitle, $usedFor, $contributionType = NULL, $setHelp) {
    $this->open($this->sboxPath . 'civicrm/admin/price?reset=1&action=add');
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent('_qf_Set_next-bottom');

    // Enter Priceset fields (Title, Used For ...)
    $this->type('title', $setTitle);
    if ($usedFor == 'Event') {
      $this->check('extends[1]');
    }
    elseif ($usedFor == 'Contribution') {
      $this->check('extends[2]');
    }
    elseif ($usedFor == 'Membership') {
      $this->click('extends[3]');
      $this->waitForElementPresent('contribution_type_id');
      $this->select("css=select.form-select", "label={$contributionType}");
    }

    $this->type('help_pre', $setHelp);

    $this->assertChecked('is_active', 'Verify that Is Active checkbox is set.');
    $this->click('_qf_Set_next-bottom');

    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent('_qf_Field_next-bottom');
    $this->assertTrue($this->isTextPresent("Your Set '{$setTitle}' has been added. You can add fields to this set now."));
  }

  function _testAddPriceFields(&$fields, &$validateString, $dateSpecificFields = FALSE, $title, $sid) {
    $memTypeParams1 = $this->webtestAddMembershipType();
    $memTypeTitle1  = $memTypeParams1['membership_type'];

    $memTypeId1     = explode('&id=', $this->getAttribute("xpath=//div[@id='membership_type']/div[2]/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[11]/span/a[3]@href"));
    $memTypeId1     = $memTypeId1[1];

    $memTypeParams2 = $this->webtestAddMembershipType();
    $memTypeTitle2  = $memTypeParams2['membership_type'];
    $memTypeId2     = explode('&id=', $this->getAttribute("xpath=//div[@id='membership_type']/div[2]/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[11]/span/a[3]@href"));
    $memTypeId2     = $memTypeId2[1];

    $this->open($this->sboxPath . "civicrm/admin/price/field?reset=1&action=add&sid={$sid}");

    foreach ($fields as $label => $type) {
      $validateStrings[] = $label;

      $this->type('label', $label);
      $this->select('html_type', "value={$type}");

      switch ($type) {
        case 'Radio':
          $options = array(
            1 => array('label' => "$memTypeTitle1",
              'membership_type_id' => $memTypeId1,
              'amount' => 100.00,
            ),
            2 => array(
              'label' => "$memTypeTitle2",
              'membership_type_id' => $memTypeId2,
              'amount' => 50.00,
            ),
          );
          $this->addMultipleChoiceOptions($options, $validateStrings);
          break;

        case 'CheckBox':
          $options = array(
            1 => array('label' => "$memTypeTitle1",
              'membership_type_id' => $memTypeId1,
              'amount' => 100.00,
            ),
            2 => array(
              'label' => "$memTypeTitle2",
              'membership_type_id' => $memTypeId2,
              'amount' => 50.00,
            ),
          );
          $this->addMultipleChoiceOptions($options, $validateStrings);
          break;

        default:
          break;
      }
      $this->click('_qf_Field_next_new-bottom');
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_Field_next-bottom');
      $this->assertTrue($this->isTextPresent("Price Field '{$label}' has been saved."));
    }
    return array($memTypeTitle1, $memTypeTitle2);
  }

  function _testVerifyPriceSet($validateStrings, $sid) {
    // verify Price Set at Preview page
    // start at Manage Price Sets listing
    $this->open($this->sboxPath . 'civicrm/admin/price?reset=1');
    $this->waitForPageToLoad('30000');

    // Use the price set id ($sid) to pick the correct row
    $this->click("css=tr#row_{$sid} a[title='Preview Price Set']");

    $this->waitForPageToLoad('30000');
    // Look for Register button
    $this->waitForElementPresent('_qf_Preview_cancel-bottom');

    // Check for expected price set field strings
    $this->assertStringsPresent($validateStrings);
  }

  function _testSignUpOrRenewMembership($sid, $contactParams, $memTypeTitle1, $memTypeTitle2, $renew = FALSE) {
    //build the membership dates.
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Utils/Date.php';
    $currentYear  = date('Y');
    $currentMonth = date('m');
    $previousDay  = date('d') - 1;
    $endYear      = ($renew) ? $currentYear + 2 : $currentYear + 1;
    $joinDate     = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $startDate    = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $endDate      = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $previousDay, $endYear));
    $configVars   = new CRM_Core_Config_Variables();
    foreach (array(
      'joinDate', 'startDate', 'endDate') as $date) {
      $$date = CRM_Utils_Date::customFormat($$date, $configVars->dateformatFull);
    }

    if (!$renew) {
      // Go directly to the URL of the screen that you will be testing (Activity Tab).
      $this->click('css=li#tab_member a');
      $this->waitForElementPresent('link=Add Membership');

      $this->click('link=Add Membership');
      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      $this->select('price_set_id', "value={$sid}");
      $this->waitForElementPresent('pricesetTotal');

      $this->click("xpath=//div[@id='priceset']/div[2]/div[2]/div/span/input");
      $this->click("xpath=//div[@id='priceset']/div[3]/div[2]/div[2]/span/input");

      $this->type('source', 'Offline membership Sign Up Test Text');
      $this->click('_qf_Membership_upload-bottom');
    }
    else {
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');

      $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');
    }
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
    //View Membership Record
    $verifyData = array(
      'Membership Type' => "{$memTypeTitle1}",
      'Status' => 'New',
      'Member Since' => $joinDate,
      'Start date' => $startDate,
      'End date' => $endDate,
    );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
        preg_quote($value)
      );
    }

    $this->click('_qf_MembershipView_cancel-bottom');
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");

    //View Membership Record
    $verifyData = array(
      'Membership Type' => "{$memTypeTitle2}",
      'Status' => 'New',
      'Member Since' => $joinDate,
      'Start date' => $startDate,
      'End date' => $endDate,
    );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
        preg_quote($value)
      );
    }
    $this->click("_qf_MembershipView_cancel-bottom");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
  }
}

