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
 * Testing the setting of frontend $f.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerFrontendFTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = new Controller();
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
