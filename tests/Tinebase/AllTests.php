<?php
/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     Tinebase
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Matthias Greiling <m.greiling@metaways.de>
 * @version     $Id$
 */

/**
 * Test helper
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Tinebase_AllTests::main');
}
class Tinebase_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Tine 2.0 Tinebase All Tests');
        $suite->addTestSuite('Tinebase_Record_RecordTest');
        $suite->addTestSuite('Tinebase_Record_RecordSetTest');
        //$suite->addTestSuite('Tinebase_Record_RelationTest');
        
        //$suite->addTestSuite('Tinebase_Record_AbstractRecordTest');
        //$suite->addTestSuite('Tinebase_Record_ContainerTest');
        return $suite;
    }
}
if (PHPUnit_MAIN_METHOD == 'Tinebase_AllTests::main') {
    Tinebase_AllTests::main();
}
