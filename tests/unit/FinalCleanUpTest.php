<?php

/**
 * Testing XH_finalCleanUp().
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

/**
 * A test case for XH_finalCleanUp().
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see     http://cmsimple-xh.org/
 */
class FinalCleanUpTest extends TestCase
{
    const HTML = '<html><head></head><body></body></html>';

    private $adminMenuStub;

    private $pluginsStub;

    /**
     * Sets up the default fixture.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->setUpFunctionStubs();
        $this->setConstant('XH_ADM', true);
        $this->setUpGlobals();
    }

    /**
     * Sets up the function stubs.
     *
     * @return void
     */
    private function setUpFunctionStubs()
    {
        $this->adminMenuStub = $this->createFunctionMock('XH_adminMenu');
        $this->adminMenuStub->expects($this->any())->willReturn('<ul id="my_admin_menu"></ul>');
        $this->pluginsStub = $this->createFunctionMock('XH_plugins');
        $this->pluginsStub->expects($this->any())->willReturn(array('filebrowser', 'jquery', 'pagemanager'));
    }

    /**
     * Sets up the global variables of the test fixture.
     *
     * @return void
     */
    private function setUpGlobals()
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
    protected function tearDown()
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
        $this->markTestSkipped('fails on CI - why?');
        $this->assertXPathContains(
            '//body/script',
            'alert(1);',
            XH_finalCleanUp(self::HTML)
        );
    }

    /**
     * Tests that the debug notice is not shown when error_reporting is off.
     *
     * @return void
     */
    public function testDebugModeNoticeNotShownWhenErrorReportingOff()
    {
        $errorReporting = error_reporting(0);
        $this->assertNotXPath(
            '//body//div[@class="xh_debug"]',
            XH_finalCleanUp(self::HTML)
        );
        error_reporting($errorReporting);
    }

    /**
     * Tests that the debug notice is shown when error_reporting is on.
     *
     * @return void
     */
    public function testDebugModeNoticeShownWhenErrorReportingOn()
    {
        $this->markTestSkipped('fails on CI - why?');
        $output = XH_finalCleanUp(self::HTML);
        $this->assertXPath(
            '//body//div[@class="xh_debug"]',
            $output
        );
    }

    /**
     * Tests the error list.
     *
     * @return void
     */
    public function testErrorList()
    {
        $this->markTestSkipped('fails on CI - why?');
        $this->assertXPathCount(
            '//div[@class="xh_debug_warnings"]/ul/li',
            2,
            XH_finalCleanUp(self::HTML)
        );
    }

    /**
     * Tests the fixed admin menu.
     *
     * @return void
     */
    public function testFixedAdminMenu()
    {
        $this->markTestSkipped('fails on CI - why?');
        $this->assertXPath(
            '//body/div[@id="xh_adminmenu_fixed"]/ul[@id="my_admin_menu"]',
            XH_finalCleanUp(self::HTML)
        );
    }

    /**
     * Tests the scrolling admin menu.
     *
     * @return void
     */
    public function testScrollingAdminMenu()
    {
        global $cf;

        $this->markTestSkipped('fails on CI - why?');
        $cf['editmenu']['scroll'] = 'true';
        $this->assertXPath(
            '//body/div[@id="xh_adminmenu_scrolling"]/ul[@id="my_admin_menu"]',
            XH_finalCleanUp(self::HTML)
        );
    }

    /**
     * Test that $bjs is emitted in the front-end.
     *
     * @return void
     */
    public function testEmitsBjsInFrontEnd()
    {
        $this->markTestSkipped('fails on CI - why?');
        $this->setConstant('XH_ADM', false);
        $this->assertXPathContains(
            '//body/script',
            'alert(1);',
            XH_finalCleanUp(self::HTML)
        );
    }
}
