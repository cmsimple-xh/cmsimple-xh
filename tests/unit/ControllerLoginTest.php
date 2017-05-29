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
 * Testing the handling of login requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerLoginTest extends ControllerLogInOutTestCase
{
    /**
     * @var object
     */
    private $passwordVerifyMock;

    /**
     * The e() mock.
     *
     * @var object
     */
    protected $eMock;

    /**
     * The XH_logMessage() mock.
     *
     * @var object
     */
    protected $logMessageMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array        The configuration of the core.
     */
    public function setUp()
    {
        global $cf;

        parent::setUp();
        $_SERVER = array(
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
            'REMOTE_ADDR' => '127.0.0.1'
        );
        $this->passwordVerifyMock = $this->getFunctionMock('password_verify', $this->subject);
        $cf['security']['password'] = '$P$BHYRVbjeM5YAvnwX2AkXnyqjLhQAod1';
        $this->eMock = $this->getFunctionMock('e', $this->subject);
        $this->logMessageMock = $this->getFunctionMock('XH_logMessage', $this->subject);
        $this->getFunctionMock('file_put_contents', $this->subject);
    }

    /**
     * Tests that login success sets the status cookie.
     *
     * @return void
     */
    public function testSuccessSetsStatusCookie()
    {
        $this->passwordVerifyMock->expects($this->any())->will($this->returnValue(true));
        $this->setcookieMock->expects($this->any())->with('status', 'adm');
        $this->subject->handleLogin();
    }

    /**
     * Tests that login success set the session variables.
     *
     * @return void
     *
     * @global array        The configuration of the core.
     */
    public function testSuccessSetsSessionVariables()
    {
        global $cf;

        $this->passwordVerifyMock->expects($this->any())->will($this->returnValue(true));
        $this->subject->handleLogin();
        $this->assertEquals(
            $cf['security']['password'],
            $_SESSION['xh_password']
        );
        $this->assertEquals(md5($_SERVER['HTTP_USER_AGENT']), $_SESSION['xh_user_agent']);
    }

    /**
     * Tests that login success regenerates the session ID.
     *
     * @return void
     */
    public function testSuccessRegeneratesSessionId()
    {
        $this->passwordVerifyMock->expects($this->any())->will($this->returnValue(true));
        $this->sessionRegenerateIdMock->expects($this->once())->with(true);
        $this->subject->handleLogin();
    }

    /**
     * Tests that login success writes a log message.
     *
     * @return void
     */
    public function testSuccessWritesLogMessage()
    {
        $this->passwordVerifyMock->expects($this->any())->will($this->returnValue(true));
        $this->logMessageMock->expects($this->once())
            ->with('info', 'XH', 'login');
        $this->subject->handleLogin();
    }

    /**
     * Tests that login failure sets global variables.
     *
     * @return void
     *
     * @global string       The requested function.
     * @global string       Whether login is requested.
     */
    public function testFailSetsGlobalVariables()
    {
        global $f, $login;

        $this->passwordVerifyMock->expects($this->any())->will($this->returnValue(false));
        $this->subject->handleLogin();
        $this->assertNull($login);
        $this->assertEquals('xh_login_failed', $f);
    }

    /**
     * Tests that login failure writes a log message.
     *
     * @return void
     */
    public function testFailWritesLogMessage()
    {
        $this->passwordVerifyMock->expects($this->any())->will($this->returnValue(false));
        $this->logMessageMock->expects($this->once())
            ->with('warning', 'XH', 'login');
        $this->subject->handleLogin();
    }
}
