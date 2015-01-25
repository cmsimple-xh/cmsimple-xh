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
 * The file under test.
 */
require_once './cmsimple/classes/LinkChecker.php';

/**
 * A test stub to avoid actual checking of external links.
 *
 * @todo Refactor out external request from XH_LinkChecker::checkExternalLink().
 */
class TestingLinkChecker extends XH_LinkChecker
{
    function checkExternalLink($parts)
    {
        // request to IDN will fail
        if (preg_match('/[\x80-\xFF]/', $parts['host'])) {
            return 'externalfail';
        }
        return '200';
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

    public function testGatherLinks()
    {
        list($hrefs, $texts, $checkedLinks) = $this->linkChecker->gatherLinks();
        $expected = array(
            array(
                'http://www.cmsimple-xh.org/',
                'http://cmsimpleforum.com/viewtopic.php?f=29&t=5376'
            ),
            array('?Welcome:About#content'),
            array()
        );
        $this->assertEquals($expected, $hrefs);
        $expected = array(
            array('CMSimple_XH', 'forum'),
            array('Top'),
            array()
        );
        $this->assertEquals($expected, $texts);
        $this->assertEquals(3, $checkedLinks);
    }

    public function dataForLinkStatus()
    {
        return array(
            array('?Welcome', '200'),
            array('?Welkome', 'internalfail'),
            array('?Welcome:About#content', '200'),
            array('?Welcome:About#template', '200'),
            array('?Welcome:About#doesnotexist', 'anchor missing'),
            array('doesnotexist/?Welcome', 'file not found'), 
            array('./?download=template.htm', '200'),
            array('./?download=doesnotexist', 'file not found'),
            array('./?download=doesnotexist.txt?v01', 'file not found'),
            array('http://www.cmsimple-xh.org/', '200'),
            array('mailto:devs@cmsimple-xh.org', 'mailto'),
            array('./tests/unit/data/template.htm', '200'),
            array('./tests/unit/data/template.htm?v=1', '200'),
            array('./tests/unit/data/doesnotexist', 'file not found'),
            array('./tests/unit/data/doesnotexist?v=1', 'file not found'),
            array('./tests/unit/data/file%20name.txt', '200'),
            array('?sitemap', '200'),
            array('?mailform', '200'),
            // TODO: add checks for second languages, what is actually too cumbersome

            // the following are (current) limitations
            array('https://bugs.php.net', 'unknown'), // no HTTPS protocol support
            array('./tests/unit/data/', 'internalfail'), // fails, even there's a index.(php|html)
            array('./tests/unit/anotherxh/?Welcome', '200'), // erroneously checks the same installation 
            array('anotherxh/?Welcome2', 'file not found'), // fails, even if anotherxh/ would exist
            array('?Secret', '200'), // does not respect unpublished pages
            array("http://www.\xC3\xA4rger.de/", 'externalfail') // can't handle IDNs
        );
    }

    /**
     * @dataProvider dataForLinkStatus()
     */
    public function testLinkStatus($link, $expected)
    {
        $actual = $this->linkChecker->linkStatus($link);
        $this->assertEquals($expected, $actual);
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
        $error = array('400', '?Welcome', 'Start Page');
        $actual = $this->linkChecker->reportError($error);
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
        $notice = array('300', $url, $text);
        $actual = $this->linkChecker->reportNotice($notice);
        @$this->assertTag($matcher, $actual);
    }

    public function testMessage()
    {
        $hints = array(
            0 => array(
                'caveats' => array(
                    array('mailto', 'devs@cmsimple-xh.org', 'Developers')
                )
            ),
            1 => array(
                'errors' => array(
                    array('internalfail', '?Welcome', 'Start Page')
                ),
                'caveats' => array(
                    array('mailto', 'devs@cmsimple-xh.org', 'Developers')
                )
            )
        );
        $actual = $this->linkChecker->message(7, $hints);
        @$this->assertSelectCount('h4', 2, $actual);
        @$this->assertSelectCount('h5', 3, $actual);
    }
}

?>
