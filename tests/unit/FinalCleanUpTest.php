<?php

/**
 * Testing XH_finalCleanUp().
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/**
 * A test case for XH_finalCleanUp().
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class FinalCleanUpTest extends PHPUnit_Framework_TestCase
{
    const HTML = '<html><head></head><body></body></html>';

    private $adminMenuStub;

    private $pluginsStub;

    /**
     * Sets up the default fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $this->_setUpFunctionStubs();
        $this->_setConstant('XH_ADM', true);
        $this->_setUpGlobals();
    }

    /**
     * Sets up the function stubs.
     *
     * @return void
     */
    private function _setUpFunctionStubs()
    {
        $this->adminMenuStub = new PHPUnit_Extensions_MockFunction('XH_adminMenu', null);
        $this->adminMenuStub->expects($this->any())->willReturn('<ul id="my_admin_menu"></ul>');
        $this->pluginsStub = new PHPUnit_Extensions_MockFunction('XH_plugins', null);
        $this->pluginsStub->expects($this->any())->willReturn(array('filebrowser', 'jquery', 'pagemanager'));
    }

    /**
     * Sets up the global variables of the test fixture.
     *
     * @return void
     *
     * @global array  PHP errors, warnings and notices.
     * @global array  The configuration of the core.
     * @global string The script elements to insert at the bottom of the body.
     */
    private function _setUpGlobals()
    {
        global $errors, $cf, $bjs;

        $errors = array('foo', 'bar');
        $cf = array(
            'editmenu' => array(
                'external' => '',
                'scroll' => ''
            )
        );
        $bjs = '<script>alert(1);</script>';
    }

    /**
     * Tears down the function stubs.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->adminMenuStub->restore();
        $this->pluginsStub->restore();
    }

    /**
     * Tests that $bjs is emitted.
     *
     * @return void
     */
    public function testEmitsBjs()
    {
        $matcher = array(
            'tag' => 'body',
            'child' => array(
                'tag' => 'script',
                'content' => 'alert(1);'
            )
        );
        $this->_assertResultMatches($matcher);
    }

    /**
     * Tests that the debug notice is not shown when error_reporting is off.
     *
     * @return void
     */
    public function testDebugModeNoticeNotShownWhenErrorReportingOff()
    {
        $errorReporting = error_reporting(0);
        $matcher = array(
            'tag' => 'body',
            'descendant' => array(
                'tag' => 'div',
                'attributes' => array('class' => 'xh_debug')
            )
        );
        @$this->assertNotTag($matcher, XH_finalCleanUp(self::HTML));
        error_reporting($errorReporting);
    }

    /**
     * Tests that the debug notice is shown when error_reporting is on.
     *
     * @return void
     */
    public function testDebugModeNoticeShownWhenErrorReportingOn()
    {
        $matcher = array(
            'tag' => 'body',
            'descendant' => array(
                'tag' => 'div',
                'attributes' => array('class' => 'xh_debug')
            )
        );
        $output = XH_finalCleanUp(self::HTML);
        @$this->assertTag($matcher, $output);
    }

    /**
     * Tests the error list.
     *
     * @return void
     */
    public function testErrorList()
    {
        $matcher = array(
            'tag' => 'ul',
            'parent' => array(
                'tag' => 'div',
                'attributes' => array('class' => 'xh_debug_warnings')
            ),
            'children' => array(
                'count' => 2,
                'only' => array('tag' => 'li')
            )
        );
        $this->_assertResultMatches($matcher);
    }

    /**
     * Tests the fixed admin menu.
     *
     * @return void
     */
    public function testFixedAdminMenu()
    {
        $matcher = array(
            'tag' => 'div',
            'id' => 'xh_adminmenu_fixed',
            'parent' => array('tag' => 'body'),
            'child' => array(
                'tag' => 'ul',
                'id' => 'my_admin_menu'
            )
        );
        $this->_assertResultMatches($matcher);
    }

    /**
     * Tests the scrolling admin menu.
     *
     * @return void
     *
     * @global array The configuration of the core.
     */
    public function testScrollingAdminMenu()
    {
        global $cf;

        $cf['editmenu']['scroll'] = 'true';
        $matcher = array(
            'tag' => 'div',
            'id' => 'xh_adminmenu_scrolling',
            'parent' => array('tag' => 'body'),
            'child' => array(
                'tag' => 'ul',
                'id' => 'my_admin_menu'
            )
        );
        $this->_assertResultMatches($matcher);
    }

    /**
     * Test that $bjs is emitted in the front-end.
     *
     * @return void
     */
    public function testEmitsBjsInFrontEnd()
    {
        $this->_setConstant('XH_ADM', false);
        $matcher = array(
            'tag' => 'body',
            'child' => array(
                'tag' => 'script',
                'content' => 'alert(1);'
            )
        );
        $this->_assertResultMatches($matcher);
    }

    /**
     * Sets the value of a constant.
     *
     * @param string A name of a constant.
     * @param mixed  A value.
     *
     * @return void
     */
    private function _setConstant($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        } else {
            runkit_constant_redefine($name, $value);
        }
    }

    /**
     * Asserts whether the result of XH_finalCleanUp() matches a matcher.
     *
     * @param array $matcher A matcher.
     *
     * @return void
     */
    private function _assertResultMatches(array $matcher)
    {
        @$this->assertTag($matcher, XH_finalCleanUp(self::HTML));
    }

}

?>
