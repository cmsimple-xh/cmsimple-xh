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
 * Testing the handling of logout requests.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerLogoutTest extends ControllerLogInOutTestCase
{
    /**
     * The XH_backup() mock.
     *
     * @var object
     */
    protected $backupMock;

    protected $filePutContentsStub;

    protected $sessionNameMock;

    /**
     * Sets up the test fixture.
     */
    protected function setUp(): void
    {
        global $pth;

        parent::setUp();
        $_SESSION = array();
        $pth = array(
            'folder' => ['cmsimple' => './cmsimple/'],
        );
        $this->backupMock = $this->createFunctionMock('XH_backup');
        $this->filePutContentsStub = $this->createFunctionMock('file_put_contents');
        $this->sessionNameMock = $this->createFunctionMock('session_name');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->backupMock->restore();
        $this->filePutContentsStub->restore();
        $this->sessionNameMock->restore();
    }

    /**
     * Tests that logout makes backups.
     *
     * @return void
     */
    public function testMakesBackups()
    {
        $this->backupMock->expects($this->once());
        $this->subject->handleLogout();
    }

    /**
     * Tests that logout deletes the status cookie.
     *
     * @return void
     */
    public function testDeletesStatusCookie()
    {
        $this->setcookieMock->expects($this->any())->with('status', '');
        $this->subject->handleLogout();
    }

    /**
     * Tests that logout regenerates the session id.
     *
     * @return void
     */
    public function testRegeneratesSessionId()
    {
        $this->sessionRegenerateIdMock->expects($this->once())->with(true);
        $this->subject->handleLogout();
    }

    /**
     * Tests that logout unsets the session variable.
     *
     * @return void
     */
    public function testUnsetsSessionVariable()
    {
        $this->subject->handleLogout();
        $this->assertArrayNotHasKey('xh_password', $_SESSION);
    }

    /**
     * Tests that logout sets $f.
     *
     * @return void
     */
    public function testSetsF()
    {
        global $f;

        $this->subject->handleLogout();
        $this->assertEquals('xh_loggedout', $f);
    }
}
