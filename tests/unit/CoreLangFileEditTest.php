<?php

/**
 * Testing the CoreLangFileEdit class.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * A test case to for the CoreLangFileEdit classes.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class CoreLangFileEditTest extends PHPUnit_Framework_TestCase
{
    private $_subject;

    public function setUp()
    {
        global $sn, $pth, $file;

        $this->_setConstant('CMSIMPLE_URL', 'http://example.com/xh/');
        $this->_setConstant('XH_FORM_NAMESPACE', '');
        $this->_setUpLanguage();
        $this->_setUpMockery();
        $sn = '/xh/';
        $file = 'language';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth = array(
            'folder' => array(
                'cmsimple' => vfsStream::url('test/'),
                'language' => vfsStream::url('test/')
            ),
            'file' => array(
                'language' => vfsStream::url('test/en.php')
            )
        );
        //$this->_setUpMetaConfig();
        $this->_subject = new XH\CoreLangFileEdit();
    }

    private function _setConstant($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        } else {
            runkit_constant_redefine($name, $value);
        }
    }

    private function _setUpLanguage()
    {
        global $tx;

        $tx = array(
            'action' => array('save' => ''),
            'filetype' => array('language' => ''),
            'message' => array('saved' => ''),
            'site' => array('title' => str_repeat('CMSimple_XH', 10))
        );
    }

    private function _setUpMockery()
    {
        global $_XH_csrfProtection;

        $this->_tagStub = new PHPUnit_Extensions_MockFunction('tag', $this->_subject);
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
                'action' => '/xh/',
                'method' => 'post',
                'accept-charset' => 'UTF-8'
            )
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsSubmitButton()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'submit',
                'class' => 'submit'
            )
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsSiteTitleField()
    {
        $matcher = array(
            'tag' => 'textarea',
            'attributes' => array(
                'name' => 'site_title',
                'class' => 'xh_setting'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsMessageSavedField()
    {
        $matcher = array(
            'tag' => 'textarea',
            'attributes' => array(
                'name' => 'message_saved',
                'class' => 'xh_setting xh_setting_short'
            )
        );
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
    }

    public function hiddenInputData()
    {
        return array(
            array('file', 'language'),
            array('action', 'save')
        );
    }

    public function testSuccessMessage()
    {
        $_GET['xh_success'] = 'language';
        $matcher = array(
            'tag' => 'p',
            'attributes' => array('class' => 'xh_success')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testSubmit()
    {
        $writeFileSpy = new PHPUnit_Extensions_MockFunction(
            'XH_writeFile', $this->_subject
        );
        $writeFileSpy->expects($this->once())->will($this->returnValue(true));
        $headerSpy = new PHPUnit_Extensions_MockFunction('header', $this->_subject);
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL
                . '?file=language&action=array&xh_success=language'
            )
        );
        $exitSpy = new PHPUnit_Extensions_MockFunction('XH_exit', $this->_subject);
        $exitSpy->expects($this->once());
        $_POST = array(
            'security_password_OLD' => 'foo',
            'security_password_NEW' => 'bar',
            'security_password_CONFIRM' => 'bar',
        );
        $this->_subject->submit();
    }

    public function testSubmitSaveFailure()
    {
        $writeFileSpy = new PHPUnit_Extensions_MockFunction(
            'XH_writeFile', $this->_subject
        );
        $writeFileSpy->expects($this->once())->will($this->returnValue(false));
        $eSpy = new PHPUnit_Extensions_MockFunction('e', $this->_subject);
        $eSpy->expects($this->once());
        $this->_subject->submit();
    }

    private function _assertFormMatches($matcher)
    {
        @$this->assertTag($matcher, $this->_subject->form());
    }
}

?>
