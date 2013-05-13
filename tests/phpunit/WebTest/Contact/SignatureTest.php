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
class WebTest_Contact_SignatureTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  /*
   *  Test Signature in TinyMC.
   */
  function testTinyMCE() {

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

    $this->open($this->sboxPath . "civicrm/dashboard?reset=1");
    $this->click("//div[@id='recently-viewed']/ul/li/a");
    $this->waitForPageToLoad('30000');

    // Get contact id from url.
    $matches = array();
    preg_match('/cid=([0-9]+)/', $this->getLocation(), $matches);
    $contactId = $matches[1];

    // Select Your Editor
    $this->_selectEditor('TinyMCE');

    $this->open($this->sboxPath . "civicrm/contact/add?reset=1&action=update&cid={$contactId}");
    $this->waitForPageToLoad('30000');

    $this->click("//tr[@id='Email_Block_1']/td[1]/div[2]/div[1]");

    // HTML format message
    $signature = 'Contact Signature in html';

    $this->fireEvent('email_1_signature_html', 'focus');
    $this->fillRichTextField('email_1_signature_html', $signature, 'TinyMCE');

    // TEXT Format Message
    $this->type('email_1_signature_text', 'Contact Signature in text');
    $this->click('_qf_Contact_upload_view-top');
    $this->waitForPageToLoad('30000');

    // Is status message correct?
    $this->assertTrue($this->isTextPresent('Your Individual contact record has been saved.'));

    // Go for Ckeck Your Editor, Click on Send Mail
    $this->click("//div[@id='crm-contact-actions-link']/span");
    $this->click('link=Send an Email');
    $this->waitForPageToLoad('30000');
    sleep(10);

    $this->click('subject');
    $subject = 'Subject_' . substr(sha1(rand()), 0, 7);
    $this->type('subject', $subject);

    // Is signature correct? in Editor
    $this->_checkSignature('html_message', $signature, 'TinyMCE');

    $this->click('_qf_Email_upload-top');
    $this->waitForPageToLoad('30000');

    // Go for Activity Search
    $this->_checkActivity($subject, $signature);
  }

  /*
   *  Test Signature in CKEditor.
   */
  function testCKEditor() {

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

    $this->open($this->sboxPath . "civicrm/dashboard?reset=1");
    $this->click("//div[@id='recently-viewed']/ul/li/a");
    $this->waitForPageToLoad('30000');

    // Get contact id from url.
    $matches = array();
    preg_match('/cid=([0-9]+)/', $this->getLocation(), $matches);
    $contactId = $matches[1];

    // Select Your Editor
    $this->_selectEditor('CKEditor');

    $this->open($this->sboxPath . "civicrm/contact/add?reset=1&action=update&cid={$contactId}");
    $this->waitForPageToLoad('30000');

    $this->click("//tr[@id='Email_Block_1']/td[1]/div[2]/div[1]");

    // HTML format message
    $signature = 'Contact Signature in html';
    $this->fireEvent('email_1_signature_html', 'focus');
    $this->fillRichTextField('email_1_signature_html', $signature, 'CKEditor');

    // TEXT Format Message
    $this->type('email_1_signature_text', 'Contact Signature in text');
    $this->click('_qf_Contact_upload_view-top');
    $this->waitForPageToLoad('30000');

    // Is status message correct?
    $this->assertTrue($this->isTextPresent('Your Individual contact record has been saved.'));

    // Go for Ckeck Your Editor, Click on Send Mail
    $this->click("//div[@id='crm-contact-actions-link']/span");
    $this->click('link=Send an Email');
    $this->waitForPageToLoad('30000');
    sleep(10);

    $this->click('subject');
    $subject = 'Subject_' . substr(sha1(rand()), 0, 7);
    $this->type('subject', $subject);

    // Is signature correct? in Editor
    $this->_checkSignature('html_message', $signature, 'CKEditor');

    $this->click('_qf_Email_upload-top');
    $this->waitForPageToLoad('30000');

    // Go for Activity Search
    $this->_checkActivity($subject, $signature);
  }

  /*
   * Helper function to select Editor.
   */
  function _selectEditor($editor) {
    // Go directly to the URL of Set Default Editor.
    $this->open($this->sboxPath . 'civicrm/admin/setting/preferences/display?reset=1');
    $this->waitForPageToLoad('30000');

    // Select your Editor
    $this->click('editor_id');
    $this->select('editor_id', "label=$editor");
    $this->click('_qf_Display_next-bottom');
    $this->waitForPageToLoad('30000');
  }
  /*
   * Helper function for Check Signature in Editor.
   */
  function _checkSignature($fieldName, $signature, $editor) {
    if ($editor == 'CKEditor') {
      $this->waitForElementPresent("xpath=//div[@id='cke_{$fieldName}']//iframe");
      $this->selectFrame("xpath=//div[@id='cke_{$fieldName}']//iframe");
    }
    else {
      $this->selectFrame("xpath=//iframe[@id='{$fieldName}_ifr']");
    }

    $this->verifyText('//html/body', preg_quote("{$signature}"));
    $this->selectFrame('relative=top');
  }
  /*
   * Helper function for Check Signature in Activity.
   */
  function _checkActivity($subject, $signature) {
    $this->open($this->sboxPath . 'civicrm/activity/search?reset=1');
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent('_qf_Search_refresh');

    $this->type('activity_subject', $subject);

    $this->click('_qf_Search_refresh');
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent('_qf_Search_next_print');

    // View your Activity
    $this->click("xpath=id('Search')/div[3]/div/div[2]/table/tbody/tr[2]/td[9]/span/a[text()='View']");
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent('_qf_ActivityView_next-bottom');

    // Is signature correct? in Activity
    $this->assertTrue($this->isTextPresent($signature));
  }
}

