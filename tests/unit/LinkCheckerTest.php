<?php

/**
 * Testing the link checker.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './cmsimple/functions.php';

/**
 * A test stub to avoid actual checking of external links.
 */
class TestingLinkChecker extends XH_LinkChecker
{
    protected function makeHeadRequest($host, $path)
    {
        // request to IDN will fail
        if (preg_match('/[\x80-\xFF]/', $host)) {
            return false;
        }
        return 200;
    }
}

/**
 * A test case for the link checker.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class LinkCheckerTest extends PHPUnit_Framework_TestCase
{
    protected $linkChecker;

    public function setUp()
    {
        global $c, $u, $cl, $pth, $cf, $onload;

        $c = array(
            '<h1>Welcome</h1>'
            . '<a href="http://www.cmsimple-xh.org/">CMSimple_XH</a>'
            . '<a href="http://cmsimpleforum.com/viewtopic.php?f=29&amp;t=5376">forum</a>',
            '<h2>About</h2>'
            . '<a name="content"></a>'
            . '<a href="#content">Top</a>',
            '<h1>Secret</h1>'
            . '#CMSimple remove#'
        );
        $u = array(
            'Welcome',
            'Welcome:About',
            'Secret'
        );
        $cl = count($c);
        $pth = array(
            'folder' => array(
                'corestyle' => '',
                'downloads' => './tests/unit/data/'
            ),
            'file' => array('template' => './tests/unit/data/template.htm')
        );
        $cf = array(
            'mailform' => array('email' => 'devs@cmsimple-xh.org'),
            'xhtml' => array('endtags' => '1')
        );
        $onload = '';
        $this->linkChecker = new TestingLinkChecker();
    }

    public function testPrepare()
    {
        global $onload;

        $matcher = array(
            'id' => 'xh_linkchecker',
            'descendant' => array('tag' => 'img')
        );
        $actual = $this->linkChecker->prepare();
        @$this->assertTag($matcher, $actual);
        $this->stringStartsWith('XH.checkLinks(', $onload);
    }

    public function testCheckLinks()
    {
        $matcher = array('tag' => 'h4');
        $actual = $this->linkChecker->checkLinks();
        @$this->assertNotTag($matcher, $actual);
    }

    public function dataForLinkStatus()
    {
        return array(
            array('?Welcome', 200),
            array('?Welkome', XH_Link::STATUS_INTERNALFAIL),
            array('?Welcome:About#content', 200),
            array('?Welcome:About#template', 200),
            array('?Welcome:About#doesnotexist', XH_Link::STATUS_ANCHOR_MISSING),
            array('./?download=template.htm', 200),
            array('./?download=doesnotexist', XH_Link::STATUS_FILE_NOT_FOUND),
            array('./?download=doesnotexist.txt?v01', XH_Link::STATUS_FILE_NOT_FOUND),
            array('http://www.cmsimple-xh.org/', 200),
            array('mailto:devs@cmsimple-xh.org', XH_Link::STATUS_MAILTO),
            array('./tests/unit/data/template.htm', 200),
            array('./tests/unit/data/doesnotexist', XH_Link::STATUS_INTERNALFAIL),
            array('./tests/unit/data/doesnotexist?v=1', XH_Link::STATUS_INTERNALFAIL),
            array('./tests/unit/data/file%20name.txt', 200),
            array('?sitemap', 200),
            array('?mailform', 200),
            // TODO: add checks for second languages, what is actually too cumbersome

            // the following are (current) limitations
            array('https://bugs.php.net', XH_Link::STATUS_UNKNOWN), // no HTTPS protocol support
            array('./tests/unit/data/', XH_Link::STATUS_INTERNALFAIL), // fails, even there's a index.(php|html)
            array('./tests/unit/anotherxh/?Welcome', 200), // erroneously checks the same installation
            array('anotherxh/?Welcome2', XH_Link::STATUS_INTERNALFAIL), // fails, even if anotherxh/ would exist
            array('?Secret', 200), // does not respect unpublished pages
            array("http://www.\xC3\xA4rger.de/", XH_Link::STATUS_EXTERNALFAIL), // can't handle IDNs
            array('./tests/unit/data/template.htm?v=1', XH_Link::STATUS_INTERNALFAIL) // doesn't accept query string for existing files
        );
    }

    /**
     * @dataProvider dataForLinkStatus()
     */
    public function testLinkStatus($url, $expected)
    {
        $link = new XH_Link($url, '');
        $this->linkChecker->determineLinkStatus($link);
        $this->assertEquals($expected, $link->getStatus());
    }

    public function testReportError()
    {
        $matcher = array(
            'tag' => 'li',
            'descendant' => array(
                'tag' => 'a',
                'attributes' => array('href' => '?Welcome'),
                'content' => 'Start Page'
            )
        );
        $link = new XH_Link('?Welcome', 'Start Page');
        $link->setStatus(400);
        $actual = $this->linkChecker->reportError($link);
        @$this->assertTag($matcher, $actual);
    }

    public function testReportNotice()
    {
        $url = 'http://cmsimple-xh.org/';
        $text = 'Start Page';
        $matcher = array(
            'tag' => 'li',
            'descendant' => array(
                'tag' => 'a',
                'attributes' => array('href' => $url),
                'content' => $text
            )
        );
        $link = new XH_Link($url, $text);
        $link->setStatus(300);
        $actual = $this->linkChecker->reportNotice($link);
        @$this->assertTag($matcher, $actual);
    }

    public function testMessage()
    {
        $link1 = new XH_Link('devs@cmsimple-xh.org', 'Developers');
        $link1->setStatus(XH_Link::STATUS_MAILTO);
        $link2 = new XH_Link('?Welcome', 'Start Page');
        $link2->setStatus(XH_Link::STATUS_INTERNALFAIL);
        $hints = array(
            0 => array(
                'caveats' => array($link1)
            ),
            1 => array(
                'errors' => array($link2),
                'caveats' => array($link1)
            )
        );
        $actual = $this->linkChecker->message(7, $hints);
        @$this->assertSelectCount('h4', 2, $actual);
        @$this->assertSelectCount('h5', 3, $actual);
    }
}

?>
