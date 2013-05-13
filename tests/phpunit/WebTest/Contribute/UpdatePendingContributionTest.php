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
class WebTest_Contribute_UpdatePendingContributionTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testUpdatePendingContribution() {
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
    $firstName = substr(sha1(rand()), 0, 7);
    $lastName  = 'Contributor';
    $email     = $firstName . "@example.com";

    //Offline Pay Later Contribution
    $this->_testOfflineContribution($firstName, $lastName, $email);

    //Online Pay Later Contribution
    $this->_testOnlineContribution($firstName, $lastName, $email);
    $this->open($this->sboxPath . "civicrm/contribute/search?reset=1");

    $this->waitForElementPresent("contribution_date_low");

    $this->type("sort_name", "$lastName, $firstName");
    $this->click("_qf_Search_refresh");

    $this->waitForPageToLoad('30000');
    $this->click('radio_ts', 'ts_all');
    $contriIDOff = explode('&', $this->getAttribute("xpath=//div[@id='contributionSearch']/table/tbody/tr[1]/td[11]/span/a@href"));
    $contriIDOn = explode('&', $this->getAttribute("xpath=//div[@id='contributionSearch']/table/tbody/tr[2]/td[11]/span/a@href"));
    if (!empty($contriIDOff)) {
      $contriIDOff = substr($contriIDOff[1], (strrpos($contriIDOff[1], '=') + 1));
    }
    if (!empty($contriIDOn)) {
      $contriIDOn = substr($contriIDOn[1], (strrpos($contriIDOn[1], '=') + 1));
    }
    $this->select('task', "label=Update Pending Contribution Status");
    $this->click("_qf_Search_next_action");
    $this->waitForPageToLoad('30000');
    $this->select('contribution_status_id', 'label=Completed');
    $this->type("trxn_id_{$contriIDOff}", substr(sha1(rand()), 0, 5));
    $this->type("trxn_id_{$contriIDOn}", substr(sha1(rand()), 0, 5));
    $this->click('_qf_Status_next');
    $this->waitForElementPresent("_qf_Result_done");
    $this->click("_qf_Result_done");
    $this->waitForPageToLoad('30000');
    $status = 'Completed';
    $this->verifyText("xpath=id('contributionSearch')/table[1]/tbody/tr[1]/td[9]", preg_quote($status));
    $this->verifyText("xpath=id('contributionSearch')/table[1]/tbody/tr[2]/td[9]", preg_quote($status));
  }

  function _testOfflineContribution($firstName, $lastName, $email) {
    // Create a contact to be used as soft creditor
    $softCreditFname = substr(sha1(rand()), 0, 7);
    $softCreditLname = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($softCreditFname, $softCreditLname, FALSE);

    // Go directly to the URL of the screen that you will be testing (New Contribution-standalone).
    $this->open($this->sboxPath . "civicrm/contribute/add?reset=1&context=standalone");

    // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
    // button at the end of this page to show up, to make sure it's fully loaded.
    $this->waitForElementPresent("_qf_Contribution_upload");

    // Let's start filling the form with values.

    // create new contact using dialog
    $this->webtestNewDialogContact($firstName, "Contributor", $email);

    // select financial type
    $this->select("contribution_type_id", "value=1");

    // fill in Received Date
    $this->webtestFillDate('receive_date');

    //Contribution status
    $this->select("contribution_status_id", "label=Pending");

    // source
    $this->type("source", "Mailer 1");

    // total amount
    $this->type("total_amount", "100");

    // select payment instrument type = Check and enter chk number
    $this->select("payment_instrument_id", "value=4");
    $this->waitForElementPresent("check_number");
    $this->type("check_number", "check #1041");

    $this->type("trxn_id", "P20901X1" . rand(100, 10000));

    // soft credit
    $this->type("soft_credit_to", $softCreditFname);
    $this->fireEvent("soft_credit_to", "focus");
    $this->click('soft_credit_to');
    $this->waitForElementPresent("css=div.ac_results-inner li");
    $this->click("css=div.ac_results-inner li");

    //Custom Data
    //$this->click('CIVICRM_QFID_3_6');

    //Additional Detail section
    $this->click("AdditionalDetail");
    $this->waitForElementPresent("thankyou_date");

    $this->type("note", "This is a test note.");
    $this->type("non_deductible_amount", "10");
    $this->type("fee_amount", "0");
    $this->type("net_amount", "0");
    $this->type("invoice_id", time());
    $this->webtestFillDate('thankyou_date');

    //Honoree section
    $this->click("Honoree");
    $this->waitForElementPresent("honor_email");

    $this->click("CIVICRM_QFID_1_2");
    $this->select("honor_prefix_id", "label=Ms.");
    $this->type("honor_first_name", "Foo");
    $this->type("honor_last_name", "Bar");
    $this->type("honor_email", "foo@bar.com");

    //Premium section
    $this->click("Premium");
    $this->waitForElementPresent("fulfilled_date");
    $this->select("product_name[0]", "label=Coffee Mug ( MUG-101 )");
    $this->select("product_name[1]", "label=Black");
    $this->webtestFillDate('fulfilled_date');

    // Clicking save.
    $this->click("_qf_Contribution_upload");
    $this->waitForPageToLoad("30000");

    // Is status message correct?
    $this->assertTrue($this->isTextPresent("The contribution record has been saved."), "Status message didn't show up after saving!");

    // verify if Membership is created
    $this->waitForElementPresent("xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='View']");

    //click through to the Membership view screen
    $this->click("xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_ContributionView_cancel-bottom");

    $expected = array(
      'Contribution Type' => 'Donation',
      'Total Amount' => '100.00',
      'Contribution Status' => 'Pending',
      'Paid By' => 'Check',
      'Check Number' => 'check #1041',
      'Soft Credit To' => "{$softCreditFname} {$softCreditLname}",
    );
    foreach ($expected as $label => $value) {
      $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody//tr/td[1][text()='$label']/../td[2]", preg_quote($value));
    }

    // go to soft creditor contact view page
    $this->click("xpath=id('ContributionView')/div[2]/table[1]/tbody//tr/td[1][text()='Soft Credit To']/../td[2]/a[text()='{$softCreditFname} {$softCreditLname}']");

    // go to contribution tab
    $this->waitForElementPresent("css=li#tab_contribute a");
    $this->click("css=li#tab_contribute a");
    $this->waitForElementPresent("link=Record Contribution (Check, Cash, EFT ...)");

    // verify soft credit details
    $expected = array(
      3 => 'Donation',
      2 => '100.00',
      5 => 'Pending',
      1 => "{$firstName} Contributor",
    );
    foreach ($expected as $value => $label) {
      $this->verifyText("xpath=id('Search')/div[2]/table[2]/tbody/tr[2]/td[$value]", preg_quote($label));
    }
  }

  function _testOnlineContribution($firstName, $lastName, $email) {

    // We need a payment processor
    $processorName = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
    $processorType = 'Dummy';
    $pageTitle     = substr(sha1(rand()), 0, 7);
    $rand          = 2 * rand(2, 50);
    $hash          = substr(sha1(rand()), 0, 7);
    $amountSection = TRUE;
    $payLater      = TRUE;
    $onBehalf      = FALSE;
    $pledges       = FALSE;
    $recurring     = FALSE;
    $memberships   = FALSE;
    $friend        = FALSE;
    $profilePreId  = 1;
    $profilePostId = NULL;
    $premiums      = FALSE;
    $widget        = FALSE;
    $pcp           = FALSE;
    $memPriceSetId = NULL;

    // create a new online contribution page
    // create contribution page with randomized title and default params
    $pageId = $this->webtestAddContributionPage($hash,
      $rand,
      $pageTitle,
      array($processorName => $processorType),
      $amountSection,
      $payLater,
      $onBehalf,
      $pledges,
      $recurring,
      $memberships,
      $memPriceSetId,
      $friend,
      $profilePreId,
      $profilePostId,
      $premiums,
      $widget,
      $pcp
    );

    //logout
    $this->open($this->sboxPath . "civicrm/logout?reset=1");
    $this->waitForPageToLoad('30000');
    $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&id=" . $pageId);
    $this->waitForElementPresent("_qf_Main_upload-bottom");



    $this->type("email-5", $email);

    $this->type("first_name", $firstName);
    $this->type("last_name", $lastName);
    
    $this->click("xpath=//div[@class='crm-section other_amount-section']//div[2]/input");
    $this->type("xpath=//div[@class='crm-section other_amount-section']//div[2]/input", 100);
    $this->click("xpath=//div[@class='crm-section payment_processor-section']/div[2]//label[text()='Pay later label {$hash}']");
    $streetAddress = "100 Main Street";
    $this->type("street_address-1", $streetAddress);
    $this->type("city-1", "San Francisco");
    $this->type("postal_code-1", "94117");
    $this->select("country-1", "value=1228");
    $this->select("state_province-1", "value=1001");


    $this->click("_qf_Main_upload-bottom");

    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("_qf_Confirm_next-bottom");

    $this->click("_qf_Confirm_next-bottom");
    $this->waitForPageToLoad('30000');

   //login to check contribution
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    //Find Contribution
    $this->open($this->sboxPath . "civicrm/contribute/search?reset=1");

    $this->waitForElementPresent("contribution_date_low");

    $this->type("sort_name", "$lastName, $firstName");
    $this->click("_qf_Search_refresh");

    $this->waitForPageToLoad('30000');

    $this->waitForElementPresent("xpath=//div[@id='contributionSearch']//table//tbody/tr[2]/td[11]/span/a[text()='View']");
    $this->click("xpath=//div[@id='contributionSearch']//table//tbody/tr[2]/td[11]/span/a[text()='View']");
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("_qf_ContributionView_cancel-bottom");
    //View Contribution Record
    $expected = array(
      2 => 'Donation',
      3 => '100.00',
      6 => 'Pending : Pay Later',
      1 => "{$firstName} {$lastName}",
    );
    foreach ($expected as $value => $label) {
      $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[$value]/td[2]", preg_quote($label));
    }
  }
}

