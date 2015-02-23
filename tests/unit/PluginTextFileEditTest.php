<?php

/**
 * Testing the PluginTextFileEdit class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';

require_once './cmsimple/functions.php';
require_once './plugins/utf8/utf8.php';
require_once UTF8 . '/ucfirst.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * A test case for the PluginTextFileEdit class.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PluginTextFileEditTest extends PHPUnit_Framework_TestCase
{
    private $_subject;

    private $_testFile;

    public function setUp()
    {
        global $pth, $sn, $plugin, $_XH_csrfProtection;

        if (!defined('CMSIMPLE_URL')) {
            define('CMSIMPLE_URL', 'http://example.com/xh/');
        } else {
            runkit_constant_redefine('CMSIMPLE_URL', 'http://example.com/xh/');
        }
        $plugin = 'pagemanager';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $this->_testFile = vfsStream::url('test/stylesheet.css');
        file_put_contents($this->_testFile, 'body{}');
        $sn = '/xh/';
        $pth['file']['plugin_stylesheet'] = $this->_testFile;
        $_XH_csrfProtection = $this->getMockBuilder('XH_CSRFProtection')
            ->disableOriginalConstructor()->getMock();
        $this->_setUpLocalization();
        $this->_subject = new XH_PluginTextFileEdit();
    }

    private function _setUpLocalization()
    {
        global $tx;

        $tx = array(
            'action' => array(
                'save' => 'save'
            ),
            'filetype' => array(
                'stylesheet' => 'stylesheet'
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
                'action' => '/xh/?&pagemanager'
            )
        );
        @$this->assertTag($matcher, $this->_subject->form());
    }

    public function testFormContainsTextarea()
    {
        $matcher = array(
            'tag' => 'textarea',
            'attributes' => array(
                'name' => 'plugin_text',
                'class' => 'xh_file_edit'
            ),
            'content' => 'body{}',
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

    public function testFormContainsAdminInput()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'hidden',
                'name' => 'admin',
                'value' => 'plugin_stylesheet'
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
                'value' => 'plugin_textsave'
            )
        );
        @$this->assertTag($matcher, $this->_subject->form());
    }

    public function testSuccessMessage()
    {
        $_GET['xh_success'] = 'stylesheet';
        $matcher = array(
            'tag' => 'p',
            'attributes' => array('class' => 'xh_success'),
            'content' => 'Saved Stylesheet'
        );
        @$this->assertTag($matcher, $this->_subject->form());
    }

    public function testSubmit()
    {
        $headerSpy = new PHPUnit_Extensions_MockFunction('header', $this->_subject);
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL . '?&pagemanager&admin=plugin_stylesheet'
                . '&action=plugin_text&xh_success=stylesheet'
            )
        );
        $exitSpy = new PHPUnit_Extensions_MockFunction('XH_exit', $this->_subject);
        $exitSpy->expects($this->once());
        $_POST = array('plugin_text' => 'body{}');
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
        $_POST = array('plugin_text' => 'body{}');
        $this->_subject->submit();
    }
}

?>
