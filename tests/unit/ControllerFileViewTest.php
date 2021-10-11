<?php

/**
 * Testing the controller functionality.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the handling of file view requests.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerFileViewTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * The XH_exit() mock.
     *
     * @var object
     */
    protected $exitMock;

    /**
     * The header() mock.
     *
     * @var object
     */
    protected $headerMock;

    /**
     * The XH_logFileView() mock.
     *
     * @var object
     */
    protected $logFileViewMock;

    /**
     * Sets up the test fixture.
     */
    protected function setUp(): void
    {
        global $file;

        $this->setUpFileSystem();
        $file = 'content';
        $this->subject = new Controller();
        $this->exitMock = $this->createFunctionMock('XH_exit');
        $this->headerMock = $this->createFunctionMock('header');
        $this->logFileViewMock = $this->createFunctionMock('XH_logFileView');
    }

    /**
     * Sets up the file system.
     *
     * @return void
     */
    protected function setUpFileSystem()
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth['file']['content'] = vfsStream::url('test/content.htm');
        file_put_contents($pth['file']['content'], 'foo');
    }

    protected function tearDown(): void
    {
        $this->exitMock->restore();
        $this->headerMock->restore();
        $this->logFileViewMock->restore();
    }

    /**
     * Tests that the Content-Type header is sent.
     *
     * @return void
     */
    public function testSendsContentTypeHeader()
    {
        $this->headerMock->expects($this->once())
            ->with('Content-Type: text/plain; charset=utf-8');
        $this->handleFileView();
    }

    /**
     * Tests that the file contents are output.
     *
     * @return void
     */
    public function testOutputsFileContents()
    {
        $this->expectOutputString('foo');
        $this->subject->handleFileView();
    }

    /**
     * Tests that the script is exited.
     *
     * @return void
     */
    public function testExitsScript()
    {
        $this->exitMock->expects($this->once());
        $this->handleFileView();
    }

    /**
     * Calls Controller::handleFileView() while buffering output.
     *
     * @return void
     */
    protected function handleFileView()
    {
        ob_start();
        $this->subject->handleFileView();
        ob_end_clean();
    }

    /**
     * Tests the log file view.
     *
     * @return void
     */
    public function testLogFile()
    {
        global $file;

        $file = 'log';
        $this->logFileViewMock->expects($this->once());
        $this->subject->handleFileView();
    }
}
