<?php

/**
 * Testing the PluginLanguageFileEdit class.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit_Extensions_MockFunction;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * A test case to for the PluginLanguageFileEdit classes.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PluginLanguageFileEditTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        global $sn, $pth, $file, $plugin;

        $this->setConstant('CMSIMPLE_URL', 'http://example.com/xh/');
        $this->setConstant('XH_FORM_NAMESPACE', '');
        $this->setUpLanguage();
        $this->setUpMockery();
        $sn = '/xh/';
        $file = 'language';
        $plugin = 'pagemanager';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth = array(
            'folder' => array(
                'cmsimple' => vfsStream::url('test/'),
                'plugins' => vfsStream::url('test/')
            ),
            'file' => array(
                'plugin_language' => vfsStream::url('test/en.php')
            )
        );
        //$this->_setUpMetaConfig();
        $this->subject = new PluginLanguageFileEdit();
    }

    private function setUpLanguage()
    {
        global $plugin_tx;

        $plugin_tx = array(
            'pagemanager' => array(
                'foo_bar' => 'baz'
            )
        );
    }

    private function setUpMockery()
    {
        global $_XH_csrfProtection;

        $this->_tagStub = new PHPUnit_Extensions_MockFunction('tag', $this->subject);
        $this->_tagStub->expects($this->any())->will($this->returnCallback(
            function ($str) {
                return "<$str>";
            }
        ));
        $_XH_csrfProtection = $this->getMockBuilder('XH\CSRFProtection')
            ->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        $this->_tagStub->restore();
    }

    public function testFormAttributes()
    {
        $matcher = array(
            'tag' => 'form',
            'id' => 'xh_config_form',
            'attributes' => array(
                'action' => '/xh/?&pagemanager',
                'method' => 'post',
                'accept-charset' => 'UTF-8'
            )
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsSubmitButton()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'submit',
                'class' => 'submit'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsSiteTitleField()
    {
        $matcher = array(
            'tag' => 'textarea',
            'attributes' => array(),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    /**
     * @dataProvider hiddenInputData
     */
    public function testFormContainsHiddenInput($name, $value)
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'hidden',
                'name' => $name,
                'value' => $value
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function hiddenInputData()
    {
        return array(
            array('admin', 'plugin_language'),
            array('action', 'plugin_save')
        );
    }

    public function testSuccessMessage()
    {
        $_GET['xh_success'] = 'language';
        $matcher = array(
            'tag' => 'p',
            'attributes' => array('class' => 'xh_success')
        );
        $this->assertFormMatches($matcher);
    }

    public function testSubmit()
    {
        $writeFileSpy = new PHPUnit_Extensions_MockFunction('XH_writeFile', $this->subject);
        $writeFileSpy->expects($this->once())->will($this->returnValue(true));
        $headerSpy = new PHPUnit_Extensions_MockFunction('header', $this->subject);
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL . '?&pagemanager&admin=plugin_language'
                . '&action=plugin_edit&xh_success=language'
            )
        );
        $exitSpy = new PHPUnit_Extensions_MockFunction('XH_exit', $this->subject);
        $exitSpy->expects($this->once());
        $this->subject->submit();
    }

    public function testSubmitSaveFailure()
    {
        $writeFileSpy = new PHPUnit_Extensions_MockFunction('XH_writeFile', $this->subject);
        $writeFileSpy->expects($this->once())->will($this->returnValue(false));
        $eSpy = new PHPUnit_Extensions_MockFunction('e', $this->subject);
        $eSpy->expects($this->once());
        $this->subject->submit();
    }

    private function assertFormMatches($matcher)
    {
        @$this->assertTag($matcher, $this->subject->form());
    }
}
