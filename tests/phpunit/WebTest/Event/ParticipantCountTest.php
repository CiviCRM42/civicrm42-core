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
class WebTest_Event_ParticipantCountTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testParticipantCountWithFeelevel() {
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    // We need a payment processor
    $processorName = 'Webtest Dummy' . substr(sha1(rand()), 0, 7);
    $this->webtestAddPaymentProcessor($processorName);

    // create an event
    $eventTitle = 'A Conference - ' . substr(sha1(rand()), 0, 7);
    $paramsEvent = array(
      'title' => $eventTitle,
      'template_id' => 6,
      'event_type_id' => 4,
      'payment_processor' => $processorName,
      'fee_level' => array(
        'Member' => '250.00',
        'Non-Member' => '325.00',
      ),
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
    $this->click("xpath=//input[@class='form-radio']");

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing details and register
    $this->_testRegisterWithBillingInfo();

    // Register Participant 2
    // visit event info page
    $this->open($infoEvent);

    // register for event
    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    $this->click("xpath=//input[@class='form-radio']");
    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    // fill billing details and register
    $this->_testRegisterWithBillingInfo();

    // login to check participant count
    $this->open($this->sboxPath);
    $this->webtestLogin();

    // Find Participant
    $this->open($this->sboxPath . 'civicrm/event/search?reset=1');
    $this->waitForElementPresent('participant_fee_amount_low');
    $this->click("event_name");
    $this->type("event_name", $eventTitle);
    $this->typeKeys("event_name", $eventTitle);
    $this->waitForElementPresent("css=div.ac_results-inner li");
    $this->click("css=div.ac_results-inner li");
    $this->click('_qf_Search_refresh');
    $this->waitForPageToLoad('30000');

    // verify number of registered participants
    $this->assertStringsPresent(array('2 Result'));
  }

  function testParticipantCountWithPriceset() {
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
        'count' => '2',
      ),
      'Meal Choice' => array(
        'type' => 'Select',
        'options' => array(
          1 => array('label' => 'Chicken',
            'amount' => '525.00',
            'count' => '2',
          ),
          2 => array(
            'label' => 'Vegetarian',
            'amount' => '200.00',
            'count' => '2',
          ),
        ),
      ),
      'Pre-conference Meetup?' => array(
        'type' => 'Radio',
        'options' => array(
          1 => array('label' => 'Yes',
            'amount' => '50.00',
            'count' => '2',
          ),
          2 => array(
            'label' => 'No',
            'amount' => '0',
          ),
        ),
      ),
      'Evening Sessions' => array(
        'type' => 'CheckBox',
        'options' => array(
          1 => array('label' => 'First Five',
            'amount' => '100.00',
            'count' => '5',
          ),
          2 => array(
            'label' => 'Second Four',
            'amount' => '50.00',
            'count' => '4',
          ),
        ),
      ),
    );

    foreach ($fields as $label => $field) {

      $this->type('label', $label);
      $this->select('html_type', "value={$field['type']}");

      if ($field['type'] == 'Text') {
        $this->type('price', $field['amount']);
        //yash
        $this->waitForElementPresent('count');
        $this->type('count', $field['count']);
        $this->check('is_required');
      }
      else {
        $this->_testAddMultipleChoiceOptions($field['options']);
      }
      $this->click('_qf_Field_next_new-bottom');
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_Field_next-bottom');
    }

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

    $priceFieldOptionCounts = $participants = array();

    // Register Participant 1
    // visit event info page
    $this->open($infoEvent);
    $this->waitForPageToLoad('30000');

    // register for event
    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    $this->type("xpath=//input[@class='form-text four required']", '1');

    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    $participants[1] = array(
      'email' => $email,
      'first_name' => 'Jane_' . substr(sha1(rand()), 0, 5),
      'last_name' => 'San_' . substr(sha1(rand()), 0, 5),
    );

    // fill billing related info and register
    $this->_testRegisterWithBillingInfo($participants[1]);

    // Options filled by 1st participants.
    $priceFieldOptionCounts[1] = array(
      'Full Conference' => 1,
      'Meal Choice - Chicken' => 1,
      'Meal Choice - Vegetarian' => 0,
      'Pre-conference Meetup? - Yes' => 1,
      'Pre-conference Meetup? - No' => 0,
      'Evening Sessions - First Five' => 1,
      'Evening Sessions - Second Four' => 0,
    );

    // Register Participant 1
    // visit event info page
    $this->open($infoEvent);

    // register for event
    $this->click('link=Register Now');
    $this->waitForElementPresent('_qf_Register_upload-bottom');

    $this->type("xpath=//input[@class='form-text four required']", '2');
    $email = 'jane_' . substr(sha1(rand()), 0, 5) . '@example.org';
    $this->type('email-Primary', $email);

    $participants[2] = array(
      'email' => $email,
      'first_name' => 'Jane_' . substr(sha1(rand()), 0, 5),
      'last_name' => 'San_' . substr(sha1(rand()), 0, 5),
    );

    // fill billing related info and register
    $this->_testRegisterWithBillingInfo($participants[2]);

    // Options filled by 2nd participants.
    $priceFieldOptionCounts[2] = array(
      'Full Conference' => 2,
      'Meal Choice - Chicken' => 1,
      'Meal Choice - Vegetarian' => 0,
      'Pre-conference Meetup? - Yes' => 1,
      'Pre-conference Meetup? - No' => 0,
      'Evening Sessions - First Five' => 1,
      'Evening Sessions - Second Four' => 0,
    );

    // login to check participant count
    $this->open($this->sboxPath);
    $this->webtestLogin();

    // Find Participant
    $this->open($this->sboxPath . 'civicrm/event/search?reset=1');
    $this->waitForElementPresent('participant_fee_amount_low');
    $this->click("event_name");
    $this->type("event_name", $eventTitle);
    $this->typeKeys("event_name", $eventTitle);
    $this->waitForElementPresent("css=div.ac_results-inner li");
    $this->click("css=div.ac_results-inner li");
    $this->click('_qf_Search_refresh');
    $this->waitForPageToLoad('30000');

    // verify number of participants records and total participant count
    $this->assertStringsPresent(array('2 Result', 'Actual participant count : 24'));

    // CRM-7953, check custom search Price Set Details for Event
    // Participants
    $this->_testPricesetDetailsCustomSearch($paramsEvent, $participants, $priceFieldOptionCounts);
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

  function _testAddMultipleChoiceOptions($options) {
    foreach ($options as $oIndex => $oValue) {
      $this->type("option_label_{$oIndex}", $oValue['label']);
      $this->type("option_amount_{$oIndex}", $oValue['amount']);
      if (array_key_exists('count', $oValue)) {
        $this->waitForElementPresent("option_count_{$oIndex}");
        $this->type("option_count_{$oIndex}", $oValue['count']);
      }
      $this->click('link=another choice');
    }
    $this->click('CIVICRM_QFID_1_2');
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
    $this->click("xpath=//tr[@class='crm-event-manage-fee-form-block-payment_processor']/td[2]/label[text()='" . $params['payment_processor'] . "']");
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

  function _testRegisterWithBillingInfo($participant = array(
    )) {
    $this->waitForElementPresent("credit_card_type");
    $this->select('credit_card_type', 'value=Visa');
    $this->type('credit_card_number', '4111111111111111');
    $this->type('cvv2', '000');
    $this->select('credit_card_exp_date[M]', 'value=1');
    $this->select('credit_card_exp_date[Y]', 'value=2020');
    $this->type('billing_first_name', isset($participant['first_name']) ? $participant['first_name'] : 'Jane_' . substr(sha1(rand()), 0, 5));
    $this->type('billing_last_name', isset($participant['last_name']) ? $participant['last_name'] : 'San_' . substr(sha1(rand()), 0, 5));
    $this->type('billing_street_address-5', '15 Main St.');
    $this->type(' billing_city-5', 'San Jose');
    $this->select('billing_country_id-5', 'value=1228');
    $this->select('billing_state_province_id-5', 'value=1004');
    $this->type('billing_postal_code-5', '94129');

    $this->click('_qf_Register_upload-bottom');
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent('_qf_Confirm_next-bottom');
    $confirmStrings = array('Event Fee(s)', 'Billing Name and Address', 'Credit Card Information');
    $this->assertStringsPresent($confirmStrings);
    $this->click('_qf_Confirm_next-bottom');
    $this->waitForPageToLoad('30000');
    $thankStrings = array('Thank You for Registering', 'Event Total', 'Transaction Date');
    $this->assertStringsPresent($thankStrings);
  }

  function _testPricesetDetailsCustomSearch($eventParams, $participants, $priceFieldOptionCounts) {
    $this->open($this->sboxPath . 'civicrm/contact/search/custom?csid=9&reset=1');
    $this->waitForPageToLoad('30000');

    $this->select('event_id', 'label=' . $eventParams['title']);
    $this->click('_qf_Custom_refresh-bottom');
    $this->waitForPageToLoad('30000');

    $tableHeaders = array('Contact Id', 'Participant Id', 'Name');
    $tableHeaders = array_merge($tableHeaders, array_keys(current($priceFieldOptionCounts)));

    $tdnum = 2;
    foreach ($tableHeaders as $header) {
      $this->verifyText("xpath=//form[@id='Custom']//div[@class='crm-search-results']//table[@class='selector']/thead/tr[1]/th[$tdnum]", $header);
      $tdnum++;
    }

    foreach ($participants as $participantNum => $participant) {
      $tdnum = 4;
      $this->verifyText("xpath=//form[@id='Custom']//div[@class='crm-search-results']//table[@class='selector']/tbody/tr[{$participantNum}]/td[{$tdnum}]", preg_quote("{$participant['first_name']} {$participant['last_name']}"));
      foreach ($priceFieldOptionCounts[$participantNum] as $priceFieldOptionCount) {
        $tdnum++;
        $this->verifyText("xpath=//form[@id='Custom']//div[@class='crm-search-results']//table[@class='selector']/tbody/tr[{$participantNum}]/td[{$tdnum}]", preg_quote($priceFieldOptionCount));
      }
    }
  }
}

