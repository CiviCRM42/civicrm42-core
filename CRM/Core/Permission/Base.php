<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 *
 */
class CRM_Core_Permission_Base {

  /**
   * Translate permission
   *
   * @param string $name e.g. "administer CiviCRM", "cms:access user record", "Drupal:administer content", "Joomla:action:com_asset"
   * @param string $nativePrefix
   * @param array $map array($portableName => $nativeName)
   *
   * @return NULL|string a permission name
   */
  static public function translatePermission($perm, $nativePrefix, $map) {
    list ($civiPrefix, $name) = CRM_Utils_String::parsePrefix(':', $perm, NULL);
    switch ($civiPrefix) {
      case $nativePrefix:
        return $name; // pass through
      case 'cms':
        return CRM_Utils_Array::value($name, $map, CRM_Core_Permission::ALWAYS_DENY_PERMISSION);
      case NULL:
        return $name;
      default:
        return CRM_Core_Permission::ALWAYS_DENY_PERMISSION;
    }
  }
}
