<?php

/**
 * Testing the controller functionality.
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
 * Testing the standard headers.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.5
 */
class ControllerStandardHeaderTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * The headers_sent() mock.
     *
     * @var object
     */
    protected $headersSentMock;

    /**
     * The header() mock.
     *
     * @var object
     */
    protected $headerMock;

    /**
     * The XH_exit() mock.
     *
     * @var object
     */
    protected $exitMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    protected function setUp()
    {
        global $cf;

        $cf['security']['frame_options'] = 'DENY';
        $this->subject = new Controller();
        $this->headersSentMock = $this->createFunctionMock('headers_sent');
        $this->headerMock = $this->createFunctionMock('header');
        $this->exitMock = $this->createFunctionMock('XH_exit');
    }

    protected function tearDown()
    {
        $this->headersSentMock->restore();
        $this->headerMock->restore();
        $this->exitMock->restore();
    }

    /**
     * Tests the standard headers.
     *
     * @return void
     */
    public function testStandardHeaders()
    {
        $this->headersSentMock->expects($this->once())
            ->willReturn(false);
        $this->headerMock->expects($this->exactly(3));
        $this->subject->sendStandardHeaders();
    }

    /**
     * Tests headers already sent.
     *
     * @return void
     */
    public function testHeadersAlreadySent()
    {
        $this->headersSentMock->expects($this->once())
            ->willReturn(true);
        $this->exitMock->expects($this->once());
        $this->subject->sendStandardHeaders();
    }
}
