<?php

/**
 * Testing the functions in adminfuncs.php.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Error\Deprecated as Deprecated;

/**
 * A test case for the functions in tplfuncs.php.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class TplfuncsTest extends TestCase
{
    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setUp()
    {
        global $cf, $tx, $onload;

        include './cmsimple/config.php';
        include './cmsimple/languages/en.php';
        $this->setConstant('XH_ADM', true);
        $onload = 'foo()';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
    }

    public function testSitename()
    {
        $actual = sitename();
        $this->assertNotEmpty($actual);
    }

    public function testPagename()
    {
        $actual = pagename();
        $this->assertEmpty($actual);
    }

    public function testOnload()
    {
        $expected = ' onload="foo()"';
        $actual = onload();
        $this->assertEquals($expected, $actual);
    }

    public function testSearchbox()
    {
        $actual = searchbox();
        $this->assertXPath(
            '//form[@id="searchbox" and @method="get"]',
            $actual
        );
    }

    public function testSitemaplink()
    {
        global $tx;

        $actual = sitemaplink();
        $this->assertXPathContains('//a', $tx['menu']['sitemap'], $actual);
    }

    public function testSitemaplinkActive()
    {
        global $tx, $f;

        $f = 'sitemap';
        $expected = $tx['menu']['sitemap'];
        $actual = sitemaplink();
        $this->assertEquals($expected, $actual);
    }

    public function testMailformlinkNoEmail()
    {
        global $cf;

        $cf['mailform']['email'] = '';
        $actual = mailformlink();
        $this->assertEmpty($actual);
    }

    public function testMailformlink()
    {
        global $cf, $tx;

        $cf['mailform']['email'] = 'me@example.com';
        $actual = mailformlink();
        $this->assertXPathContains('//a', $tx['menu']['mailform'], $actual);
    }

    public function testMailformlinkActive()
    {
        global $cf, $tx, $f;

        $f = 'mailform';
        $email = 'me@example.com';
        $cf['mailform']['email'] = $email;
        $expected = $tx['menu']['mailform'];
        $actual = mailformlink();
        $this->assertEquals($expected, $actual);
    }

    public function testGuestbooklinkIsDeprecated()
    {
        $this->expectException(Deprecated::class);
        guestbooklink();
    }

    public function testEditmenu()
    {
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_USER_DEPRECATED);
        $actual = editmenu();
        error_reporting($errorReporting);
        $this->assertEmpty($actual);
    }

    public function testEditmenuIsDeprecated()
    {
        $this->expectException(Deprecated::class);
        editmenu();
    }

    /**
     * Tests the link to the previous page.
     *
     * @return void
     */
    public function testPreviouspage()
    {
        global $tx;

        $findPreviousPageMock = $this->createFunctionMock('XH_findPreviousPage');
        $findPreviousPageMock->expects($this->any())->willReturn(1);
        $getPageUrlMock = $this->createFunctionMock('XH_getPageURL');
        $getPageUrlMock->expects($this->any())->willReturn('some URL');
        $this->assertXPathContains(
            '//a[@rel="prev"]',
            $tx['navigator']['previous'],
            previouspage()
        );
        $getPageUrlMock->restore();
        $findPreviousPageMock->restore();
    }

    /**
     * Tests that there's no link to the previous page if there is none.
     *
     * @return void
     */
    public function testNoPreviousPage()
    {
        global $s;

        $s = 10;
        $findPreviousPageMock = $this->createFunctionMock('XH_findPreviousPage');
        $findPreviousPageMock->expects($this->any())->willReturn(false);
        $this->assertNull(previouspage());
        $findPreviousPageMock->restore();
    }

    /**
     * Tests the link to the next page.
     *
     * @return void
     */
    public function testNextpage()
    {
        $findNextPageMock = $this->createFunctionMock('XH_findNextPage');
        $findNextPageMock->expects($this->any())->willReturn(1);
        $getPageUrlMock = $this->createFunctionMock('XH_getPageURL');
        $getPageUrlMock->expects($this->any())->willReturn('some URL');
        $this->assertXPathContains(
            '//a[@rel="next"]',
            'next', /*$tx['navigator']['next']*/
            nextpage()
        );
        $getPageUrlMock->restore();
        $findNextPageMock->restore();
    }

    /**
     * Tests that there's no link to the next page if there is none.
     *
     * @return void
     */
    public function testNoNextPage()
    {
        $findNextPageMock = $this->createFunctionMock('XH_findNextPage');
        $findNextPageMock->expects($this->any())->willReturn(false);
        $this->assertNull(nextpage());
        $findNextPageMock->restore();
    }

    public function testTop()
    {
        $actual = top();
        $this->assertXPath('//a[@href="#TOP"]', $actual);
    }

    /**
     * Tests languagemenu().
     *
     * @return void
     */
    public function testLanguageMenu()
    {
        global $pth;

        $pth = array(
            'folder' => array('base' => './', 'flags' => vfsStream::url('test/'), 'templateflags' => 'foo')
        );
        touch($pth['folder']['flags'] . 'da.gif');
        $secondLanguagesMock = $this->createFunctionMock('XH_secondLanguages');
        $secondLanguagesMock->expects($this->any())->willReturn(array('da', 'de'));
        $this->assertXPathContains(
            '//a[@href="./de/"]',
            'Deutsch',
            languagemenu()
        );
        $this->assertXPath(
            sprintf(
                '//a[@href="./da/"]/img[@class="flag" and @alt="Dansk" and @title="Dansk" and @src="%s"]',
                "{$pth['folder']['flags']}da.gif"
            ),
            languagemenu()
        );
        $secondLanguagesMock->restore();
    }

    /**
     * @return void
     */
    public function testPoweredByLink()
    {
        $expected = '<a href="https://cmsimple-xh.org/" target="_blank">'
            . 'Powered by CMSimple_XH</a>';
        $this->assertEquals($expected, poweredByLink());
    }
}
