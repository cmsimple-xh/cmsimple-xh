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

namespace XH;

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
class CoreLangFileEditTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        global $sn, $pth, $file;

        $this->setConstant('CMSIMPLE_URL', 'http://example.com/xh/');
        $this->setConstant('XH_FORM_NAMESPACE', '');
        $this->setUpLanguage();
        $this->setUpMockery();
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
        $this->subject = new CoreLangFileEdit();
    }

    private function setUpLanguage()
    {
        global $tx;

        $tx = array(
            'action' => array('save' => ''),
            'filetype' => array('language' => ''),
            'message' => array('saved' => ''),
            'site' => array('title' => str_repeat('CMSimple_XH', 10))
        );
    }

    private function setUpMockery()
    {
        global $_XH_csrfProtection;

        $this->_tagStub = $this->getFunctionMock('tag');
        $this->_tagStub->expects($this->any())->will($this->returnCallback(
            function ($str) {
                return "<$str>";
            }
        ));
        $_XH_csrfProtection = $this->createMock(CSRFProtection::class);
    }

    public function tearDown()
    {
        $this->_tagStub->restore();
    }

    public function testFormAttributes()
    {
        $this->assertXPath(
            '//form[@id="xh_config_form" and @action="/xh/" and @method="post" and @accept-charset="UTF-8"]',
            $this->subject->form()
        );
    }

    public function testFormContainsSubmitButton()
    {
        $this->assertXPath(
            '//input[@type="submit" and @class="submit"]',
            $this->subject->form()
        );
    }

    public function testFormContainsSiteTitleField()
    {
        $this->assertXPath(
            '//form//textarea[@name="site_title" and @class="xh_setting"]',
            $this->subject->form()
        );
    }

    public function testFormContainsMessageSavedField()
    {
        $this->assertXPath(
            '//textarea[@name="message_saved" and @class="xh_setting xh_setting_short"]',
            $this->subject->form()
        );
    }

    /**
     * @dataProvider hiddenInputData
     */
    public function testFormContainsHiddenInput($name, $value)
    {
        $this->assertXPath(
            sprintf('//form//input[@type="hidden" and @name="%s" and @value="%s"]', $name, $value),
            $this->subject->form()
        );
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
        $this->assertXPath(
            '//p[@class="xh_success"]',
            $this->subject->form()
        );
    }

    public function testSubmit()
    {
        $writeFileSpy = $this->getFunctionMock('XH_writeFile');
        $writeFileSpy->expects($this->once())->willReturn(true);
        $headerSpy = $this->getFunctionMock('header');
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL
                . '?file=language&action=array&xh_success=language'
            )
        );
        $exitSpy = $this->getFunctionMock('XH_exit');
        $exitSpy->expects($this->once());
        $_POST = array(
            'security_password_OLD' => 'foo',
            'security_password_NEW' => 'bar',
            'security_password_CONFIRM' => 'bar',
        );
        $this->subject->submit();
        $writeFileSpy->restore();
        $headerSpy->restore();
        $exitSpy->restore();
    }

    public function testSubmitSaveFailure()
    {
        $writeFileSpy = $this->getFunctionMock('XH_writeFile');
        $writeFileSpy->expects($this->once())->willReturn(false);
        $eSpy = $this->getFunctionMock('e');
        $eSpy->expects($this->once());
        $this->subject->submit();
        $writeFileSpy->restore();
        $eSpy->restore();
    }
}
