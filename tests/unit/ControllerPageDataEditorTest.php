<?php

/**
 * Testing the controller functionality.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the handling of page data editor requests.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerPageDataEditorTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * The page data editor mock.
     *
     * @var PageDataEditor
     */
    protected $pageDataEditorMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = $this->getMockBuilder(Controller::class)->setMethods(array('makePageDataEditor'))->getMock();
        $this->pageDataEditorMock = $this->createMock(PageDataEditor::class);
        $this->subject->method('makePageDataEditor')
            ->willReturn($this->pageDataEditorMock);
    }

    /**
     * Tests that PageDataEditor::process() is called.
     *
     * @return void
     */
    public function testCallsProcess()
    {
        $this->pageDataEditorMock->expects($this->once())->method('process');
        $this->subject->handlePageDataEditor();
    }
}
