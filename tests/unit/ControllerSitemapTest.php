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

use PHPUnit_Extensions_MockFunction;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

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
    public function setUp()
    {
        global $cl, $tx;

        $cl = 10;
        $tx['title'] = array(
            'sitemap' => 'Sitemap'
        );
        $this->subject = $this->getMock('XH\Controller', null);
        $this->hideMock = new PHPUnit_Extensions_MockFunction('hide', $this->subject);
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
