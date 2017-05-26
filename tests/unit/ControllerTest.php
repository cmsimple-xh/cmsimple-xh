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

require_once './vendor/autoload.php';
require_once './cmsimple/adminfuncs.php';
require_once './cmsimple/functions.php';
require_once './cmsimple/tplfuncs.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the factory methods.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerMakeTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = new XH\Controller();
    }

    /**
     * Tests ::makeSearch().
     *
     * @return void
     */
    public function testMakeSearch()
    {
        $this->assertInstanceOf('XH\Search', $this->subject->makeSearch());
    }

    /**
     * Tests ::makeMailform().
     *
     * @return void
     */
    public function testMakeMailform()
    {
        $this->assertInstanceOf('XH\Mailform', $this->subject->makeMailform());
    }

    /**
     * Tests ::makePasswordForgotten().
     *
     * @return void
     */
    public function testMakePasswordForgotten()
    {
        $this->assertInstanceOf(
            'XH\PasswordForgotten', $this->subject->makePasswordForgotten()
        );
    }

    /**
     * Tests ::makePageDataEditor().
     *
     * @return void
     */
    public function testMakePageDataEditor()
    {
        $this->assertInstanceOf(
            'XH\PageDataEditor', $this->subject->makePageDataEditor()
        );
    }
}

/**
 * Testing the handling of mailform requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerMailformTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * The mailform mock.
     *
     * @var XH\Mailform
     */
    protected $mailformMock;

    /**
     * The head() mock.
     *
     * @var object
     */
    protected $sheadMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array The configuration of the core.
     * @global array The localization of the core.
     */
    public function setUp()
    {
        global $cf, $tx;

        $cf['mailform']['email'] = 'devs@cmsimple-xh.org';
        $tx['title']['mailform'] = 'Mailform';
        $this->subject = $this->getMock('XH\Controller', array('makeMailform'));
        $this->mailformMock = $this->getMockBuilder('XH\Mailform')
            ->disableOriginalConstructor()->getMock();
        $this->subject->expects($this->any())->method('makeMailform')
            ->will($this->returnValue($this->mailformMock));
        $this->sheadMock = new PHPUnit_Extensions_MockFunction(
            'shead', $this->subject
        );
    }

    /**
     * Tests that the title is set.
     *
     * @return void
     *
     * @global string The content of the title element.
     */
    public function testSetsTitle()
    {
        global $title;

        $this->subject->handleMailform();
        $this->assertEquals('Mailform', $title);
    }

    /**
     * Tests the rendered HTML.
     *
     * @return void
     *
     * @global string The HTML of the content area.
     */
    public function testRenderedHTML()
    {
        global $o;

        $this->subject->handleMailform();
        @$this->assertTag(
            array(
                'tag' => 'div',
                'id' => 'xh_mailform',
                'child' => array(
                    'tag' => 'h1',
                    'content' => 'Mailform'
                )
            ),
            $o
        );
    }

    /**
     * Tests that ::process() is called.
     *
     * @return void
     */
    public function testCallsProcess()
    {
        $this->mailformMock->expects($this->once())->method('process');
        $this->subject->handleMailform();
    }

    /**
     * Tests that shead() is called when the mailform is disabled.
     *
     * @return void
     *
     * @global array The configuration of the core.
     */
    public function testCallsSheadWhenMailformIsDisabled()
    {
        global $cf;

        $cf['mailform']['email'] = '';
        $this->sheadMock->expects($this->once());
        $this->subject->handleMailform();
    }

}

/**
 * Testing the handling of search requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerSearchTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * The search mock.
     *
     * @var XH\Search
     */
    protected $searchMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array The localization of the core.
     */
    public function setUp()
    {
        global $tx;

        $tx['title']['search'] = 'Search';
        $this->subject = $this->getMock('XH\Controller', array('makeSearch'));
        $this->searchMock = $this->getMockBuilder('XH\Search')
            ->disableOriginalConstructor()->getMock();
        $this->subject->expects($this->any())->method('makeSearch')
            ->will($this->returnValue($this->searchMock));
    }

    /**
     * Tests that the title is set.
     *
     * @return void
     *
     * @global string The content of the title element.
     */
    public function testSetsTitle()
    {
        global $title;

        $this->subject->handleSearch();
        $this->assertEquals('Search', $title);
    }

    /**
     * Tests that ::render() is called.
     *
     * @return void
     */
    public function testCallsRender()
    {
        $this->searchMock->expects($this->once())->method('render');
        $this->subject->handleSearch();
    }
}

/**
 * Testing the handling of sitemap requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerSitemapTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * The hide() mock.
     *
     * @var object
     */
    protected $hideMock;

    /**
     * The li() mock.
     *
     * @var object
     */
    protected $liMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global int   The number of pages.
     * @global array The localization of the core.
     */
    public function setUp()
    {
        global $cl, $tx;

        $cl = 10;
        $tx['title'] = array(
            'sitemap' => 'Sitemap'
        );
        $this->subject = $this->getMock('XH\Controller', null);
        $this->hideMock = new PHPUnit_Extensions_MockFunction(
            'hide', $this->subject
        );
        $this->liMock = new PHPUnit_Extensions_MockFunction('li', $this->subject);
    }

    /**
     * Tests that the title is set.
     *
     * @return void
     *
     * @global string The content of the title element.
     */
    public function testSetsTitle()
    {
        global $title;

        $this->subject->handleSitemap();
        $this->assertEquals('Sitemap', $title);
    }

    /**
     * Tests the rendered HTML.
     *
     * @return void
     *
     * @global string The HTML of the contents area.
     */
    public function testRenderedHTML()
    {
        global $o;

        $this->subject->handleSitemap();
        @$this->assertTag(
            array(
                'tag' => 'h1',
                'content' => 'Sitemap'
            ),
            $o
        );
    }

    /**
     * Tests that li() is called.
     *
     * @return void
     */
    public function testCallsLi()
    {
        $this->liMock->expects($this->once())->with(range(0, 9), 'sitemaplevel');
        $this->subject->handleSitemap();
    }
}

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
class ControllerPasswordForgottenTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * The password forgotten mock.
     *
     * @var XH\PasswordForgotten
     */
    protected $passwordForgottenMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = $this->getMock(
            'XH\Controller', array('makePasswordForgotten')
        );
        $this->passwordForgottenMock = $this->getMockBuilder('XH\PasswordForgotten')
            ->disableOriginalConstructor()->getMock();
        $this->subject->expects($this->any())->method('makePasswordForgotten')
            ->will($this->returnValue($this->passwordForgottenMock));
    }

    /**
     * Tests that XH\PasswordForgotten::dispatch() is called.
     *
     * @return void
     */
    public function testCallsDispatch()
    {
        $this->passwordForgottenMock->expects($this->once())->method('dispatch');
        $this->subject->handlePasswordForgotten();
    }
}

abstract class ControllerLogInOutTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
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

    public function setUp()
    {
        $this->defineConstant('CMSIMPLE_ROOT', '/xh/');
        $this->subject = new XH\Controller();
        $this->sessionStartMock = new PHPUnit_Extensions_MockFunction(
            'session_start', $this->subject
        );
        $this->sessionRegenerateIdMock = new PHPUnit_Extensions_MockFunction(
            'session_regenerate_id', $this->subject
        );
        $this->setcookieMock = new PHPUnit_Extensions_MockFunction(
            'setcookie', $this->subject
        );
    }

    /**
     * (Re)defines a constant.
     *
     * @param string $name  A name.
     * @param mixed  $value A value.
     *
     * @return void
     */
    protected function defineConstant($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        } else {
            runkit_constant_redefine($name, $value);
        }
    }
}

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
        $this->passwordVerifyMock = new PHPUnit_Extensions_MockFunction('password_verify', $this->subject);
        $cf['security']['password'] = '$P$BHYRVbjeM5YAvnwX2AkXnyqjLhQAod1';
        $this->eMock = new PHPUnit_Extensions_MockFunction('e', $this->subject);
        $this->logMessageMock = new PHPUnit_Extensions_MockFunction(
            'XH_logMessage', $this->subject
        );
        new PHPUnit_Extensions_MockFunction('file_put_contents', $this->subject);
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
        $this->assertEquals(
            md5($_SERVER['HTTP_USER_AGENT']), $_SESSION['xh_user_agent']
        );
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

/**
 * Testing the handling of logout requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
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

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global PasswordHash The password hasher.
     * @global array        The configuration of the core.
     */
    public function setUp()
    {
        parent::setUp();
        $_SESSION = array();
        $this->backupMock = new PHPUnit_Extensions_MockFunction(
            'XH_backup', $this->subject
        );
        new PHPUnit_Extensions_MockFunction('file_put_contents', $this->subject);
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
     *
     * @global string The requested function.
     */
    public function testSetsF()
    {
        global $f;

        $this->subject->handleLogout();
        $this->assertEquals('xh_loggedout', $f);
    }
}

/**
 * Testing the setting of frontend $f.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerFrontendFTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = new XH\Controller();
    }

    /**
     * Tests $f == 'forgotten'.
     *
     * @return void
     *
     * @global string The requested function.
     * @global string The requested function.
     */
    public function testForgotten()
    {
        global $function, $f;

        $function = 'forgotten';
        $this->subject->setFrontendF();
        $this->assertEquals('forgotten', $f);
    }

    /**
     * Tests $f == 'search'.
     *
     * @return void
     *
     * @global string The requested function.
     * @global string The requested function.
     */
    public function testSearch()
    {
        global $function, $f;

        $function = 'search';
        $this->subject->setFrontendF();
        $this->assertEquals('search', $f);
    }

    /**
     * Tests $f == 'mailform' for mailform display.
     *
     * @return void
     *
     * @global string The URL of the current page.
     * @global string Whether the mailform is requested.
     * @global string The requested function.
     */
    public function testMailformDisplay()
    {
        global $su, $mailform, $f;

        $su = 'mailform';
        $mailform = 'true';
        $this->subject->setFrontendF();
        $this->assertEquals('mailform', $f);
    }

    /**
     * Test $f == 'mailform' for mailform submission.
     *
     * @return void
     *
     * @global string The URL of the current page.
     * @global string The requested function.
     * @global string The requested function.
     */
    public function testMailformSubmission()
    {
        global $su, $function, $f;

        $su = '';
        $function = 'mailform';
        $this->subject->setFrontendF();
        $this->assertEquals('mailform', $f);
    }

    /**
     * Tests $f == 'sitemap'.
     *
     * @return void
     *
     * @global string The URL of the current page.
     * @global string Whether the sitemap is requested.
     * @global string The requested function.
     */
    public function testSitemap()
    {
        global $su, $sitemap, $f;

        $su = '';
        $sitemap = 'true';
        $this->subject->setFrontendF();
        $this->assertEquals('sitemap', $f);
    }

    /**
     * Tests $f == 'xhpages'.
     *
     * @return void
     *
     * @global string The URL of the current page.
     * @global string Whether the page manager is requested.
     * @global string The requested function.
     */
    public function testXhpages()
    {
        global $xhpages, $f;

        $xhpages = 'true';
        $this->subject->setFrontendF();
        $this->assertEquals('xhpages', $f);
    }

    /**
     * Tests $f == 'sitemap', if search and sitemap are requested.
     *
     * @return void
     *
     * @global string The URL of the current page.
     * @global string Whether the sitemap is requested.
     * @global string The requested function.
     * @global string The requested function.
     */
    public function testSearchAndSitemap()
    {
        global $su, $sitemap, $function, $f;

        $su = '';
        $sitemap = 'true';
        $function = 'search';
        $this->subject->setFrontendF();
        $this->assertEquals('sitemap', $f);
    }
}

/**
 * Testing the setting of backend $f.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerBackendFTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = new XH\Controller();
    }

    /**
     * Tests $f == 'validate'.
     *
     * @return void
     *
     * @global string Whether the link check is requested.
     * @global string The requested function.
     */
    public function testValidate()
    {
        global $validate, $f;

        $validate = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('validate', $f);
    }

    /**
     * Tests $f == 'do_validate'.
     *
     * @return void
     *
     * @global string Whether the actual link check is requested.
     * @global string The requested function.
     */
    public function testDoValidate()
    {
        global $xh_do_validate, $f;

        $xh_do_validate = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('do_validate', $f);
    }

    /**
     * Tests $f == 'settings'.
     *
     * @return void
     *
     * @global string Whether the settings page is requested.
     * @global string The requested function.
     */
    public function testSettings()
    {
        global $settings, $f;

        $settings = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('settings', $f);
    }

    /**
     * Tests $f == 'xh_backups'.
     *
     * @return void
     *
     * @global string Whether the backup page is requested.
     * @global string The requested function.
     */
    public function testBackups()
    {
        global $xh_backups, $f;

        $xh_backups = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('xh_backups', $f);
    }

    /**
     * Tests $f == 'xh_pagedata'.
     *
     * @return void
     *
     * @global string Whether the pagedata editor is requested.
     * @global string The requested function.
     */
    public function testPagedata()
    {
        global $xh_pagedata, $f;

        $xh_pagedata = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('xh_pagedata', $f);
    }

    /**
     * Tests $f == 'sysinfo'.
     *
     * @return void
     *
     * @global string Whether the system info is requested.
     * @global string The requested function.
     */
    public function testSysinfo()
    {
        global $sysinfo, $f;

        $sysinfo = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('sysinfo', $f);
    }

    /**
     * Tests $f == 'phpinfo'.
     *
     * @return void
     *
     * @global string Whether the PHP info is requested.
     * @global string The requested function.
     */
    public function testPhpinfo()
    {
        global $phpinfo, $f;

        $phpinfo = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('phpinfo', $f);
    }

    /**
     * Tests $f == 'file'.
     *
     * @return void
     *
     * @global string The name of a special file to be handled.
     * @global string The requested function.
     */
    public function testFile()
    {
        global $file, $f;

        $file = 'config';
        $this->subject->setBackendF();
        $this->assertEquals('file', $f);
    }

    /**
     * Tests $f == 'userfiles'.
     *
     * @return void
     *
     * @global string Whether the file browser is requested to show the
     *                userfiles folder.
     * @global string The requested function.
     */
    public function testUserfiles()
    {
        global $userfiles, $f;

        $userfiles = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('userfiles', $f);
    }

    /**
     * Tests $f == 'images'.
     *
     * @return void
     *
     * @global string Whether the file browser is requested to show the image
     *                folder.
     * @global string The requested function.
     */
    public function testImages()
    {
        global $images, $f;

        $images = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('images', $f);
    }

    /**
     * Tests $f == 'downloads'.
     *
     * @return void
     *
     * @global string Whether the file browser is requested to show the download
     *                folder.
     * @global string The requested function.
     */
    public function testDownloads()
    {
        global $downloads, $f;

        $downloads = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('downloads', $f);
    }

    /**
     * Tests $f == 'save'.
     *
     * @return void
     *
     * @global string The requested function.
     * @global string The requested function.
     */
    public function testSave()
    {
        global $function, $f;

        $function = 'save';
        $this->subject->setBackendF();
        $this->assertEquals('save', $f);
    }

    /**
     * Tests $f == 'save' when system info and saving are requested.
     *
     * @return void
     *
     * @global string The requested function.
     * @global string Whether the system info is requested.
     * @global string The requested function.
     */
    public function testSysinfoAndSave()
    {
        global $function, $sysinfo, $f;

        $function = 'save';
        $sysinfo = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('save', $f);
    }
}

/**
 * Testing the handling of save page data requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerSavePageDataTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * The e() mock.
     *
     * @var object
     */
    protected $eMock;

    /**
     * The exit mock.
     *
     * @var object
     */
    protected $exitMock;

    /**
     * The header mock.
     *
     * @var object
     */
    protected $headerMock;

    /**
     * The message mock.
     *
     * @var object
     */
    protected $messageMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global int               The index of the currently selected page.
     * @global XH\PageDataRouter The page data router.
     * @global XH\CSRFProtection The CSRF protector.
     */
    public function setUp()
    {
        global $s, $pd_router, $_XH_csrfProtection;

        $_POST = array(
            'foo' => 'bar',
            'save_page_data' => '',
            'xh_csrf_token' => '0123456789abcdef'
        );
        $s = 0;
        $pd_router = $this->getMockBuilder('XH\PageDataRouter')
            ->disableOriginalConstructor()->getMock();
        $_XH_csrfProtection = $this->getMockBuilder('XH\CSRFProtection')
            ->disableOriginalConstructor()->getMock();
        $this->subject = new XH\Controller();
        $this->eMock = new PHPUnit_Extensions_MockFunction('e', $this->subject);
        $this->exitMock = new PHPUnit_Extensions_MockFunction(
            'XH_exit', $this->subject
        );
        $this->headerMock = new PHPUnit_Extensions_MockFunction(
            'header', $this->subject
        );
        $this->messageMock = new PHPUnit_Extensions_MockFunction(
            'XH_message', $this->subject
        );
    }

    /**
     * Tests that XH\PageDataRouter::update() is called.
     *
     * @return void
     *
     * @global int               The index of the currently selected page.
     * @global XH\PageDataRouter The page data router.
     */
    public function testCallsUpdate()
    {
        global $s, $pd_router;

        $pd_router->expects($this->once())->method('update')
            ->with($s, array('foo' => 'bar'));
        $this->subject->handleSavePageData();
    }

    /**
     * Tests that Ajax success outputs a message.
     *
     * @return void
     *
     * @global XH\PageDataRouter The page data router.
     */
    public function testAjaxSuccessOutputsMessage()
    {
        global $pd_router;

        $_GET['xh_pagedata_ajax'] = '';
        $pd_router->expects($this->any())->method('update')
            ->will($this->returnValue(true));
        $this->messageMock->expects($this->once())->with('info');
        $this->subject->handleSavePageData();
    }

    /**
     * Tests that Ajax failure outputs a message.
     *
     * @return void
     *
     * @global XH\PageDataRouter The page data router.
     */
    public function testAjaxFailureOutputsMessage()
    {
        global $pd_router;

        $_GET['xh_pagedata_ajax'] = '';
        $pd_router->expects($this->any())->method('update')
            ->will($this->returnValue(false));
        $this->messageMock->expects($this->once())->with('fail');
        $this->subject->handleSavePageData();
    }

    /**
     * Tests that Ajax exists the script.
     *
     * @return void
     */
    public function testAjaxExistsScript()
    {
        $_GET['xh_pagedata_ajax'] = '';
        $this->exitMock->expects($this->once());
        $this->subject->handleSavePageData();
    }

    /**
     * Tests that no Ajax success does not call e().
     *
     * @return void
     *
     * @global XH\PageDataRouter The page data router.
     */
    public function testNoAjaxSuccessDoesNotCallE()
    {
        global $pd_router;

        $pd_router->expects($this->any())->method('update')
            ->will($this->returnValue(true));
        $this->eMock->expects($this->never());
        $this->subject->handleSavePageData();
    }

    /**
     * Tests that no Ajax failure calls e().
     *
     * @return void
     *
     * @global XH\PageDataRouter The page data router.
     */
    public function testNoAjaxFailureCallsE()
    {
        global $pd_router;

        $pd_router->expects($this->any())->method('update')
            ->will($this->returnValue(false));
        $this->eMock->expects($this->once());
        $this->subject->handleSavePageData();
    }
}

/**
 * Testing the handling of page data editor requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerPageDataEditorTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * The page data editor mock.
     *
     * @var XH\PageDataEditor
     */
    protected $pageDataEditorMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = $this->getMock(
            'XH\Controller', array('makePageDataEditor')
        );
        $this->pageDataEditorMock = $this->getMockBuilder('XH\PageDataEditor')
            ->disableOriginalConstructor()->getMock();
        $this->subject->expects($this->any())->method('makePageDataEditor')
            ->will($this->returnValue($this->pageDataEditorMock));
    }

    /**
     * Tests that XH\PageDataEditor::process() is called.
     *
     * @return void
     */
    public function testCallsProcess()
    {
        $this->pageDataEditorMock->expects($this->once())->method('process');
        $this->subject->handlePageDataEditor();
    }
}

/**
 * Testing the handling of file view requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerFileViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
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
     *
     * @return void
     *
     * @global string The name of a special file to be handled.
     */
    public function setUp()
    {
        global $file;

        $this->setUpFileSystem();
        $file = 'content';
        $this->subject = new XH\Controller();
        $this->exitMock = new PHPUnit_Extensions_MockFunction(
            'XH_exit', $this->subject
        );
        $this->headerMock = new PHPUnit_Extensions_MockFunction(
            'header', $this->subject
        );
        $this->logFileViewMock = new PHPUnit_Extensions_MockFunction(
            'XH_logFileView', $this->subject
        );
    }

    /**
     * Sets up the file system.
     *
     * @return void
     *
     * @global array The paths of system files and folders.
     */
    protected function setUpFileSystem()
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth['file']['content'] = vfsStream::url('test/content.htm');
        file_put_contents($pth['file']['content'], 'foo');
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
     * Calls XH\Controller::handleFileView() while buffering output.
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
     *
     * @global string The name of a special file to be handled.
     */
    public function testLogFile()
    {
        global $file;

        $file = 'log';
        $this->logFileViewMock->expects($this->once());
        $this->subject->handleFileView();
    }
}

/**
 * Testing the handling of file backup requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerFileBackupTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global string            The name of a special file to be handled.
     * @global XH\CSRFProtection The CRSF protector.
     */
    public function setUp()
    {
        global $file, $_XH_csrfProtection;

        $_POST['xh_suffix'] = 'extra';
        $file = 'content';
        $_XH_csrfProtection = $this->getMockBuilder('XH\CSRFProtection')
            ->disableOriginalConstructor()->getMock();
        $this->subject = new XH\Controller();
        $this->extraBackupMock = new PHPUnit_Extensions_MockFunction(
            'XH_extraBackup', $this->subject
        );
    }

    /**
     * Tests that the CSRF token is checked.
     *
     * @return void
     *
     * @global XH\CSRFProtection The CRSF protector.
     */
    public function testChecksCsrfToken()
    {
        global $_XH_csrfProtection;

        $_XH_csrfProtection->expects($this->once())->method('check');
        $this->subject->handleFileBackup();
    }

    /**
     * Tests that XH_extraBackup() is called.
     *
     * @return void
     */
    public function testCallsExtraBackup()
    {
        $this->extraBackupMock->expects($this->once());
        $this->subject->handleFileBackup();
    }
}

/**
 * Testing the handling of file edit requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerFileEditTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * The file editor mock.
     *
     * @var XH\FileEdit
     */
    protected $fileEditorMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = $this->getMock('XH\Controller', array('makeFileEditor'));
        $this->fileEditorMock = $this->getMockBuilder('XH\CoreConfigFileEdit')
            ->disableOriginalConstructor()->getMock();
        $this->subject->expects($this->any())->method('makeFileEditor')
            ->will($this->returnValue($this->fileEditorMock));
    }

    /**
     * Tests that the array action calls XH\FileEdit::form().
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
     * Tests that the save action calls XH\FileEdit::submit().
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

/**
 * Testing the rendering of error messages.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.5
 */
class ControllerRenderErrorMessagesTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
     */
    protected $subject;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        global $e;

        $e = '<li>First error</li>'
            . '<li>Second error</li>';
        $this->subject = new XH\Controller();
    }

    /**
     * Tests render error messages.
     *
     * @return void
     */
    public function testRenderErrorMessages()
    {
        @$this->assertTag(
            [
                'tag' => 'ul',
                'parent' => [
                    'tag' => 'div', 'attributes' => ['class' => 'xh_warning']
                ],
                'children' => ['count' => 2, 'only' => ['tag' => 'li']]
            ],
            $this->subject->renderErrorMessages()
        );
    }
}

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
class ControllerStandardHeaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var XH\Controller
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
        $this->subject = new XH\Controller();
        $this->headersSentMock = new PHPUnit_Extensions_MockFunction(
            'headers_sent', $this->subject
        );
        $this->headerMock = new PHPUnit_Extensions_MockFunction(
            'header', $this->subject
        );
        $this->exitMock = new PHPUnit_Extensions_MockFunction(
            'XH_exit', $this->subject
        );
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

?>
