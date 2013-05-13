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

/**
 * Tests for linking to resource files
 */
class CRM_Core_ResourcesTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name'    => 'Resources',
      'description' => 'Tests for linking to resource files',
      'group'     => 'Core',
    );
  }

  function setUp() {
    parent::setUp();

    $this->res = new CRM_Core_Resources(array(
      'civicrm' => 'http://core-app/',
      '*' => 'http://ext-dir/',
    ));
    CRM_Core_Resources::singleton($this->res);

    // Templates injected into regions should normally be file names, but for unit-testing it's handy to use "string:" notation
    require_once 'CRM/Core/Smarty/resources/String.php';
    civicrm_smarty_register_string_resource( );
  }

  function testAddScriptFile() {
    $this->res
      ->addScriptFile('com.example.ext', 'foo%20bar.js', 0, 'testAddScriptFile')
      ->addScriptFile('com.example.ext', 'foo%20bar.js', 0, 'testAddScriptFile') // extra
      ->addScriptFile('civicrm', 'foo%20bar.js', 0, 'testAddScriptFile')
      ;

    $smarty = CRM_Core_Smarty::singleton();
    $actual = $smarty->fetch('string:{crmRegion name=testAddScriptFile}{/crmRegion}');
    $expected = "" // stable ordering: alphabetical by (snippet.weight,snippet.name)
      . "<script type=\"text/javascript\" src=\"http://core-app/foo%20bar.js\">\n</script>\n"
      . "<script type=\"text/javascript\" src=\"http://ext-dir/com.example.ext/foo%20bar.js\">\n</script>\n"
      ;
    $this->assertEquals($expected, $actual);
  }

  function testAddScriptURL() {
    $this->res
      ->addScriptUrl('/whiz/foo%20bar.js', 0, 'testAddScriptURL')
      ->addScriptUrl('/whiz/foo%20bar.js', 0, 'testAddScriptURL') // extra
      ->addScriptUrl('/whizbang/foo%20bar.js', 0, 'testAddScriptURL')
      ;

    $smarty = CRM_Core_Smarty::singleton();
    $actual = $smarty->fetch('string:{crmRegion name=testAddScriptURL}{/crmRegion}');
    $expected = "" // stable ordering: alphabetical by (snippet.weight,snippet.name)
      . "<script type=\"text/javascript\" src=\"/whiz/foo%20bar.js\">\n</script>\n"
      . "<script type=\"text/javascript\" src=\"/whizbang/foo%20bar.js\">\n</script>\n"
      ;
    $this->assertEquals($expected, $actual);
  }

  function testAddScript() {
    $this->res
      ->addScript('alert("hi");', 0, 'testAddScript')
      ->addScript('alert("there");', 0, 'testAddScript')
      ;

    $smarty = CRM_Core_Smarty::singleton();
    $actual = $smarty->fetch('string:{crmRegion name=testAddScript}{/crmRegion}');
    $expected = ""
      . "<script type=\"text/javascript\">\nalert(\"hi\");\n</script>\n"
      . "<script type=\"text/javascript\">\nalert(\"there\");\n</script>\n"
      ;
    $this->assertEquals($expected, $actual);
  }

  function testCrmJS() {
    $smarty = CRM_Core_Smarty::singleton();

    $actual = $smarty->fetch('string:{crmScript ext=com.example.ext file=foo%20bar.js region=testCrmJS}');
    $this->assertEquals('', $actual);

    $actual = $smarty->fetch('string:{crmScript url=/whiz/foo%20bar.js region=testCrmJS weight=1}');
    $this->assertEquals('', $actual);

    $actual = $smarty->fetch('string:{crmRegion name=testCrmJS}{/crmRegion}');
    $expected = "" // stable ordering: alphabetical by (snippet.weight,snippet.name)
      . "<script type=\"text/javascript\" src=\"http://ext-dir/com.example.ext/foo%20bar.js\">\n</script>\n"
      . "<script type=\"text/javascript\" src=\"/whiz/foo%20bar.js\">\n</script>\n"
      ;
    $this->assertEquals($expected, $actual);
  }

  function testAddStyleFile() {
    $this->res
      ->addStyleFile('com.example.ext', 'foo%20bar.css', 0, 'testAddStyleFile')
      ->addStyleFile('com.example.ext', 'foo%20bar.css', 0, 'testAddStyleFile') // extra
      ->addStyleFile('civicrm', 'foo%20bar.css', 0, 'testAddStyleFile')
      ;

    $smarty = CRM_Core_Smarty::singleton();
    $actual = $smarty->fetch('string:{crmRegion name=testAddStyleFile}{/crmRegion}');
    $expected = "" // stable ordering: alphabetical by (snippet.weight,snippet.name)
      . "<link href=\"http://core-app/foo%20bar.css\" rel=\"stylesheet\" type=\"text/css\"/>\n"
      . "<link href=\"http://ext-dir/com.example.ext/foo%20bar.css\" rel=\"stylesheet\" type=\"text/css\"/>\n"
      ;
    $this->assertEquals($expected, $actual);
  }

  function testAddStyleURL() {
    $this->res
      ->addStyleUrl('/whiz/foo%20bar.css', 0, 'testAddStyleURL')
      ->addStyleUrl('/whiz/foo%20bar.css', 0, 'testAddStyleURL') // extra
      ->addStyleUrl('/whizbang/foo%20bar.css', 0, 'testAddStyleURL')
      ;

    $smarty = CRM_Core_Smarty::singleton();
    $actual = $smarty->fetch('string:{crmRegion name=testAddStyleURL}{/crmRegion}');
    $expected = "" // stable ordering: alphabetical by (snippet.weight,snippet.name)
      . "<link href=\"/whiz/foo%20bar.css\" rel=\"stylesheet\" type=\"text/css\"/>\n"
      . "<link href=\"/whizbang/foo%20bar.css\" rel=\"stylesheet\" type=\"text/css\"/>\n"
      ;
    $this->assertEquals($expected, $actual);
  }

  function testAddStyle() {
    $this->res
      ->addStyle('body { background: black; }', 0, 'testAddStyle')
      ->addStyle('body { text-color: black; }', 0, 'testAddStyle')
      ;

    $smarty = CRM_Core_Smarty::singleton();
    $actual = $smarty->fetch('string:{crmRegion name=testAddStyle}{/crmRegion}');
    $expected = ""
      . "<style type=\"text/css\">\nbody { background: black; }\n</style>\n"
      . "<style type=\"text/css\">\nbody { text-color: black; }\n</style>\n"
      ;
    $this->assertEquals($expected, $actual);
  }

  function testCrmCSS() {
    $smarty = CRM_Core_Smarty::singleton();

    $actual = $smarty->fetch('string:{crmStyle ext=com.example.ext file=foo%20bar.css region=testCrmCSS}');
    $this->assertEquals('', $actual);

    $actual = $smarty->fetch('string:{crmStyle url=/whiz/foo%20bar.css region=testCrmCSS weight=1}');
    $this->assertEquals('', $actual);

    $actual = $smarty->fetch('string:{crmRegion name=testCrmCSS}{/crmRegion}');
    $expected = "" // stable ordering: alphabetical by (snippet.weight,snippet.name)
      . "<link href=\"http://ext-dir/com.example.ext/foo%20bar.css\" rel=\"stylesheet\" type=\"text/css\"/>\n"
      . "<link href=\"/whiz/foo%20bar.css\" rel=\"stylesheet\" type=\"text/css\"/>\n"
      ;
    $this->assertEquals($expected, $actual);
  }

  function testGetURL() {
    $this->assertEquals(
      'http://core-app/dir/file%20name.txt',
      $this->res->getURL('civicrm', 'dir/file%20name.txt')
    );
    $this->assertEquals(
      'http://ext-dir/com.example.ext/dir/file%20name.txt',
      $this->res->getURL('com.example.ext', 'dir/file%20name.txt')
    );
    $this->assertEquals(
      'http://core-app/',
      $this->res->getURL('civicrm')
    );
    $this->assertEquals(
      'http://ext-dir/com.example.ext/',
      $this->res->getURL('com.example.ext')
    );
  }

  function testCrmResURL() {
    $smarty = CRM_Core_Smarty::singleton();

    $actual = $smarty->fetch('string:{crmResURL ext=com.example.ext file=foo%20bar.png}');
    $this->assertEquals('http://ext-dir/com.example.ext/foo%20bar.png', $actual);

    $actual = $smarty->fetch('string:{crmResURL ext=com.example.ext}');
    $this->assertEquals('http://ext-dir/com.example.ext/', $actual);
  }
}
