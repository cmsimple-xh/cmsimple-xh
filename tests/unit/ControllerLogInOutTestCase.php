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

abstract class ControllerLogInOutTestCase extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * The session_start() mock.
     *
     * @var object
     */
    protected $sessionStartMock;

    /**
     * The session_regenerate_id() mock.
     *
     * @var object
     */
    protected $sessionRegenerateIdMock;

    /**
     * The setcookie() mock.
     *
     * @var object
     */
    protected $setcookieMock;

    protected function setUp()
    {
        $this->setConstant('CMSIMPLE_ROOT', '/xh/');
        $this->subject = new Controller();
        $this->sessionStartMock = $this->createFunctionMock('session_start');
        $this->sessionRegenerateIdMock = $this->createFunctionMock('session_regenerate_id');
        $this->setcookieMock = $this->createFunctionMock('setcookie');
    }

    protected function tearDown()
    {
        $this->sessionStartMock->restore();
        $this->sessionRegenerateIdMock->restore();
        $this->setcookieMock->restore();
    }
}
