<?php

/**
 * Testing the controller functionality.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the handling of sitemap requests.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerSitemapTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
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
    protected function setUp()
    {
        global $cl, $tx;

        $cl = 10;
        $tx['title'] = array(
            'sitemap' => 'Sitemap'
        );
        $this->subject = $this->getMockBuilder(Controller::class)->setMethods(null)->getMock();
        $this->hideMock = $this->createFunctionMock('hide');
        $this->liMock = $this->createFunctionMock('li');
    }

    protected function tearDown()
    {
        $this->hideMock->restore();
        $this->liMock->restore();
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
        $this->assertXPathContains('//h1', 'Sitemap', $o);
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
