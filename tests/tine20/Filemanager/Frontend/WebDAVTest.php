<?php

use Sabre\DAV;

/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     Tinebase
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2010-2010 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 */

/**
 * Test helper
 */
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Filemanager_Frontend_WebDAVTest::main');
}

/**
 * Test class for Filemanager_Frontend_Tree
 * 
 * @package     Tinebase
 */
class Filemanager_Frontend_WebDAVTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array test objects
     */
    protected $objects = array();

    /**
     * Tree
     *
     * @var Sabre\DAV\ObjectTree
     */
    protected $_webdavTree;
    
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite('Tine 2.0 webdav tree tests');
        PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        Tinebase_TransactionManager::getInstance()->startTransaction(Tinebase_Core::getDb());
        
        $this->_webdavTree = new DAV\ObjectTree(new Tinebase_WebDav_Root());
    }

    /**
     * Tears down the fixture
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
        Tinebase_TransactionManager::getInstance()->rollBack();
        
        unset($_SERVER['HTTP_OC_CHUNKED']);
        unset($_SERVER['CONTENT_LENGTH']);
    }
    
    /**
     * testgetNodeForPath
     */
    public function testgetNodeForPath()
    {
        $node = $this->_webdavTree->getNodeForPath(null);
        
        $this->assertTrue($node instanceof Tinebase_WebDav_Root, 'wrong node class');
        
        $children = $node->getChildren();
        
        $this->setExpectedException('Sabre\DAV\Exception\Forbidden');
        
        $this->_webdavTree->delete('/');
    }
    
    public function testgetNodeForPath_webdav()
    {
        $node = $this->_webdavTree->getNodeForPath('/webdav');
        
        $this->assertTrue($node instanceof DAV\SimpleCollection, 'wrong node class');
        $this->assertEquals('webdav', $node->getName());
        
        $children = $node->getChildren();
        
        $this->assertTrue($children[0] instanceof Tinebase_WebDav_Collection_Abstract, 'wrong child class');
        
        $this->setExpectedException('Sabre\DAV\Exception\Forbidden');
        
        $this->_webdavTree->delete('/webdav');
    }
    
    public function testgetNodeForPath_webdav_filemanager()
    {
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager');
        
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV, 'wrong node class');
        $this->assertEquals('Filemanager', $node->getName());
        
        $children = $node->getChildren();
        
        $this->assertEquals(2, count($children));
        $this->assertEquals('Filemanager_Frontend_WebDAV', get_class($children[0]), 'wrong child class');
        
        $this->setExpectedException('Sabre\DAV\Exception\Forbidden');
        
        $this->_webdavTree->delete('/webdav/Filemanager');
    }
    
    public function testgetNodeForPath_webdav_filemanager_personal()
    {
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/personal');
        
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV, 'wrong node class');
        $this->assertEquals('personal', $node->getName());
        
        $children = $node->getChildren();
        
        $this->assertEquals(1, count($children));
        $this->assertEquals('Filemanager_Frontend_WebDAV', get_class($children[0]), 'wrong child class');
        
        $this->setExpectedException('Sabre\DAV\Exception\Forbidden');
        
        $this->_webdavTree->delete('/webdav/Filemanager/personal');
    }
    
    public function testgetNodeForPath_webdav_filemanager_personal_currentuser()
    {
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/personal/' . Tinebase_Core::getUser()->accountLoginName);
        
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV, 'wrong node class');
        $this->assertEquals(Tinebase_Core::getUser()->accountLoginName, $node->getName());
        
        $children = $node->getChildren();
        
        $this->assertGreaterThanOrEqual(1, count($children));
        $this->assertTrue($children[0] instanceof Filemanager_Frontend_WebDAV_Container, 'wrong child class');
        
        $this->setExpectedException('Sabre\DAV\Exception\Forbidden');
        
        $this->_webdavTree->delete('/webdav/Filemanager/personal/' . Tinebase_Core::getUser()->accountLoginName);
    }
    
    /**
     * @return Filemanager_Frontend_WebDAV_Directory
     */
    public function testgetNodeForPath_webdav_filemanager_personal_currentuser_unittestdirectory()
    {
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/personal/' . Tinebase_Core::getUser()->accountLoginName);
        
        $node->createDirectory('unittestdirectory');
        
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/personal/' . Tinebase_Core::getUser()->accountLoginName .'/unittestdirectory');
        
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV_Container, 'wrong node class');
        $this->assertEquals('unittestdirectory', $node->getName());
        
        $children = $this->_webdavTree->getChildren('/webdav/Filemanager/personal/' . Tinebase_Core::getUser()->accountLoginName);
        foreach ($children as $node) {
            $names[] = $node->getName();
        }
        $this->assertContains('unittestdirectory', $names);
        
        $this->_webdavTree->delete('/webdav/Filemanager/personal/' . Tinebase_Core::getUser()->accountLoginName .'/unittestdirectory');
        
        $this->setExpectedException('Sabre\DAV\Exception\NotFound');
        
        $this->_webdavTree->getNodeForPath('/webdav/Filemanager/personal/' . Tinebase_Core::getUser()->accountLoginName .'/unittestdirectory');
    }
    
    public function testgetNodeForPath_webdav_filemanager_shared()
    {
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/shared');
        
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV, 'wrong node class');
        $this->assertEquals('shared', $node->getName());
        
        $children = $node->getChildren();
        
        $this->setExpectedException('Sabre\DAV\Exception\Forbidden');
        
        $this->_webdavTree->delete('/webdav/Filemanager/shared');
    }
    
    /**
     * testgetNodeForPath_webdav_filemanager_shared_unittestdirectory
     * 
     * @return Filemanager_Frontend_WebDAV_Container
     */
    public function testgetNodeForPath_webdav_filemanager_shared_unittestdirectory()
    {
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/shared');
        
        $node->createDirectory('unittestdirectory');
        
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/shared/unittestdirectory');
        
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV_Container, 'wrong node class');
        $this->assertEquals('unittestdirectory', $node->getName());
        
        $children = $node->getChildren();
        
        $properties = $node->getProperties(array());
        
        return $node;
    }
    
    /**
     * @return Filemanager_Frontend_WebDAV_File
     */
    public function testgetNodeForPath_webdav_filemanager_shared_unittestdirectory_file()
    {
        $parent = $this->testgetNodeForPath_webdav_filemanager_shared_unittestdirectory();
        
        $etag = $parent->createFile('tine_logo.png', fopen(dirname(__FILE__) . '/../../Tinebase/files/tine_logo.png', 'r'));
        
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/shared/unittestdirectory/tine_logo.png');
        
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV_File, 'wrong node class: ' . get_class($node));
        $this->assertTrue(is_resource($node->get()));
        $this->assertEquals('tine_logo.png', $node->getName());
        $this->assertEquals(7246, $node->getSize());
        $this->assertEquals('image/png', $node->getContentType());
        $this->assertEquals('"7424e2c16388bf388af1c4fe44c1dd67d31f468b"', $node->getETag());
        $this->assertTrue(preg_match('/"\w+"/', $etag) === 1);
        
        return $node;
    }
    
    public function testUpdateFile()
    {
        $node = $this->testgetNodeForPath_webdav_filemanager_shared_unittestdirectory_file();
        
        $etag = $node->put(fopen(dirname(__FILE__) . '/../../Tinebase/files/tine_logo.png', 'r'));
        
        $this->assertEquals('Filemanager_Frontend_WebDAV_File', get_class($node), 'wrong type');
        $this->assertTrue(preg_match('/"\w+"/', $etag) === 1);
    }
    
    /**
     * test chunked upload from OwnCloud clients
     * 
     * @return Filemanager_Frontend_WebDAV_File
     */
    public function testOwnCloudChunkedUpload()
    {
        $_SERVER['HTTP_OC_CHUNKED'] = 1;
        
        $fileStream = fopen(dirname(__FILE__) . '/../../Tinebase/files/tine_logo.png', 'r');
        
        $parent = $this->testgetNodeForPath_webdav_filemanager_shared_unittestdirectory();
        
        // upload first chunk
        $tempStream = fopen('php://temp', 'w');
        $_SERVER['CONTENT_LENGTH'] = stream_copy_to_stream($fileStream, $tempStream, 1000);
        rewind($tempStream);
        $etag = $parent->createFile('tine_logo.png-chunking-1000-3-0', $tempStream);
        fclose($tempStream);
        
        // upload second chunk
        $tempStream = fopen('php://temp', 'w');
        $_SERVER['CONTENT_LENGTH'] = stream_copy_to_stream($fileStream, $tempStream, 1000);
        rewind($tempStream);
        $etag = $parent->createFile('tine_logo.png-chunking-1000-3-1', $tempStream);
        fclose($tempStream);
        
        // upload last chunk
        $tempStream = fopen('php://temp', 'w');
        $_SERVER['CONTENT_LENGTH'] = stream_copy_to_stream($fileStream, $tempStream);
        rewind($tempStream);
        $etag = $parent->createFile('tine_logo.png-chunking-1000-3-2', $tempStream);
        fclose($tempStream);
        fclose($fileStream);
        
        // retrieve final node
        $this->_webdavTree->markDirty('/webdav/Filemanager/shared/unittestdirectory/tine_logo.png');
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/shared/unittestdirectory/tine_logo.png');
        
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV_File, 'wrong node class: ' . get_class($node));
        $this->assertTrue(is_resource($node->get()));
        $this->assertEquals('tine_logo.png', $node->getName());
        $this->assertEquals(7246, $node->getSize());
        $this->assertEquals('image/png', $node->getContentType());
        $this->assertEquals('"7424e2c16388bf388af1c4fe44c1dd67d31f468b"', $node->getETag());
        $this->assertTrue(preg_match('/"\w+"/', $etag) === 1);
        
        $fileStream = fopen(dirname(__FILE__) . '/../../Tinebase/files/tine_logo_setup.png', 'r');
        
        // upload first chunk
        $tempStream = fopen('php://temp', 'w');
        $_SERVER['CONTENT_LENGTH'] = stream_copy_to_stream($fileStream, $tempStream, 1000);
        rewind($tempStream);
        $etag = $parent->createFile('tine_logo.png-chunking-1001-3-0', $tempStream);
        fclose($tempStream);
        
        // upload second chunk
        $tempStream = fopen('php://temp', 'w');
        $_SERVER['CONTENT_LENGTH'] = stream_copy_to_stream($fileStream, $tempStream, 1000);
        rewind($tempStream);
        $etag = $parent->createFile('tine_logo.png-chunking-1001-3-1', $tempStream);
        fclose($tempStream);
        
        // upload last chunk
        $tempStream = fopen('php://temp', 'w');
        $_SERVER['CONTENT_LENGTH'] = stream_copy_to_stream($fileStream, $tempStream);
        rewind($tempStream);
        $etag = $parent->createFile('tine_logo.png-chunking-1001-3-2', $tempStream);
        fclose($tempStream);
        
        // retrieve final node
        $this->_webdavTree->markDirty('/webdav/Filemanager/shared/unittestdirectory/tine_logo.png');
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/shared/unittestdirectory/tine_logo.png');
        
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV_File, 'wrong node class: ' . get_class($node));
        $this->assertTrue(is_resource($node->get()));
        $this->assertEquals('tine_logo.png', $node->getName());
        $this->assertEquals(7258, $node->getSize());
        $this->assertEquals('image/png', $node->getContentType());
        $this->assertEquals('"434f1e3474a04ce3a10febad78a64e65d7b4f531"', $node->getETag());
        $this->assertTrue(preg_match('/"\w+"/', $etag) === 1);
        
        return $node;
    }
    
    public function testDeleteFile()
    {
        $node = $this->testgetNodeForPath_webdav_filemanager_shared_unittestdirectory_file();
    
        $this->_webdavTree->delete('/webdav/Filemanager/shared/unittestdirectory/tine_logo.png');
    
        $this->setExpectedException('Sabre\DAV\Exception\NotFound');
        
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/shared/unittestdirectory/tine_logo.png');
    }
    
    /**
     * @return Filemanager_Frontend_WebDAV_Directory
     */
    public function testgetNodeForPath_webdav_filemanager_shared_unittestdirectory_directory()
    {
        $parent = $this->testgetNodeForPath_webdav_filemanager_shared_unittestdirectory();
    
        $file = $parent->createDirectory('directory');
    
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/shared/unittestdirectory/directory');
    
        $this->assertTrue($node instanceof Filemanager_Frontend_WebDAV_Directory, 'wrong node class');
        $this->assertEquals('directory', $node->getName());
            
        return $node;
    }
    
    public function testgetNodeForPath_invalidApplication()
    {
        $this->setExpectedException('Sabre\DAV\Exception\NotFound');
        
        $node = $this->_webdavTree->getNodeForPath('/webdav/invalidApplication');
    }
    
    public function testgetNodeForPath_invalidContainerType()
    {
        $this->setExpectedException('Sabre\DAV\Exception\NotFound');
        
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/invalidContainerType');
    }
    
    public function testgetNodeForPath_invalidFolder()
    {
        $this->setExpectedException('Sabre\DAV\Exception\NotFound');
        
        $node = $this->_webdavTree->getNodeForPath('/webdav/Filemanager/shared/invalidContainer');
    }    
}        
    

if (PHPUnit_MAIN_METHOD == 'Filemanager_Frontend_WebDAVTest::main') {
    Filemanager_Frontend_WebDAVTest::main();
}
