<?php

/**
 * Testing the controller functionality.
 *
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
 * Testing the handling of file edit requests.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerFileEditTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * The file editor mock.
     *
     * @var FileEdit
     */
    protected $fileEditorMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = $this->getMockBuilder(Controller::class)->setMethods(array('makeFileEditor'))->getMock();
        $this->fileEditorMock = $this->createMock(CoreConfigFileEdit::class);
        $this->subject->expects($this->any())->method('makeFileEditor')
            ->willReturn($this->fileEditorMock);
    }

    /**
     * Tests that the array action calls FileEdit::form().
     *
     * @return void
     *
     * @global string The name of a special file to be handled.
     * @global string The requested action.
     */
    public function testArrayActionCallsForm()
    {
        global $file, $action;

        $file = 'config';
        $action = 'array';
        $this->fileEditorMock->expects($this->once())->method('form');
        $this->subject->handleFileEdit();
    }

    /**
     * Tests that the save action calls FileEdit::submit().
     *
     * @return void
     *
     * @global string The name of a special file to be handled.
     * @global string The requested action.
     */
    public function testSaveActionCallsSubmit()
    {
        global $file, $action;

        $file = 'config';
        $action = 'save';
        $this->fileEditorMock->expects($this->once())->method('submit');
        $this->subject->handleFileEdit();
    }
}
