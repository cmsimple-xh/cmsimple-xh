<?php

/**
 * Testing the link checker.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

/**
 * A test case for the link checker.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class LinkCheckerTest extends TestCase
{
    protected $linkChecker;

    protected function setUp()
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
            'mailform' => array('email' => 'devs@cmsimple-xh.org')
        );
        $onload = '';
        $this->linkChecker = $this->getMockBuilder(LinkChecker::class)
            ->setMethods(['makeHeadRequest'])
            ->getMock();
        $this->linkChecker->method('makeHeadRequest')
            ->will($this->returnCallback(function ($host) {
                // request to IDN will fail
                if (preg_match('/[\x80-\xFF]/', $host)) {
                    return false;
                }
                return 200;
            }));
    }

    public function testPrepare()
    {
        global $onload;

        $actual = $this->linkChecker->prepare();
        $this->assertXPath(
            '//*[@id="xh_linkchecker"]//img',
            $actual
        );
        $this->stringStartsWith('XH.checkLinks(', $onload);
    }

    public function testCheckLinks()
    {
        $actual = $this->linkChecker->checkLinks();
        $this->assertNotXPath('//h4', $actual);
    }

    public function dataForLinkStatus()
    {
        return array(
            array('?Welcome', 200),
            array('?Welkome', Link::STATUS_INTERNALFAIL),
            array('?Welcome:About#content', 200),
            array('?Welcome:About#template', 200),
            array('?Welcome:About#doesnotexist', Link::STATUS_ANCHOR_MISSING),
            array('./?download=template.htm', 200),
            array('./?download=doesnotexist', Link::STATUS_FILE_NOT_FOUND),
            array('./?download=doesnotexist.txt?v01', Link::STATUS_FILE_NOT_FOUND),
            array('http://www.cmsimple-xh.org/', 200),
            array('mailto:devs@cmsimple-xh.org', Link::STATUS_MAILTO),
            array('./tests/unit/data/template.htm', 200),
            array('./tests/unit/data/doesnotexist', Link::STATUS_INTERNALFAIL),
            array('./tests/unit/data/doesnotexist?v=1', Link::STATUS_INTERNALFAIL),
            array('./tests/unit/data/file%20name.txt', 200),
            array('?sitemap', 200),
            array('?mailform', 200),
            // TODO: add checks for second languages, what is actually too cumbersome

            // the following are (current) limitations
            array('https://bugs.php.net', Link::STATUS_UNKNOWN), // no HTTPS protocol support
            array('./tests/unit/data/', Link::STATUS_INTERNALFAIL), // fails, even there's a index.(php|html)
            array('./tests/unit/anotherxh/?Welcome', 200), // erroneously checks the same installation
            array('anotherxh/?Welcome2', Link::STATUS_INTERNALFAIL), // fails, even if anotherxh/ would exist
            array('?Secret', 200), // does not respect unpublished pages
            array("http://www.\xC3\xA4rger.de/", Link::STATUS_EXTERNALFAIL), // can't handle IDNs
            array('./tests/unit/data/template.htm?v=1', Link::STATUS_INTERNALFAIL)
                // doesn't accept query string for existing files
        );
    }

    /**
     * @dataProvider dataForLinkStatus()
     */
    public function testLinkStatus($url, $expected)
    {
        $link = new Link($url, '');
        $this->linkChecker->determineLinkStatus($link);
        $this->assertEquals($expected, $link->getStatus());
    }

    public function testReportError()
    {
        $link = new Link('?Welcome', 'Start Page');
        $link->setStatus(400);
        $actual = $this->linkChecker->reportError($link);
        $this->assertXPathContains(
            '//li//a[@href="?Welcome"]',
            'Start Page',
            $actual
        );
    }

    public function testReportNotice()
    {
        $url = 'http://cmsimple-xh.org/';
        $text = 'Start Page';
        $link = new Link($url, $text);
        $link->setStatus(300);
        $actual = $this->linkChecker->reportNotice($link);
        $this->assertXPathContains(
            sprintf('//li//a[@href="%s"]', $url),
            $text,
            $actual
        );
    }

    public function testMessage()
    {
        $link1 = new Link('devs@cmsimple-xh.org', 'Developers');
        $link1->setStatus(Link::STATUS_MAILTO);
        $link2 = new Link('?Welcome', 'Start Page');
        $link2->setStatus(Link::STATUS_INTERNALFAIL);
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
        $this->assertXPathCount('//h4', 2, $actual);
        $this->assertXPathCount('//h5', 3, $actual);
    }
}
