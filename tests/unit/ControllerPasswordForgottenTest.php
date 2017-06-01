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
 * Testing the handling of password forgotten requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerPasswordForgottenTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * The password forgotten mock.
     *
     * @var PasswordForgotten
     */
    protected $passwordForgottenMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = $this->getMockBuilder(Controller::class)->setMethods(array('makePasswordForgotten'))->getMock();
        $this->passwordForgottenMock = $this->createMock(PasswordForgotten::class);
        $this->subject->method('makePasswordForgotten')
            ->willReturn($this->passwordForgottenMock);
    }

    /**
     * Tests that PasswordForgotten::dispatch() is called.
     *
     * @return void
     */
    public function testCallsDispatch()
    {
        $this->passwordForgottenMock->expects($this->once())->method('dispatch');
        $this->subject->handlePasswordForgotten();
    }
}
