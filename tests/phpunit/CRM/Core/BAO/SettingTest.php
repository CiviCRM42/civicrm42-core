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
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/


require_once 'CiviTest/CiviUnitTestCase.php';
class CRM_Core_BAO_SettingTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name' => 'Setting BAO',
      'description' => 'Test set/get on setting variables.',
      'group' => 'CiviCRM BAO Tests',
    );
  }

  function setUp() {
    parent::setUp();
    global $civicrm_setting;
    $this->origSetting = $civicrm_setting;
    CRM_Utils_Cache::singleton()->flush();
  }

  function tearDown() {
    global $civicrm_setting;
    $civicrm_setting = $this->origSetting;
    CRM_Utils_Cache::singleton()->flush();
    parent::tearDown();
  }

  function testEnableComponentValid() {
    $config = CRM_Core_Config::singleton(TRUE, TRUE);

    $result = CRM_Core_BAO_ConfigSetting::enableComponent('CiviCampaign');

    $this->assertTrue($result);
  }

  function testEnableComponentAlreadyPresent() {
    $config = CRM_Core_Config::singleton(TRUE, TRUE);

    $result = CRM_Core_BAO_ConfigSetting::enableComponent('CiviCampaign');
    $result = CRM_Core_BAO_ConfigSetting::enableComponent('CiviCampaign');

    $this->assertTrue($result);
  }

  function testEnableComponentInvalid() {
    $config = CRM_Core_Config::singleton(TRUE, TRUE);

    $result = CRM_Core_BAO_ConfigSetting::enableComponent('CiviFake');

    $this->assertFalse($result);
  }

  /**
   * Ensure that overrides in $civicrm_setting apply when
   * using getItem($group,$name).
   */
  function testGetItem_Override() {
    global $civicrm_setting;
    $civicrm_setting[CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME]['imageUploadDir'] = '/test/override';
    $value = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME, 'imageUploadDir');
    $this->assertEquals('/test/override', $value);
  }

  /**
   * Ensure that overrides in $civicrm_setting apply when
   * using getItem($group).
   */
  function testGetItemGroup_Override() {
    global $civicrm_setting;
    $civicrm_setting[CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME]['imageUploadDir'] = '/test/override';
    $values = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME);
    $this->assertEquals('/test/override', $values['imageUploadDir']);
  }

  /**
   * Ensure that overrides in $civicrm_setting apply when
   * when using retrieveDirectoryAndURLPreferences().
   */
  function testRetrieveDirectoryAndURLPreferences_Override() {
    global $civicrm_setting;
    $civicrm_setting[CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME]['imageUploadDir'] = '/test/override';

    $params = array();
    CRM_Core_BAO_Setting::retrieveDirectoryAndURLPreferences($params);
    $this->assertEquals('/test/override', $params['imageUploadDir']);
  }
}

