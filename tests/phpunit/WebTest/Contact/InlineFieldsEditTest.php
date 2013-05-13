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
class WebTest_Contact_InlineFieldsEditTest extends CiviSeleniumTestCase {
  
  protected function setUp() {
    parent::setUp();
  }
  
  function testAddAndEditField() {
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
    
    //adding a contact
    $firstName = 'Anthony' . substr(sha1(rand()), 0, 7);
    $lastName  = 'Anderson' . substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, $lastName);
    
    //email block check
    $this->addEditPhoneEmail();
    
    //phone block check
    $this->addEditPhoneEmail('phone');

    //communications block check
    $this->addCommunicationPreferences($firstName, $lastName);
    
    //demographics block check
    $this->addDemographics();
   
    //custom data check
    $this->fillCustomData();
  }
  
  function addEditPhoneEmail($field = "email") {
    $isEmail = $isPhone = FALSE;
    if ($field == "email") {
      $isEmail = TRUE;
    } elseif ($field == "phone") {
      $isPhone = TRUE;
    }
    $linkText = "add {$field}";
    $this->_checkClickLink($linkText, $field);
    
    //fill the field data
    $loc = array( 1 => 'Home', 2 => 'Work', 3 => 'Main');
    $phoneType = array( 1 => 'Phone', 2 => 'Mobile', 3 => 'Fax');
    //add / delete link check
    $moreFields = 3;
    for ($i = 1; $i <= $moreFields; $i++) {
      $this->click("xpath=//div[@id='{$field}-block']/div/form/table[@class='crm-inline-edit-form']/tbody/tr[2]/td/span[@id='add-more-{$field}']/a");
    }
    $this->click("xpath=//div[@id='{$field}-block']/div/form/table[@class='crm-inline-edit-form']/tbody/tr[5]/td[5]/a");

    $assertValues = array( );
    for ($i = 1; $i <= $moreFields; $i++) {
      $randNumber = rand();
      $inputVal = ($field == "email") ? $randNumber . 'an@example.org' : $randNumber;
          
      if ($isEmail) {
        $this->assertTrue($this->isElementPresent("email[{$i}][on_hold]"));
        $this->assertTrue($this->isElementPresent("Email_{$i}_IsBulkmail"));
        $this->assertTrue($this->isElementPresent("Email_{$i}_IsPrimary"));
      } elseif ($isPhone) {
        $this->assertTrue($this->isElementPresent("phone_2_phone_ext"));
        $this->assertTrue($this->isElementPresent("phone_1_phone_type_id"));
      }
      
      $assertValues[$loc[$i]] = $inputVal;
      $this->select("{$field}_{$i}_location_type_id", "label={$loc[$i]}");
      $this->type("{$field}_{$i}_{$field}", $inputVal);
    }
    $ucFieldName = ucfirst($field);
    $this->click("_qf_{$ucFieldName}_upload");

    //to wait for load effect to end
    $this->waitForElementPresent("xpath=//div[@id='crm-{$field}-content']");
    //checking done for location values
    $i = 1;
    foreach ($assertValues as $location => $value) {
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-label'][$i]", $location ." ". $ucFieldName);
      $primaryClass = "";
      if($i == 1) {
        $key = 1;
        $primaryClass = "primary";
      } else {
        $key = $i - 1;  
      }
      if ($isEmail) {
        $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-content crm-contact_email {$primaryClass}'][$key]/span/a", $value);
      } else {
        $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-content crm-contact_phone {$primaryClass}'][$key]/span", $value);
      }
      $i++;
    }
    
    $linkText = "add or edit {$field}";      
    $this->_checkClickLink($linkText, $field);
    
    //check for values present in edit mode
    for ($i = 1; $i <= $moreFields; $i++) {
      $this->verifySelectedValue("{$field}_{$i}_location_type_id", "{$i}");
      $this->assertTrue(($this->getValue("{$field}_{$i}_{$field}") == $assertValues[$loc[$i]]), "Failed assertion for {$field} field value present in edit mode");
    }
    
    if ($isEmail) {
      $this->click('email[3][on_hold]');
      $this->click('Email_3_IsBulkmail');
      $this->click('_qf_Email_upload');
      sleep(2);
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-label'][3]", "Main Email");
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-content crm-contact_email '][2]/span[@class='email-hold']", preg_quote($assertValues[$loc[3]] .' (On Hold) (Bulk)'));
    } else {
      $this->type("{$field}_2_{$field}_ext", 543);
      $this->select("{$field}_1_{$field}_type_id", "label={$phoneType[2]}");
      $this->click('_qf_Phone_upload');
      sleep(2);
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-label'][1]", "Home " . $phoneType[2]);
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-content crm-contact_phone '][1]/span", preg_quote($assertValues['Work'] ."  ext. ". 543));
    }

  }
  
  function addCommunicationPreferences($firstName, $lastName) {
    $linkText = "add or edit communication preferences";
    $field = "communication-pref";
    $this->_checkClickLink($linkText, $field);
    $privacyOptions = array(
      "do_not_mail" => "Do not mail",
      "do_not_sms" => "Do not sms",                         
      "do_not_trade" => "Do not trade"
    );
   
    foreach ($privacyOptions as $key => $value) {
      $this->check("privacy[{$key}]");
    }
    $this->check("is_opt_out");
    
    $preferedCommunication = array(1 => "Phone", 2 => "Email");
    foreach ($preferedCommunication as $key => $value) {
      $this->check("preferred_communication_method[{$key}]");
    }
    
    $this->assertTrue($this->isElementPresent("preferred_language"), "preferred language field missing");
    $this->assertTrue($this->isElementPresent("preferred_mail_format"), "preferred mail format field missing");
    $this->click("xpath=//span[@id='email_greeting_display']/a");
    $this->select("email_greeting_id", "value=1");        
    $this->click("xpath=//span[@id='postal_greeting_display']/a");
    $this->select("postal_greeting_id", "value=1");
    
    $this->click("xpath=//span[@id='addressee_display']/a");
    $this->select("addressee_id", "value=1");

    $this->click("_qf_CommunicationPreferences_upload");

    //assertions
    $assertValues = array(
      'preferred_communication_method_display' => array('label' => "Preferred Method(s)", 'content' => implode(", ", $preferedCommunication)),
      'preferred_language' => array('label' => "Preferred Language", 'content' => "English (United States)"),
      'preferred_mail_format' => array('label' => "Email Format", 'content' => "Both"),
      'email_greeting_display' => array('label' => "Email Greeting", 'content' => "Dear {$firstName}"),
      'postal_greeting_display' => array('label' => "Postal Greeting", 'content' => "Dear {$firstName}"),
      'addressee_display' => array('label' => "Addressee", 'content' => "{$firstName} {$lastName}")
    );
    //to wait for load effect to end 
    $this->waitForElementPresent("xpath=//div[@id='crm-communication-pref-content']");
    //privacy options check
    $this->verifyText("xpath=//div[@id='crm-communication-pref-content']/div[@class='crm-clear']/div[@class='crm-label']", "Privacy");
    $assertCheck = array_merge($privacyOptions, array("No Bulk Emails (User Opt Out)"));
    $assertCheck = implode("\n ", $assertCheck);
    $this->verifyText("xpath=//div[@id='crm-communication-pref-content']/div[@class='crm-clear']/div[3]", preg_quote("{$assertCheck}"));
    
    $i = 2;
    foreach ($assertValues as $key => $value) {
      $this->verifyText("xpath=//div[@id='crm-communication-pref-content']/div[@class='crm-clear']/div[@class='crm-label'][{$i}]", preg_quote($value['label']));
      $this->verifyText("xpath=//div[@id='crm-communication-pref-content']/div[@class='crm-clear']/div[@class='crm-content crm-contact-{$key}']", preg_quote($value['content']));
      $i++;
    }

    //check for default values set
    $this->_checkClickLink($linkText, $field);
    foreach ($privacyOptions as $key => $value) {
      $this->assertTrue($this->isChecked("privacy[{$key}]"), "{$value} privacy option is not set by default during edit mode");
    }
    $this->assertTrue($this->isChecked("is_opt_out"), "is_opt_out is not set by default during edit mode");
    
    $preferedCommunication = array(1 => "Phone", 2 => "Email");
    foreach ($preferedCommunication as $key => $value) {
      $this->assertTrue($this->isChecked("preferred_communication_method[{$key}]"), "{$value} preferred communication method is not set by default during edit mode");
    }
    $this->verifySelectedValue("postal_greeting_id", "1");
    $this->verifySelectedValue("addressee_id", "1");
    $this->verifySelectedValue("email_greeting_id", "1");
  }

  function addDemographics() {
    $linkText = "add or edit demographics";
    $field = "demographic";

    $this->_checkClickLink($linkText, $field);
    $this->click("civicrm_gender_Male_2");
    $this->webtestFillDate("birth_date", "10 September 1989");
    $this->click("is_deceased");
    $this->click("_qf_Demographics_upload");
    sleep(2);
    $assertValues = array(
      "crm-contact-gender_display" => array("label" => 'Gender', "content" => "Male"),
      "crm-contact-birth_date_display" => array("label" => "Date of birth", "content" => "September 10th, 1989"),
      "crm-contact-deceased_message" => array("label" => "", "content" => "Contact is Deceased")
    );
    
    $i = 1;
    foreach ($assertValues as $key => $value) {
      $this->verifyText("xpath=//div[@id='crm-demographic-content']/div[@class='crm-clear']/div[@class='crm-label'][{$i}]", preg_quote($value['label']));
      $this->verifyText("xpath=//div[@id='crm-demographic-content']/div[@class='crm-clear']/div[@class='crm-content {$key}']", preg_quote($value['content']));
      $i++;
    }
  
    //check for default value
    $this->_checkClickLink($linkText, $field);
    $this->assertTrue($this->isChecked("civicrm_gender_Male_2"), "Gender field is not set");
    $this->assertTrue(($this->getValue("birth_date") == "09/10/1989"), "Birth date is not set expected 09/10/1989");
    $this->assertTrue($this->isChecked("is_deceased"), "Deceased field is not set");
  }
 
  function fillCustomData() {
    $this->verifyText("xpath=//table[@id='constituent_information_1']//div[@class='crm-config-option']/a", "add or edit custom set");
    $this->click("xpath=//table[@id='constituent_information_1']//div[@class='crm-config-option']/a");
    
    $this->click("xpath=//table[@id='constituent_information_1']/tbody/tr/td/a");
    $dateFieldId = $this->getAttribute("xpath=//div[@id='constituent_information']/table/tbody/tr[3]/td[@class='html-adjust']/input@id");
    $this->click("xpath=//div[@id='constituent_information']/table/tbody/tr/td[@class='html-adjust']/input");
    $this->select("xpath=//div[@id='constituent_information']/table/tbody/tr[2]/td[@class='html-adjust']/select", "value=S");
    $this->webtestFillDate($dateFieldId, "1 June 2010");
    $this->click("_qf_CustomData_upload");
    sleep(2);
    
    //assertions
    $assertValues = array(
       1 => array('label' => 'Most Important Issue', 'content' => 'Education'),
       2 => array('label' => 'Marital Status', 'content' => 'Single'),
       3 => array('label' => 'Marriage Date', 'content' => 'June 1st, 2010')
    );
    
    foreach($assertValues as $key => $value) {
      $this->verifyText("xpath=//table[@id='constituent_information_1']/tbody/tr[2]/td/div/div/div/div[@class='crm-clear']/div[@class='crm-label'][{$key}]", preg_quote($value['label']));
      $this->verifyText("xpath=//table[@id='constituent_information_1']/tbody/tr[2]/td/div/div/div/div[@class='crm-clear']/div[@class='crm-content html-adjust crm-custom-data'][$key]", preg_quote($value['content']));
    }
    
    $this->verifyText("xpath=//table[@id='constituent_information_1']//div[@class='crm-config-option']/a", "add or edit custom set");
    $this->click("xpath=//table[@id='constituent_information_1']//div[@class='crm-config-option']/a");
    $this->click("xpath=//table[@id='constituent_information_1']/tbody/tr/td/a");
    
    //assert for default values
    $this->assertTrue($this->isChecked("xpath=//div[@id='constituent_information']/table/tbody/tr/td[@class='html-adjust']/input"), "custom field value not set in edit mode");
    $this->verifySelectedValue("xpath=//div[@id='constituent_information']/table/tbody/tr[2]/td[@class='html-adjust']/select", "S");
    $this->assertTrue(($this->getValue("{$dateFieldId}") == "06/01/2010"), "Constituent information date field not set in edit mode");
    $this->click('_qf_CustomData_cancel');
  }
  
  function _checkClickLink($linkText, $field) {
    //check element presence
    $text = $this->getText("xpath=//div[@id='{$field}-block']//div[@id='crm-{$field}-content']//a[@id='edit-{$field}']");
    $this->assertTrue((($text == $linkText) && $this->isElementPresent("xpath=//div[@id='{$field}-block']//div[@id='crm-{$field}-content']//a[@id='edit-{$field}']")), "'{$linkText}' link text: {$text} missing on contact summary page");
    $this->click("xpath=//div[@id='{$field}-block']//div[@id='crm-{$field}-content']//a[@id='edit-{$field}']");
  }
}