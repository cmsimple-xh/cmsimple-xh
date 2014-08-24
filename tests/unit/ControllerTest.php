<?php

/**
 * Testing the controller functionality.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';
require_once './cmsimple/functions.php';
require_once './cmsimple/tplfuncs.php';
require_once './cmsimple/classes/Mailform.php';
require_once './cmsimple/classes/PasswordForgotten.php';
require_once './cmsimple/classes/Search.php';
require_once './cmsimple/classes/Controller.php';

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
     * @var XH_Controller
     */
    protected $subject;

    /**
     * The mailform mock.
     *
     * @var XH_Mailform
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
        $this->subject = $this->getMock('XH_Controller', array('makeMailform'));
        $this->mailformMock = $this->getMockBuilder('XH_Mailform')
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
     * @global string The (X)HTML of the content area.
     */
    public function testRenderedHTML()
    {
        global $o;

        $this->subject->handleMailform();
        $this->assertTag(
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
     * @var XH_Controller
     */
    protected $subject;

    /**
     * The search mock.
     *
     * @var XH_Search
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
        $this->subject = $this->getMock('XH_Controller', array('makeSearch'));
        $this->searchMock = $this->getMockBuilder('XH_Search')
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
     * @var XH_Controller
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
        $this->subject = $this->getMock('XH_Controller', null);
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
     * @global string The (X)HTML of the contents area.
     */
    public function testRenderedHTML()
    {
        global $o;

        $this->subject->handleSitemap();
        $this->assertTag(
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
     * @var XH_Controller
     */
    protected $subject;

    /**
     * The password forgotten mock.
     *
     * @var XH_PasswordForgotten
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
            'XH_Controller', array('makePasswordForgotten')
        );
        $this->passwordForgottenMock = $this->getMockBuilder('XH_PasswordForgotten')
            ->disableOriginalConstructor()->getMock();
        $this->subject->expects($this->any())->method('makePasswordForgotten')
            ->will($this->returnValue($this->passwordForgottenMock));
    }

    /**
     * Tests that XH_PasswordForgotten::dispatch() is called.
     *
     * @return void
     */
    public function testCallsDispatch()
    {
        $this->passwordForgottenMock->expects($this->once())->method('dispatch');
        $this->subject->handlePasswordForgotten();
    }
}

?>
