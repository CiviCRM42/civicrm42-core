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
/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_Core_DAO_Batch extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_batch';
    /**
     * static instance to hold the field values
     *
     * @var array
     * @static
     */
    static $_fields = null;
    /**
     * static instance to hold the FK relationships
     *
     * @var string
     * @static
     */
    static $_links = null;
    /**
     * static instance to hold the values that can
     * be imported
     *
     * @var array
     * @static
     */
    static $_import = null;
    /**
     * static instance to hold the values that can
     * be exported
     *
     * @var array
     * @static
     */
    static $_export = null;
    /**
     * static value to see if we should log any modifications to
     * this table in the civicrm_log table
     *
     * @var boolean
     * @static
     */
    static $_log = false;
    /**
     * Unique Address ID
     *
     * @var int unsigned
     */
    public $id;
    /**
     * Variable name/programmatic handle for this batch.
     *
     * @var string
     */
    public $name;
    /**
     * Friendly Name.
     *
     * @var string
     */
    public $title;
    /**
     * Description of this batch set.
     *
     * @var text
     */
    public $description;
    /**
     * FK to Contact ID
     *
     * @var int unsigned
     */
    public $created_id;
    /**
     * When was this item created
     *
     * @var datetime
     */
    public $created_date;
    /**
     * FK to Contact ID
     *
     * @var int unsigned
     */
    public $modified_id;
    /**
     * When was this item created
     *
     * @var datetime
     */
    public $modified_date;
    /**
     * FK to Saved Search ID
     *
     * @var int unsigned
     */
    public $saved_search_id;
    /**
     * fk to Batch Status options in civicrm_option_values
     *
     * @var int unsigned
     */
    public $status_id;
    /**
     * fk to Batch Type options in civicrm_option_values
     *
     * @var int unsigned
     */
    public $type_id;
    /**
     * fk to Batch mode options in civicrm_option_values
     *
     * @var int unsigned
     */
    public $mode_id;
    /**
     * Total amount for this batch.
     *
     * @var float
     */
    public $total;
    /**
     * Number of items in a batch.
     *
     * @var int unsigned
     */
    public $item_count;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_batch
     */
    function __construct()
    {
        $this->__table = 'civicrm_batch';
        parent::__construct();
    }
    /**
     * return foreign links
     *
     * @access public
     * @return array
     */
    function links()
    {
        if (!(self::$_links)) {
            self::$_links = array(
                'created_id' => 'civicrm_contact:id',
                'modified_id' => 'civicrm_contact:id',
                'saved_search_id' => 'civicrm_saved_search:id',
            );
        }
        return self::$_links;
    }
    /**
     * returns all the column names of this table
     *
     * @access public
     * @return array
     */
    static function &fields()
    {
        if (!(self::$_fields)) {
            self::$_fields = array(
                'id' => array(
                    'name' => 'id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                ) ,
                'name' => array(
                    'name' => 'name',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Name') ,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
                ) ,
                'title' => array(
                    'name' => 'title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Title') ,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
                ) ,
                'description' => array(
                    'name' => 'description',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Description') ,
                    'rows' => 4,
                    'cols' => 80,
                ) ,
                'created_id' => array(
                    'name' => 'created_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Contact_DAO_Contact',
                ) ,
                'created_date' => array(
                    'name' => 'created_date',
                    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
                    'title' => ts('Created Date') ,
                ) ,
                'modified_id' => array(
                    'name' => 'modified_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Contact_DAO_Contact',
                ) ,
                'modified_date' => array(
                    'name' => 'modified_date',
                    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
                    'title' => ts('Modified Date') ,
                ) ,
                'saved_search_id' => array(
                    'name' => 'saved_search_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Contact_DAO_SavedSearch',
                ) ,
                'status_id' => array(
                    'name' => 'status_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                ) ,
                'type_id' => array(
                    'name' => 'type_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                ) ,
                'mode_id' => array(
                    'name' => 'mode_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'total' => array(
                    'name' => 'total',
                    'type' => CRM_Utils_Type::T_MONEY,
                    'title' => ts('Total') ,
                ) ,
                'item_count' => array(
                    'name' => 'item_count',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Item Count') ,
                    'required' => true,
                ) ,
            );
        }
        return self::$_fields;
    }
    /**
     * returns the names of this table
     *
     * @access public
     * @static
     * @return string
     */
    static function getTableName()
    {
        return CRM_Core_DAO::getLocaleTableName(self::$_tableName);
    }
    /**
     * returns if this table needs to be logged
     *
     * @access public
     * @return boolean
     */
    function getLog()
    {
        return self::$_log;
    }
    /**
     * returns the list of fields that can be imported
     *
     * @access public
     * return array
     * @static
     */
    static function &import($prefix = false)
    {
        if (!(self::$_import)) {
            self::$_import = array();
            $fields = self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('import', $field)) {
                    if ($prefix) {
                        self::$_import['batch'] = & $fields[$name];
                    } else {
                        self::$_import[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_import;
    }
    /**
     * returns the list of fields that can be exported
     *
     * @access public
     * return array
     * @static
     */
    static function &export($prefix = false)
    {
        if (!(self::$_export)) {
            self::$_export = array();
            $fields = self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('export', $field)) {
                    if ($prefix) {
                        self::$_export['batch'] = & $fields[$name];
                    } else {
                        self::$_export[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
}
