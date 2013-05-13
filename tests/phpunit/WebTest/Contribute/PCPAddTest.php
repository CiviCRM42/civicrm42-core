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
class WebTest_Contribute_PCPAddTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testPCPAdd() {
    // open browser, login
    $this->open($this->sboxPath);
    $this->webtestLogin();

    // set pcp supporter name and email
    $firstName  = 'Ma' . substr(sha1(rand()), 0, 4);
    $lastName   = 'An' . substr(sha1(rand()), 0, 7);
    $middleName = 'Mid' . substr(sha1(rand()), 0, 7);
    $email      = substr(sha1(rand()), 0, 7) . '@example.org';

    $this->open($this->sboxPath . 'civicrm/admin/domain?action=update&reset=1');
    $this->waitForElementPresent('_qf_Domain_cancel-bottom');
    $this->type('name', 'DefaultDomain');
    $this->type('email_name', $firstName);
    $this->type('email_address', $email);

    $this->click('_qf_Domain_next_view-bottom');
    $this->waitForPageToLoad('30000');
    $this->assertTrue($this->isTextPresent("Domain information for 'DefaultDomain' has been saved."),
      "Status message didn't show up after saving!"
    );

    require_once 'ContributionPageAddTest.php';

    // a random 7-char string and an even number to make this pass unique
    $hash            = substr(sha1(rand()), 0, 7);
    $rand            = $contributionAmount = 2 * rand(2, 50);
    $pageTitle       = 'PCP Contribution' . $hash;
    $processorType   = 'Dummy';
    $processorName   = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
    $amountSection   = TRUE;
    $payLater        = TRUE;
    $onBehalf        = FALSE;
    $pledges         = FALSE;
    $recurring       = FALSE;
    $memberships     = FALSE;
    $memPriceSetId   = NULL;
    $friend          = FALSE;
    $profilePreId    = NULL;
    $profilePostId   = NULL;
    $premiums        = FALSE;
    $widget          = FALSE;
    $pcp             = TRUE;
    $isAprovalNeeded = TRUE;

    // create a new online contribution page with pcp enabled
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
      $pcp,
      TRUE,
      $isAprovalNeeded
    );

    // logout
    $this->open($this->sboxPath . "civicrm/logout?reset=1");
    // Wait for Login button to indicate we've logged out.
    $this->waitForElementPresent("edit-submit");

    $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&id=" . $pageId);
    $this->waitForElementPresent("_qf_Main_upload-bottom");

    $this->click("xpath=//div[@class='crm-section other_amount-section']//div[2]/input");
    $this->type("xpath=//div[@class='crm-section other_amount-section']//div[2]/input", $contributionAmount);
    $this->type("email-5", $email);

    $this->webtestAddCreditCardDetails();
    $this->webtestAddBillingDetails($firstName, $middleName, $lastName);

    $this->click("_qf_Main_upload-bottom");
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("_qf_Confirm_next-bottom");
    $this->click("_qf_Confirm_next-bottom");

    $this->waitForElementPresent("thankyou_footer");
    $this->open($this->sboxPath . "civicrm/contribute/campaign?action=add&reset=1&pageId=" . $pageId . "&component=contribute");
    $this->waitForElementPresent("_qf_PCPAccount_next-bottom");

    $cmsUserName = 'CmsUser' . substr(sha1(rand()), 0, 7);
    $this->type("cms_name", $cmsUserName);
    $this->click("checkavailability");
    $this->type("first_name", $firstName);
    $this->type("last_name", $lastName);
    $this->type("email-Primary", $email);
    $this->click("_qf_PCPAccount_next-bottom");
    $this->waitForElementPresent("_qf_Campaign_upload-bottom");

    $pcpTitle = 'PCPTitle' . substr(sha1(rand()), 0, 7);
    $this->type("pcp_title", $pcpTitle);
    $this->type("pcp_intro_text", "Welcome Text $hash");
    $this->type("goal_amount", $contributionAmount);
    $this->click("_qf_Campaign_upload-bottom");

    $this->open($this->sboxPath);
    $this->webtestLogin();
    $this->open($this->sboxPath . "civicrm/admin/pcp?reset=1");
    $this->waitForElementPresent("_qf_PCP_refresh");
    $this->select('status_id', 'value=1');
    $this->click("_qf_PCP_refresh");
    $this->waitForElementPresent("_qf_PCP_refresh");
    $id = explode('id=', $this->getAttribute("xpath=//div[@id='option11_wrapper']/table[@id='option11']/tbody/tr/td/a[text()='$pcpTitle']@href"));
    $pcpId = trim($id[1]);
    $pcpUrl = "civicrm/contribute/pcp/info?reset=1&id=$pcpId";
    $this->click("xpath=//td[@id=$pcpId]/span[1]/a[2]");
    $this->waitForPageToLoad("30000");
    // logout
    $this->open($this->sboxPath . 'civicrm/logout?reset=1');
    // Wait for Login button to indicate we've logged out.
    $this->waitForElementPresent('edit-submit');

    // Set pcp contributor name
    $donorFirstName  = 'Donor' . substr(sha1(rand()), 0, 4);
    $donorLastName   = 'Person' . substr(sha1(rand()), 0, 7);
    $middleName = 'Mid' . substr(sha1(rand()), 0, 7);

    $this->open($this->sboxPath . $pcpUrl);

    $this->waitForPageToLoad("30000");
    $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&id=$pageId&pcpId=$id[1]");

    $this->waitForElementPresent("_qf_Main_upload-bottom");
    $this->click("xpath=//div[@class='crm-section other_amount-section']//div[2]/input");
    $this->type("xpath=//div[@class='crm-section other_amount-section']//div[2]/input", $contributionAmount);
    $this->type("email-5", $donorFirstName . "@example.com");

    $this->webtestAddCreditCardDetails();
    $this->webtestAddBillingDetails($donorFirstName, $middleName, $donorLastName);
    $this->click("_qf_Main_upload-bottom");
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("_qf_Confirm_next-bottom");
    $this->click("_qf_Confirm_next-bottom");

    $this->waitForElementPresent("thankyou_footer");
    //login to check contribution
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    //Find Contribution
    $this->open($this->sboxPath . "civicrm/contribute/search?reset=1");

    $this->waitForElementPresent("contribution_date_low");

    $this->select('contribution_pcp_made_through_id', "label={$pcpTitle}");

    $this->click("_qf_Search_refresh");

    $this->waitForPageToLoad('30000');

    $this->waitForElementPresent("xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']");
    $this->click("xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']");
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("_qf_ContributionView_cancel-bottom");

    //View Contribution Record
    $expected = array(
      2 => 'Donation',
      3 => $contributionAmount,
      7 => 'Completed',
      1 => "{$donorFirstName} {$donorLastName}",
    );
    foreach ($expected as $value => $label) {
      $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[$value]/td[2]",
        preg_quote($label)
      );
    }

    //Check for SoftCredit
    $this->verifyText("xpath=id('PCPView')//div[@class='crm-accordion-body']/table/tbody/tr[1]/td[2]/a[text()]", preg_quote($pcpTitle));
    $this->verifyText("xpath=id('PCPView')//div[@class='crm-accordion-body']/table/tbody/tr[2]/td[2]/a[text()]", preg_quote("{$firstName} {$lastName}"));

    // Check PCP Summary Report
    $this->open($this->sboxPath . "civicrm/report/instance/15?reset=1");
    $this->verifyText("PCP", preg_quote($pcpTitle));
    $this->verifyText("PCP", preg_quote("{$lastName}, {$firstName}"));
  }
}

