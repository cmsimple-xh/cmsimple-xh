<?php

/**
 * Testing the CoreTextFileEdit class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';

require_once './cmsimple/functions.php';
require_once './plugins/utf8/utf8.php';
require_once UTF8 . '/ucfirst.php';
require_once './cmsimple/classes/CSRFProtection.php';

/**
 * The file under test.
 */
require_once './cmsimple/classes/FileEdit.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * A test case for the CoreTextFileEdit class.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class CoreTextFileEditTest extends PHPUnit_Framework_TestCase
{
    private $_subject;

    private $_testFile;

    public function setUp()
    {
        global $pth, $sn, $file, $_XH_csrfProtection;

        if (!defined('CMSIMPLE_URL')) {
            define('CMSIMPLE_URL', 'http://example.com/xh/');
        } else {
            runkit_constant_redefine('CMSIMPLE_URL', 'http://example.com/xh/');
        }
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $this->_testFile = vfsStream::url('test/template.htm');
        file_put_contents($this->_testFile, '<html>');
        $file = 'template';
        $sn = '/xh/';
        $pth['file']['template'] = $this->_testFile;
        $_XH_csrfProtection = $this->getMockBuilder('XH_CSRFProtection')
            ->disableOriginalConstructor()->getMock();
        $this->_setUpLocalization();
        $this->_subject = new XH_CoreTextFileEdit();
    }

    private function _setUpLocalization()
    {
        global $tx;

        $tx = array(
            'action' => array(
                'save' => 'save'
            ),
            'filetype' => array(
                'template' => 'template'
            ),
            'message' => array(
                'saved' => 'Saved %s'
            )
        );
    }

    public function testFormAttributes()
    {
        $matcher = array(
            'tag' => 'form',
            'attributes' => array(
                'method' => 'post',
                'action' => '/xh/'
            )
        );
        @$this->assertTag($matcher, $this->_subject->form());
    }

    public function testFormContainsTextarea()
    {
        $matcher = array(
            'tag' => 'textarea',
            'attributes' => array(
                'name' => 'text',
                'class' => 'xh_file_edit'
            ),
            'content' => '<html>',
            'parent' => array('tag' => 'form')
        );
        @$this->assertTag($matcher, $this->_subject->form());
    }

    public function testFormContainsSubmitButton()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'submit',
                'class' => 'submit',
                'value' => 'Save'

            ),
            'parent' => array('tag' => 'form')
        );
        @$this->assertTag($matcher, $this->_subject->form());
    }

    public function testFormContainsFileInput()
    {
        global $file;

        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'hidden',
                'name' => 'file',
                'value' => $file
            )
        );
        @$this->assertTag($matcher, $this->_subject->form());
    }

    public function testFormContainsActionInput()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'hidden',
                'name' => 'action',
                'value' => 'save'
            )
        );
        @$this->assertTag($matcher, $this->_subject->form());
    }

    public function testSuccessMessage()
    {
        $_GET['xh_success'] = 'template';
        $matcher = array(
            'tag' => 'p',
            'attributes' => array('class' => 'xh_success'),
            'content' => 'Saved Template'
        );
        @$this->assertTag($matcher, $this->_subject->form());
    }

    public function testSubmit()
    {
        $headerSpy = new PHPUnit_Extensions_MockFunction('header', $this->_subject);
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL
                . '?file=template&action=edit&xh_success=template'
            )
        );
        $exitSpy = new PHPUnit_Extensions_MockFunction('XH_exit', $this->_subject);
        $exitSpy->expects($this->once());
        $_POST = array('text' => '</html>');
        $this->_subject->submit();
    }

    public function testSubmitCantSave()
    {
        $writeFileStub = new PHPUnit_Extensions_MockFunction(
            'XH_writeFile', $this->_subject
        );
        $writeFileStub->expects($this->once())->will($this->returnValue(false));
        $eSpy = new PHPUnit_Extensions_MockFunction('e', $this->_subject);
        $eSpy->expects($this->once())->with(
            $this->equalTo('cntsave'), $this->equalTo('file'),
            $this->equalTo($this->_testFile)
        );
        $_POST = array('text' => '</html>');
        $this->_subject->submit();
    }
}

?>
