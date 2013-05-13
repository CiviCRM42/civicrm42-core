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
class WebTest_Admin_Form_ScheduleReminderTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testScheduleReminder() {

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

    // Add new Schedule Reminder
    $this->open($this->sboxPath . 'civicrm/admin/scheduleReminders?reset=1');
    $this->open($this->sboxPath . 'civicrm/admin/scheduleReminders?action=add&reset=1');
    $this->waitForElementPresent('_qf_ScheduleReminders_cancel-bottom');

    // Fill Title
    $title = 'Title' . substr(sha1(rand()), 0, 4);
    $this->type('title', $title);

    // Fill Entity Details
    $this->click('entity[0]');
    $this->select('entity[0]', 'label=Activity');
    $this->addSelection('entity[1]', 'label=Meeting');
    $this->addSelection('entity[2]', 'label=Completed');
    $this->select('start_action_offset', 'label=1');
    $this->select('start_action_condition', 'label=after');
    $this->click('is_repeat');
    $this->select('repetition_frequency_interval', 'label=1');
    $this->click('recipient');
    $this->select('recipient', 'label=Activity Assignees');

    // Fill Subject
    $subject = 'subject' . substr(sha1(rand()), 0, 4);
    $this->type('subject', $subject);

    //click on save
    $this->click('_qf_ScheduleReminders_next-bottom');
    $this->waitForPageToLoad('30000');

    $this->click("//div[@id='reminder']//div[@class='dataTables_wrapper']/table/tbody//tr/td[1][text()='{$title}']/../td[7]/span/a[text()='Edit']");
    $this->waitForElementPresent('_qf_ScheduleReminders_cancel-bottom');

    $this->assertEquals($title, $this->getValue('id=title'));
    $this->removeSelection('entity[1]', 'label=Meeting');
    $this->addSelection('entity[1]', 'label=Phone Call');
    $this->addSelection('entity[1]', 'label=Interview');
    $this->removeSelection('entity[2]', 'label=Completed');
    $this->addSelection('entity[2]', 'label=Scheduled');
    $this->addSelection('entity[2]', 'label=Completed');

    $this->assertEquals('1', $this->getSelectedValue('id=start_action_offset'));
    $this->assertEquals('hour', $this->getSelectedValue('id=start_action_unit'));
    $this->assertEquals('after', $this->getSelectedValue('id=start_action_condition'));
    $this->assertEquals('activity_date_time', $this->getSelectedValue('id=start_action_date'));

    $this->assertChecked('is_repeat');

    $this->assertEquals('1', $this->getSelectedValue('id=recipient'));
    $this->assertChecked('is_active');
  }
}

