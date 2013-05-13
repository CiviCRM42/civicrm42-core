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
class WebTest_Admin_RelationshipTypeAddTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testRelationshipTypeAdd() {

    $this->open($this->sboxPath);
    $this->webtestLogin();
    $this->waitForPageToLoad('30000');

    $this->click('link=CiviCRM');
    $this->waitForPageToLoad('30000');

    //jump directly to relationship type selector.
    $this->open($this->sboxPath . 'civicrm/admin/reltype?reset=1&action=browse');
    $this->waitForPageToLoad('30000');

    //load the form to add new relationship type.
    $this->click('link=Add Relationship Type');
    $this->waitForPageToLoad('30000');

    //enter the relationship type values.
    $labelAB = 'Test Relationship Type A - B -' . rand();
    $labelBA = 'Test Relationship Type B - A -' . rand();
    $this->type('label_a_b', $labelAB);
    $this->type('label_b_a', $labelBA);
    $this->select('contact_types_a', "value=Individual");
    $this->select('contact_types_b', "value=Individual");
    $this->type('description', 'Test Relationship Type Description');

    //save the data.
    $this->click('_qf_RelationshipType_next-bottom');
    $this->waitForPageToLoad('30000');

    //does data saved.
    $this->assertTrue($this->isTextPresent('The Relationship Type has been saved.'),
      "Status message didn't show up after saving!"
    );

    //validate data.
    $data = array(
      'Relationship A to B' => $labelAB,
      'Relationship B to A' => $labelBA,
      'Contact Type A' => 'Individual',
      'Contact Type B' => 'Individual',
    );
    foreach ($data as $param => $val) {
      $this->assertTrue($this->isTextPresent($val), "Could not able to save $param");
    }
  }

  function testRelationshipTypeAddValidateFormRules() {

    $this->open($this->sboxPath);
    $this->webtestLogin();
    $this->waitForPageToLoad('30000');

    $this->click('link=CiviCRM');
    $this->waitForPageToLoad('30000');

    //jump directly to relationship type selector.
    $this->open($this->sboxPath . 'civicrm/admin/reltype?reset=1&action=browse');
    $this->waitForPageToLoad('30000');

    //validate form rules.
    $this->click('link=Add Relationship Type');
    $this->waitForPageToLoad('30000');

    $this->select('contact_types_a', 'value=Individual');
    $this->select('contact_types_b', 'value=Individual');
    $description = 'Test Relationship Type Description';
    $this->type('description', $description);

    $this->click('_qf_RelationshipType_next-bottom');
    $this->waitForPageToLoad('30000');
    $this->assertTrue($this->isTextPresent('Relationship Label-A to B is a required field.'),
      'Required form rule for Label A - B seems to be broken.'
    );

    //enter the relationship type values.
    $labelAB = 'Test Relationship Type A - B - DUPLICATE TO BE' . rand();
    $labelBA = 'Test Relationship Type B - A - DUPLICATE TO BE' . rand();
    $this->type('label_a_b', $labelAB);
    $this->type('label_b_a', $labelBA);
    $this->select('contact_types_a', "value=Individual");
    $this->select('contact_types_b', "value=Individual");
    $this->type('description', 'Test Relationship Type Description');
    $this->click('_qf_RelationshipType_next-bottom');
    $this->waitForPageToLoad('30000');

    $this->open($this->sboxPath . 'civicrm/admin/reltype?reset=1&action=browse');
    $this->waitForPageToLoad('30000');
    $this->click('link=Add Relationship Type');
    $this->waitForPageToLoad('30000');

    $this->type('label_a_b', $labelAB);
    $this->type('label_b_a', $labelBA);
    $this->click('_qf_RelationshipType_next-bottom');
    $this->waitForPageToLoad('30000');
    $this->assertTrue($this->isTextPresent('Label already exists in Database.'),
      'Unique relationship type label form rule seems to be broken.'
    );
  }
}

