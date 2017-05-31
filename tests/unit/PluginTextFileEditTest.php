<?php

/**
 * Testing the PluginTextFileEdit class.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

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
class PluginTextFileEditTest extends TestCase
{
    private $subject;

    private $testFile;

    public function setUp()
    {
        global $pth, $sn, $plugin, $_XH_csrfProtection;

        $this->setConstant('CMSIMPLE_URL', 'http://example.com/xh/');
        $plugin = 'pagemanager';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $this->testFile = vfsStream::url('test/stylesheet.css');
        file_put_contents($this->testFile, 'body{}');
        $sn = '/xh/';
        $pth['file']['plugin_stylesheet'] = $this->testFile;
        $_XH_csrfProtection = $this->getMockBuilder('XH\CSRFProtection')
            ->disableOriginalConstructor()->getMock();
        $this->setUpLocalization();
        $this->subject = new PluginTextFileEdit();
    }

    private function setUpLocalization()
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
        $this->assertXPath(
            '//form[@method="post" and @action="/xh/?&pagemanager"]',
            $this->subject->form()
        );
    }

    public function testFormContainsTextarea()
    {
        $this->assertXPathContains(
            '//form/textarea[@name="plugin_text" and @class="xh_file_edit"]',
            'body{}',
            $this->subject->form()
        );
    }

    public function testFormContainsSubmitButton()
    {
        $this->assertXPath(
            '//form/input[@type="submit" and @class="submit" and @value="Save"]',
            $this->subject->form()
        );
    }

    public function testFormContainsAdminInput()
    {
        $this->assertXPath(
            '//input[@type="hidden" and @name="admin" and @value="plugin_stylesheet"]',
            $this->subject->form()
        );
    }

    public function testFormContainsActionInput()
    {
        $this->assertXPath(
            '//input[@type="hidden" and @name="action" and @value="plugin_textsave"]',
            $this->subject->form()
        );
    }

    public function testSuccessMessage()
    {
        $_GET['xh_success'] = 'stylesheet';
        $this->assertXPathContains('//p[@class="xh_success"]', 'Saved Stylesheet', $this->subject->form());
    }

    public function testSubmit()
    {
        $headerSpy = $this->getFunctionMock('header');
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL . '?&pagemanager&admin=plugin_stylesheet'
                . '&action=plugin_text&xh_success=stylesheet'
            )
        );
        $exitSpy = $this->getFunctionMock('XH_exit');
        $exitSpy->expects($this->once());
        $_POST = array('plugin_text' => 'body{}');
        $this->subject->submit();
        $headerSpy->restore();
        $exitSpy->restore();
    }

    public function testSubmitCantSave()
    {
        $writeFileStub = $this->getFunctionMock('XH_writeFile');
        $writeFileStub->expects($this->once())->will($this->returnValue(false));
        $eSpy = $this->getFunctionMock('e');
        $eSpy->expects($this->once())->with(
            $this->equalTo('cntsave'),
            $this->equalTo('file'),
            $this->equalTo($this->testFile)
        );
        $_POST = array('plugin_text' => 'body{}');
        $this->subject->submit();
        $writeFileStub->restore();
        $eSpy->restore();
    }
}
