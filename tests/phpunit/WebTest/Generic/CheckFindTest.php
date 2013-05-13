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
 | Version 3, 19 November 2007.                                       |
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
class WebTest_Generic_CheckFindTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testCheckDashboardElements() {
    $this->open($this->sboxPath);
    $this->webtestLogin();

    // Go directly to the URL of the screen that you will be testing.
    $this->open($this->sboxPath . "civicrm/contact/search?reset=1");
    $this->waitForElementPresent("_qf_Basic_refresh");
    $this->click("//input[@name='_qf_Basic_refresh' and @value='Search']");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isElementPresent("search-status"));
  }
}


