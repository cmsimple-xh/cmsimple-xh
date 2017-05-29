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

use PHPUnit_Extensions_MockFunction;
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
     *
     * @global array The configuration of the core.
     */
    public function setUp()
    {
        global $cf;

        $cf['security']['frame_options'] = 'DENY';
        $this->subject = new Controller();
        $this->headersSentMock = new PHPUnit_Extensions_MockFunction('headers_sent', $this->subject);
        $this->headerMock = new PHPUnit_Extensions_MockFunction('header', $this->subject);
        $this->exitMock = new PHPUnit_Extensions_MockFunction('XH_exit', $this->subject);
    }

    /**
     * Tests the standard headers.
     *
     * @return void
     */
    public function testStandardHeaders()
    {
        $this->headersSentMock->expects($this->once())
            ->will($this->returnValue(false));
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
            ->will($this->returnValue(true));
        $this->exitMock->expects($this->once());
        $this->subject->sendStandardHeaders();
    }
}
