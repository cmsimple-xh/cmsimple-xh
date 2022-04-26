<?php

/**
 * Testing the CSRF protection.
 *
 * If CMSimple_XH is not installed directly in the web root,
 * the environment variable CMSIMPLEDIR has to be set to the installation folder
 * (e.g. cmsimple_xh/).
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

/**
 * A test case to simulate the CSRF protection.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class CSRFProtectionTest extends TestCase
{
    protected $subject;

    protected $startSessionMock;

    protected $headerMock;

    protected $exitMock;

    protected function setUp(): void
    {
        $this->setConstant('CMSIMPLE_ROOT', '/test/');
        $this->startSessionMock = $this->createFunctionMock('XH_startSession');
        $this->headerMock = $this->createFunctionMock('header');
        $this->exitMock = $this->createFunctionMock('XH_exit');
        $this->subject = new CSRFProtection();
    }

    protected function tearDown(): void
    {
        $this->startSessionMock->restore();
        $this->headerMock->restore();
        $this->exitMock->restore();
    }

    public function testGetFollowedByPost()
    {
        $input = $this->subject->tokenInput();
        preg_match('/value="(.*)"/', $input, $matches);
        $this->subject->store();
        $_POST['xh_csrf_token'] = $matches[1];
        $this->subject->check();
    }

    public function testCSRFAttack()
    {
        $this->subject = new CSRFProtection();
        $_SESSION['xh_csrf_token'] = '5dff45ce0e8db5e4ea2bf59cf0cb96dd';
        $_POST['xh_csrf_token'] = 'fd97a436f658ecc2178561898f8a6c9e';
        $this->headerMock->expects($this->once())->with('HTTP/1.0 403 Forbidden');
        $this->exitMock->expects($this->once());
        $this->subject->check();
    }
}
