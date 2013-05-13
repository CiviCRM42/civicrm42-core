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
class WebTest_Contact_SearchBuilderTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testSearchBuilderRLIKE() {
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
    $this->createDetailContact($firstName);

    $sortName = "adv$firstName, $firstName";
    $displayName = "$firstName adv$firstName";

    // Go directly to the URL of the screen that you will be testing (Home dashboard).
    $this->open($this->sboxPath . "civicrm/contact/search/builder?reset=1");
    $this->waitForPageToLoad("30000");

    $this->select("id=mapper[1][0][0]", "label=Individual");
    $this->select("id=mapper[1][0][1]", "label=Postal Code");
    $this->select("id=operator_1_0", "label=RLIKE");
    $this->type("id=value_1_0", "100[0-9]");
    $this->click("id=_qf_Builder_refresh");
    $this->waitForPageToLoad("30000");

    // Is contact present?
    $this->assertTrue($this->isTextPresent("$sortName"), "Did not find Contact!");
  }

  // function to create contact with details (contact details, address, Constituent information ...)
  function createDetailContact($firstName = NULL) {

    if (!$firstName) {
      $firstName = substr(sha1(rand()), 0, 7);
    }

    // create contact type Individual with subtype
    // with most of values to required to search
    $Subtype = "Student";
    $this->open($this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual");
    $this->waitForPageToLoad("30000");
    $this->waitForElementPresent("_qf_Contact_cancel");

    // --- fill few values in Contact Detail block
    $this->type("first_name", "$firstName");
    $this->type("middle_name", "mid$firstName");
    $this->type("last_name", "adv$firstName");
    $this->select("contact_sub_type", "label=- $Subtype");
    $this->type("email_1_email", "$firstName@advsearch.co.in");
    $this->type("phone_1_phone", "123456789");
    $this->type("external_identifier", "extid$firstName");

    // --- fill few values in address
    $this->click("//form[@id='Contact']/div[2]/div[4]/div[1]");
    $this->waitForElementPresent("address_1_geo_code_2");
    $this->type("address_1_street_address", "street 1 $firstName");
    $this->type("address_1_supplemental_address_1", "street supplement 1 $firstName");
    $this->type("address_1_supplemental_address_2", "street supplement 2 $firstName");
    $this->type("address_1_city", "city$firstName");
    $this->type("address_1_postal_code", "100100");
    $this->select("address_1_country_id", "United States");
    $this->select("address_1_state_province_id", "Alaska");

    // save contact
    $this->click("_qf_Contact_upload_view");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("$firstName adv$firstName"));
  }
}


