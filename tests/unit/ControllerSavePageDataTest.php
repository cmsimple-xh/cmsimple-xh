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
 * Testing the handling of save page data requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerSavePageDataTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
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
     * @global int            The index of the currently selected page.
     * @global PageDataRouter The page data router.
     * @global CSRFProtection The CSRF protector.
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
        $this->subject = new Controller();
        $this->eMock = $this->getFunctionMock('e', $this->subject);
        $this->exitMock = $this->getFunctionMock('XH_exit', $this->subject);
        $this->headerMock = $this->getFunctionMock('header', $this->subject);
        $this->messageMock = $this->getFunctionMock('XH_message', $this->subject);
    }

    /**
     * Tests that PageDataRouter::update() is called.
     *
     * @return void
     *
     * @global int            The index of the currently selected page.
     * @global PageDataRouter The page data router.
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
     * @global PageDataRouter The page data router.
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
     * @global PageDataRouter The page data router.
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
     * @global PageDataRouter The page data router.
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
     * @global PageDataRouter The page data router.
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
