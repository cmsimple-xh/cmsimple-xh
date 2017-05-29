<?php

/**
 * Testing the CoreTextFileEdit class.
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
 * A test case for the CoreTextFileEdit class.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class CoreTextFileEditTest extends TestCase
{
    private $subject;

    private $testFile;

    public function setUp()
    {
        global $pth, $sn, $file, $_XH_csrfProtection;

        $this->setConstant('CMSIMPLE_URL', 'http://example.com/xh/');
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $this->testFile = vfsStream::url('test/template.htm');
        file_put_contents($this->testFile, '<html>');
        $file = 'template';
        $sn = '/xh/';
        $pth['file']['template'] = $this->testFile;
        $_XH_csrfProtection = $this->getMockBuilder('XH\CSRFProtection')
            ->disableOriginalConstructor()->getMock();
        $this->setUpLocalization();
        $this->subject = new CoreTextFileEdit();
    }

    private function setUpLocalization()
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
        @$this->assertTag($matcher, $this->subject->form());
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
        @$this->assertTag($matcher, $this->subject->form());
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
        @$this->assertTag($matcher, $this->subject->form());
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
        @$this->assertTag($matcher, $this->subject->form());
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
        @$this->assertTag($matcher, $this->subject->form());
    }

    public function testSuccessMessage()
    {
        $_GET['xh_success'] = 'template';
        $matcher = array(
            'tag' => 'p',
            'attributes' => array('class' => 'xh_success'),
            'content' => 'Saved Template'
        );
        @$this->assertTag($matcher, $this->subject->form());
    }

    public function testSubmit()
    {
        $headerSpy = $this->getFunctionMock('header', $this->subject);
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL
                . '?file=template&action=edit&xh_success=template'
            )
        );
        $exitSpy = $this->getFunctionMock('XH_exit', $this->subject);
        $exitSpy->expects($this->once());
        $_POST = array('text' => '</html>');
        $this->subject->submit();
    }

    public function testSubmitCantSave()
    {
        $writeFileStub = $this->getFunctionMock('XH_writeFile', $this->subject);
        $writeFileStub->expects($this->once())->will($this->returnValue(false));
        $eSpy = $this->getFunctionMock('e', $this->subject);
        $eSpy->expects($this->once())->with(
            $this->equalTo('cntsave'),
            $this->equalTo('file'),
            $this->equalTo($this->testFile)
        );
        $_POST = array('text' => '</html>');
        $this->subject->submit();
    }
}
