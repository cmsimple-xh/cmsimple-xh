<?php

/**
 * Testing the controller functionality.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the setting of frontend $f.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
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
     */
    protected function setUp(): void
    {
        $this->subject = new Controller();
    }

    /**
     * Tests $f == 'forgotten'.
     *
     * @return void
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
