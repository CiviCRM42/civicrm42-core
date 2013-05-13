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
class WebTest_Event_PricesetMaxCountTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testWithoutFieldCount() {
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    // We need a payment processor
    $processorName = 'Webtest Dummy' . substr(sha1(rand()), 0, 7);
    $this->webtestAddPaymentProcessor($processorName);

    // create priceset
    $priceset = 'Price - ' . substr(sha1(rand()), 0, 7);
    $this->_testAddSet($priceset);

    // create price fields
    $fields = array(
      'Full Conference' => array('type' => 'Text',
        'amount' => '525.00',
        'max_count' => 2,
        'is_required' => TRUE,
      ),
      'Meal Choice' => array(
        'type' => 'Select',
        'options' => array(
          1 => array('label' => 'Chicken',
            'amount' => '525.00',
            'max_count' => 1,
          ),
          2 => array(
            'label' => 'Vegetarian',
            'amount' => '200.00',
            'max_count' => 5,
          ),
        ),
      ),
      'Pre-conference Meetup?' => array(
        'type' => 'Radio',
        'options' => array(
          1 => array('label' => 'Yes',
            'amount' => '50.00',
            'max_count' => 1,
          ),
          2 => array(
            'label' => 'No',
            'amount' => '10',
            'max_count' => 5,
          ),
        ),
      ),
      'Evening Sessions' => array(
        'type' => 'CheckBox',
        'options' => array(
          1 => array('label' => 'First Five',
            'amount' => '100.00',
            'max_count' => 2,
          ),
          2 => array(
            'label' => 'Second Four',
            'amount' => '50.00',
            'max_count' => 4,
          ),
        ),
      ),
    );

    // add price fields
    $this->_testAddPriceFields($fields);

    // get price set url.
    $pricesetLoc = $this->getLocation();

    // get text field Id.
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[1]/td[9]/span[1]/a[2]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/fid=([0-9]+)/', $this->getLocation(), $matches);
    $textFieldId = $matches[1];

    $this->open($pricesetLoc);
    $this->waitForPageToLoad('30000');

    // get select field id
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[2]/td[8]/a");
    $this->waitForPageToLoad('30000');
    $selectFieldLoc = $this->getLocation();
    $matches = array();
    preg_match('/fid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldId = $matches[1];

    // get select field ids
    // get select field option1
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[1]/td[6]/span[1]/a[1]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/oid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldOp1 = $matches[1];

    $this->open($selectFieldLoc);
    $this->waitForPageToLoad('30000');

    // get select field option2
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[2]/td[6]/span[1]/a[1]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/oid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldOp2 = $matches[1];

    // create event.
    $eventTitle = 'Meeting - ' . substr(sha1(rand()), 0, 7);
    $paramsEvent = array(
      'title' => $eventTitle,
      'template_id' => 6,
      'event_type_id' => 4,
      'payment_processor' => $processorName,
      'price_set' => $priceset,
    );

    $infoEvent = $this->_testAddEvent($paramsEvent);

    // logout to register for event.
    $this->open($this->sboxPath . 'civicrm/logout?reset=1');
    $this->waitForPageToLoad('30000');

    // Register Participant 1
    // visit event info page
    $this->open($infoEvent);
    $this->waitForPageToLoad('30000');

    // register for event
    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '3');

    $this->select("price_{$selectFieldId}", "value={$selectFieldOp1}");

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing related info
    $this->_fillRegisterWithBillingInfo();

    $this->assertStringsPresent(array('Sorry, currently only 2 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    $this->_checkConfirmationAndRegister();

    // Register Participant 2
    // visit event info page
    $this->open($infoEvent);
    $this->waitForPageToLoad('30000');

    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '2');
    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing related info and register
    $this->_fillRegisterWithBillingInfo();

    $this->assertStringsPresent(array('Sorry, currently only a single seat is available for this option.'));

    // fill correct value for test field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    // select sold option for select field, check for form rule
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp1}");
    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, this option is currently sold out.'));

    // fill correct available option for select field
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    $this->_checkConfirmationAndRegister();
  }

  function testWithFieldCount() {
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    // We need a payment processor
    $processorName = 'Webtest Dummy' . substr(sha1(rand()), 0, 7);
    $this->webtestAddPaymentProcessor($processorName);

    // create priceset
    $priceset = 'Price - ' . substr(sha1(rand()), 0, 7);
    $this->_testAddSet($priceset);

    // create price fields
    $fields = array(
      'Full Conference' => array('type' => 'Text',
        'amount' => '525.00',
        'max_count' => 4,
        'count' => 2,
        'is_required' => TRUE,
      ),
      'Meal Choice' => array(
        'type' => 'Select',
        'options' => array(
          1 => array('label' => 'Chicken',
            'amount' => '525.00',
            'max_count' => 2,
            'count' => 2,
          ),
          2 => array(
            'label' => 'Vegetarian',
            'amount' => '200.00',
            'max_count' => 10,
            'count' => 5,
          ),
        ),
      ),
      'Pre-conference Meetup?' => array(
        'type' => 'Radio',
        'options' => array(
          1 => array('label' => 'Yes',
            'amount' => '50.00',
            'max_count' => 2,
            'count' => 1,
          ),
          2 => array(
            'label' => 'No',
            'amount' => '10',
            'max_count' => 10,
            'count' => 5,
          ),
        ),
      ),
      'Evening Sessions' => array(
        'type' => 'CheckBox',
        'options' => array(
          1 => array('label' => 'First Five',
            'amount' => '100.00',
            'max_count' => 4,
            'count' => 2,
          ),
          2 => array(
            'label' => 'Second Four',
            'amount' => '50.00',
            'max_count' => 8,
            'count' => 4,
          ),
        ),
      ),
    );

    // add price fields
    $this->_testAddPriceFields($fields);

    // get price set url.
    $pricesetLoc = $this->getLocation();

    // get text field Id.
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[1]/td[9]/span[1]/a[2]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/fid=([0-9]+)/', $this->getLocation(), $matches);
    $textFieldId = $matches[1];

    $this->open($pricesetLoc);
    $this->waitForPageToLoad('30000');

    // get select field id
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[2]/td[8]/a");
    $this->waitForPageToLoad('30000');
    $selectFieldLoc = $this->getLocation();
    $matches = array();
    preg_match('/fid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldId = $matches[1];

    // get select field ids
    // get select field option1
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[1]/td[6]/span[1]/a[1]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/oid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldOp1 = $matches[1];

    $this->open($selectFieldLoc);
    $this->waitForPageToLoad('30000');

    // get select field option2
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[2]/td[6]/span[1]/a[1]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/oid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldOp2 = $matches[1];

    // create event.
    $eventTitle = 'Meeting - ' . substr(sha1(rand()), 0, 7);
    $paramsEvent = array(
      'title' => $eventTitle,
      'template_id' => 6,
      'event_type_id' => 4,
      'payment_processor' => $processorName,
      'price_set' => $priceset,
    );

    $infoEvent = $this->_testAddEvent($paramsEvent);

    // logout to register for event.
    $this->open($this->sboxPath . 'civicrm/logout?reset=1');
    $this->waitForPageToLoad('30000');

    // Register Participant 1
    // visit event info page
    $this->open($infoEvent);
    $this->waitForPageToLoad('30000');

    // register for event
    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    // check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '3');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing related info
    $this->_fillRegisterWithBillingInfo();

    $this->assertStringsPresent(array('Sorry, currently only 4 seats are available for this option.'));

    $this->select("price_{$selectFieldId}", "value={$selectFieldOp1}");

    // fill correct value and register
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    $this->_checkConfirmationAndRegister();

    // Register Participant 2
    // visit event info page
    $this->open($infoEvent);
    $this->waitForPageToLoad('30000');

    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    // check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '2');
    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing related info and register
    $this->_fillRegisterWithBillingInfo();

    $this->assertStringsPresent(array('Sorry, currently only 2 seats are available for this option.'));

    // fill correct value and register
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    // check for sold option for select field
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp1}");

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, this option is currently sold out.'));

    // check for sold option for select field
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    $this->_checkConfirmationAndRegister();
  }

  function testAdditionalParticipantWithoutFieldCount() {
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    // We need a payment processor
    $processorName = 'Webtest Dummy' . substr(sha1(rand()), 0, 7);
    $this->webtestAddPaymentProcessor($processorName);

    // create priceset
    $priceset = 'Price - ' . substr(sha1(rand()), 0, 7);
    $this->_testAddSet($priceset);

    // create price fields
    $fields = array(
      'Full Conference' => array('type' => 'Text',
        'amount' => '525.00',
        'max_count' => 6,
        'is_required' => TRUE,
      ),
      'Meal Choice' => array(
        'type' => 'Select',
        'options' => array(
          1 => array('label' => 'Chicken',
            'amount' => '525.00',
            'max_count' => 3,
          ),
          2 => array(
            'label' => 'Vegetarian',
            'amount' => '200.00',
            'max_count' => 2,
          ),
        ),
      ),
      'Pre-conference Meetup?' => array(
        'type' => 'Radio',
        'options' => array(
          1 => array('label' => 'Yes',
            'amount' => '50.00',
            'max_count' => 4,
          ),
          2 => array(
            'label' => 'No',
            'amount' => '10',
            'max_count' => 5,
          ),
        ),
      ),
      'Evening Sessions' => array(
        'type' => 'CheckBox',
        'options' => array(
          1 => array('label' => 'First Five',
            'amount' => '100.00',
            'max_count' => 6,
          ),
          2 => array(
            'label' => 'Second Four',
            'amount' => '50.00',
            'max_count' => 4,
          ),
        ),
      ),
    );

    // add price fields
    $this->_testAddPriceFields($fields);

    // get price set url.
    $pricesetLoc = $this->getLocation();

    // get text field Id.
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[1]/td[9]/span[1]/a[2]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/fid=([0-9]+)/', $this->getLocation(), $matches);
    $textFieldId = $matches[1];

    $this->open($pricesetLoc);
    $this->waitForPageToLoad('30000');

    // get select field id
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[2]/td[8]/a");
    $this->waitForPageToLoad('30000');
    $selectFieldLoc = $this->getLocation();
    $matches = array();
    preg_match('/fid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldId = $matches[1];

    // get select field ids
    // get select field option1
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[1]/td[6]/span[1]/a[1]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/oid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldOp1 = $matches[1];

    $this->open($selectFieldLoc);
    $this->waitForPageToLoad('30000');

    // get select field option2
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[2]/td[6]/span[1]/a[1]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/oid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldOp2 = $matches[1];

    // create event.
    $eventTitle = 'Meeting - ' . substr(sha1(rand()), 0, 7);
    $paramsEvent = array(
      'title' => $eventTitle,
      'template_id' => 6,
      'event_type_id' => 4,
      'payment_processor' => $processorName,
      'price_set' => $priceset,
      'is_multiple_registrations' => TRUE,
    );

    $infoEvent = $this->_testAddEvent($paramsEvent);

    // logout to register for event.
    $this->open($this->sboxPath . 'civicrm/logout?reset=1');
    $this->waitForPageToLoad('30000');


    // 1'st registration
    // Register Participant 1
    // visit event info page
    $this->open($infoEvent);
    $this->waitForPageToLoad('30000');

    // register for event
    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    // select 3 participants ( including current )
    $this->select('additional_participants', 'value=2');

    // Check for Participant1
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '7');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing related info
    $this->_fillRegisterWithBillingInfo();

    $this->assertStringsPresent(array('Sorry, currently only 6 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    // Check for Participant2
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '6');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, currently only 6 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '3');
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    // Check for Participant3, check and skip
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '3');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    $this->click('_qf_Participant_2_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, currently only 6 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    // check for select
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Participant_2_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, currently only 2 seats are available for this option.'));

    // Skip participant3 and register
    $this->click('_qf_Participant_2_next_skip-Array');
    $this->waitForPageToLoad('30000');

    $this->_checkConfirmationAndRegister();


    // 2'st registration
    // Register Participant 1
    // visit event info page
    $this->open($infoEvent);
    $this->waitForPageToLoad('30000');

    // register for event
    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    // select 2 participants ( including current )
    $this->select('additional_participants', 'value=1');

    // Check for Participant1
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '3');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing related info
    $this->_fillRegisterWithBillingInfo();

    $this->assertStringsPresent(array('Sorry, currently only 2 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    // check for select field
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, this option is currently sold out.'));

    // fill available value for select
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp1}");

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    // Check for Participant2
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '2');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, currently only 2 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    // check for select field
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, this option is currently sold out.'));

    // fill available value for select
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp1}");

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    $this->_checkConfirmationAndRegister();
  }

  function testAdditionalParticipantWithFieldCount() {
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    // We need a payment processor
    $processorName = 'Webtest Dummy' . substr(sha1(rand()), 0, 7);
    $this->webtestAddPaymentProcessor($processorName);

    // create priceset
    $priceset = 'Price - ' . substr(sha1(rand()), 0, 7);
    $this->_testAddSet($priceset);

    // create price fields
    $fields = array(
      'Full Conference' => array('type' => 'Text',
        'amount' => '525.00',
        'count' => 2,
        'max_count' => 12,
        'is_required' => TRUE,
      ),
      'Meal Choice' => array(
        'type' => 'Select',
        'options' => array(
          1 => array('label' => 'Chicken',
            'amount' => '525.00',
            'count' => 1,
            'max_count' => 3,
          ),
          2 => array(
            'label' => 'Vegetarian',
            'amount' => '200.00',
            'count' => 2,
            'max_count' => 4,
          ),
        ),
      ),
      'Pre-conference Meetup?' => array(
        'type' => 'Radio',
        'options' => array(
          1 => array('label' => 'Yes',
            'amount' => '50.00',
            'count' => 2,
            'max_count' => 8,
          ),
          2 => array(
            'label' => 'No',
            'amount' => '10',
            'count' => 5,
            'max_count' => 25,
          ),
        ),
      ),
      'Evening Sessions' => array(
        'type' => 'CheckBox',
        'options' => array(
          1 => array('label' => 'First Five',
            'amount' => '100.00',
            'count' => 2,
            'max_count' => 16,
          ),
          2 => array(
            'label' => 'Second Four',
            'amount' => '50.00',
            'count' => 1,
            'max_count' => 4,
          ),
        ),
      ),
    );

    // add price fields
    $this->_testAddPriceFields($fields);

    // get price set url.
    $pricesetLoc = $this->getLocation();

    // get text field Id.
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[1]/td[9]/span[1]/a[2]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/fid=([0-9]+)/', $this->getLocation(), $matches);
    $textFieldId = $matches[1];

    $this->open($pricesetLoc);
    $this->waitForPageToLoad('30000');

    // get select field id
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[2]/td[8]/a");
    $this->waitForPageToLoad('30000');
    $selectFieldLoc = $this->getLocation();
    $matches = array();
    preg_match('/fid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldId = $matches[1];

    // get select field ids
    // get select field option1
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[1]/td[6]/span[1]/a[1]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/oid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldOp1 = $matches[1];

    $this->open($selectFieldLoc);
    $this->waitForPageToLoad('30000');

    // get select field option2
    $this->click("xpath=//div[@id='field_page']//table/tbody/tr[2]/td[6]/span[1]/a[1]");
    $this->waitForPageToLoad('30000');
    $matches = array();
    preg_match('/oid=([0-9]+)/', $this->getLocation(), $matches);
    $selectFieldOp2 = $matches[1];

    // create event.
    $eventTitle = 'Meeting - ' . substr(sha1(rand()), 0, 7);
    $paramsEvent = array(
      'title' => $eventTitle,
      'template_id' => 6,
      'event_type_id' => 4,
      'payment_processor' => $processorName,
      'price_set' => $priceset,
      'is_multiple_registrations' => TRUE,
    );

    $infoEvent = $this->_testAddEvent($paramsEvent);

    // logout to register for event.
    $this->open($this->sboxPath . 'civicrm/logout?reset=1');
    $this->waitForPageToLoad('30000');


    // 1'st registration
    // Register Participant 1
    // visit event info page
    $this->open($infoEvent);
    $this->waitForPageToLoad('30000');

    // register for event
    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    // select 3 participants ( including current )
    $this->select('additional_participants', 'value=2');

    // Check for Participant1
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '7');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing related info
    $this->_fillRegisterWithBillingInfo();

    $this->assertStringsPresent(array('Sorry, currently only 12 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    // Check for Participant2
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '6');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, currently only 12 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '3');
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    // Check for Participant3, check and skip
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '3');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    $this->click('_qf_Participant_2_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, currently only 12 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    // check for select
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Participant_2_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, currently only 4 seats are available for this option.'));

    // Skip participant3 and register
    $this->click('_qf_Participant_2_next_skip-Array');
    $this->waitForPageToLoad('30000');

    $this->_checkConfirmationAndRegister();


    // 2'st registration
    // Register Participant 1
    // visit event info page
    $this->open($infoEvent);
    $this->waitForPageToLoad('30000');

    // register for event
    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    // select 2 participants ( including current )
    $this->select('additional_participants', 'value=1');

    // Check for Participant1
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '3');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing related info
    $this->_fillRegisterWithBillingInfo();

    $this->assertStringsPresent(array('Sorry, currently only 4 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    // check for select field
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, this option is currently sold out.'));

    // fill available value for select
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp1}");

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');

    // Check for Participant2
    // exceed maximun count for text field, check for form rule
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '2');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, currently only 4 seats are available for this option.'));

    // fill correct value for text field
    $this->type("xpath=//input[@id='price_{$textFieldId}']", '1');

    // check for select field
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp2}");

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    $this->assertStringsPresent(array('Sorry, this option is currently sold out.'));

    // fill available value for select
    $this->select("price_{$selectFieldId}", "value={$selectFieldOp1}");

    $this->click('_qf_Participant_1_next-Array');
    $this->waitForPageToLoad('30000');

    $this->_checkConfirmationAndRegister();
  }

  function _testAddSet($setTitle) {
    $this->open($this->sboxPath . 'civicrm/admin/price?reset=1&action=add');
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent('_qf_Set_next-bottom');

    // Enter Priceset fields (Title, Used For ...)
    $this->type('title', $setTitle);
    $this->check('extends[1]');
    $this->type('help_pre', 'This is test priceset.');

    $this->assertChecked('is_active', 'Verify that Is Active checkbox is set.');
    $this->click('_qf_Set_next-bottom');

    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent('_qf_Field_next-bottom');
  }

  function _testAddPriceFields($fields) {
    $fieldCount = count($fields);
    $count = 1;
    foreach ($fields as $label => $field) {
      $this->type('label', $label);
      $this->select('html_type', "value={$field['type']}");

      if ($field['type'] == 'Text') {
        $this->type('price', $field['amount']);

        if (isset($field['count'])) {
          $this->waitForElementPresent('count');
          $this->type('count', $field['count']);
        }

        if (isset($field['count'])) {
          $this->waitForElementPresent('count');
          $this->type('count', $field['count']);
        }

        if (isset($field['max_count'])) {
          $this->waitForElementPresent('max_value');
          $this->type('max_value', $field['max_count']);
        }
      }
      else {
        $this->_testAddMultipleChoiceOptions($field['options'], $field['type']);
      }

      if (isset($field['is_required']) && $field['is_required']) {
        $this->check('is_required');
      }

      if ($count < $fieldCount) {
        $this->click('_qf_Field_next_new-bottom');
      }
      else {
        $this->click('_qf_Field_next-bottom');
      }
      $this->waitForPageToLoad('30000');

      $count++;
    }
  }

  function _testAddMultipleChoiceOptions($options, $fieldType) {
    foreach ($options as $oIndex => $oValue) {
      $this->type("option_label_{$oIndex}", $oValue['label']);
      $this->type("option_amount_{$oIndex}", $oValue['amount']);

      if (isset($oValue['count'])) {
        $this->waitForElementPresent("option_count_{$oIndex}");
        $this->type("option_count_{$oIndex}", $oValue['count']);
      }

      if (isset($oValue['max_count'])) {
        $this->waitForElementPresent("option_max_value_{$oIndex}");
        $this->type("option_max_value_{$oIndex}", $oValue['max_count']);
      }

      $this->click('link=another choice');
    }

    // select first element as default
    if ($fieldType == 'CheckBox') {
      $this->click('default_checkbox_option[1]');
    }
    else {
      $this->click('CIVICRM_QFID_1_2');
    }
  }

  function _testAddEvent($params) {
    $this->open($this->sboxPath . 'civicrm/event/add?reset=1&action=add');

    $this->waitForElementPresent('_qf_EventInfo_upload-bottom');

    // Let's start filling the form with values.
    $this->select('event_type_id', "value={$params['event_type_id']}");

    // Attendee role s/b selected now.
    $this->select('default_role_id', 'value=1');

    // Enter Event Title, Summary and Description
    $this->type('title', $params['title']);
    $this->type('summary', 'This is a great conference. Sign up now!');
    $this->fillRichTextField('description', 'Here is a description for this event.', 'CKEditor');

    // Choose Start and End dates.
    // Using helper webtestFillDate function.
    $this->webtestFillDateTime('start_date', '+1 week');
    $this->webtestFillDateTime('end_date', '+1 week 1 day 8 hours ');

    $this->type('max_participants', '50');
    $this->click('is_map');
    $this->click('_qf_EventInfo_upload-bottom');

    // Wait for Location tab form to load
    $this->waitForPageToLoad('30000');

    // Go to Fees tab
    $this->click('link=Fees');
    $this->waitForElementPresent('_qf_Fee_upload-bottom');
    $this->click('CIVICRM_QFID_1_2');
    $processorName = $params['payment_processor'];
    $this->click("xpath=//tr[@class='crm-event-manage-fee-form-block-payment_processor']/td[2]/label[text()='$processorName']");
    $this->select('contribution_type_id', 'value=4');

    if (array_key_exists('price_set', $params)) {
      $this->select('price_set_id', 'label=' . $params['price_set']);
    }
    if (array_key_exists('fee_level', $params)) {
      $counter = 1;
      foreach ($params['fee_level'] as $label => $amount) {
        $this->type("label_{$counter}", $label);
        $this->type("value_{$counter}", $amount);
        $counter++;
      }
    }

    $this->click('_qf_Fee_upload-bottom');
    $this->waitForPageToLoad('30000');

    // Go to Online Registration tab
    $this->click('link=Online Registration');
    $this->waitForElementPresent('_qf_Registration_upload-bottom');

    $this->check('is_online_registration');
    $this->assertChecked('is_online_registration');

    if (isset($params['is_multiple_registrations']) && $params['is_multiple_registrations']) {
      $this->click('is_multiple_registrations');
    }

    $this->fillRichTextField('intro_text', 'Fill in all the fields below and click Continue.');

    // enable confirmation email
    $this->click('CIVICRM_QFID_1_2');
    $this->type('confirm_from_name', 'Jane Doe');
    $this->type('confirm_from_email', 'jane.doe@example.org');

    $this->click('_qf_Registration_upload-bottom');
    $this->waitForPageToLoad('30000');
    $this->waitForTextPresent("'Registration' information has been saved.");

    // verify event input on info page
    // start at Manage Events listing
    $this->open($this->sboxPath . 'civicrm/event/manage?reset=1');
    $this->click('link=' . $params['title']);

    $this->waitForPageToLoad('30000');
    return $this->getLocation();
  }

  function _fillRegisterWithBillingInfo() {
    $this->waitForElementPresent('credit_card_type');
    $this->select('credit_card_type', 'value=Visa');
    $this->type('credit_card_number', '4111111111111111');
    $this->type('cvv2', '000');
    $this->select('credit_card_exp_date[M]', 'value=1');
    $this->select('credit_card_exp_date[Y]', 'value=2020');
    $this->type('billing_first_name', 'Jane_' . substr(sha1(rand()), 0, 5));
    $this->type('billing_last_name', 'San_' . substr(sha1(rand()), 0, 5));
    $this->type('billing_street_address-5', '15 Main St.');
    $this->type(' billing_city-5', 'San Jose');
    $this->select('billing_country_id-5', 'value=1228');
    $this->select('billing_state_province_id-5', 'value=1004');
    $this->type('billing_postal_code-5', '94129');

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');
  }

  function _checkConfirmationAndRegister() {
    $confirmStrings = array('Event Fee(s)', 'Billing Name and Address', 'Credit Card Information');
    $this->assertStringsPresent($confirmStrings);
    $this->click('_qf_Confirm_next-bottom');
    $this->waitForPageToLoad('30000');
    $thankStrings = array('Thank You for Registering', 'Event Total', 'Transaction Date');
    $this->assertStringsPresent($thankStrings);
  }
}

