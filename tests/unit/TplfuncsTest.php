<?php

/**
 * Testing the functions in adminfuncs.php.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

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

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testGuestbooklinkIsDeprecated()
    {
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

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testEditmenuIsDeprecated()
    {
        editmenu();
    }

    /**
     * Tests the link to the previous page.
     *
     * @return void
     *
     * @global array The localization of the core.
     * @global int   The index of the requested page.
     */
    public function testPreviouspage()
    {
        global $tx, $s;

        $s = 10;
        $hideMock = $this->createFunctionMock('hide');
        $hideMock->expects($this->any())->willReturn(false);
        $this->assertXPathContains(
            '//a[@rel="prev"]',
            $tx['navigator']['previous'],
            previouspage()
        );
        $hideMock->restore();
    }

    /**
     * Tests that there's no link to the previous page if there is none.
     *
     * @return void
     *
     * @global int The index of the requested page.
     */
    public function testNoPreviousPage()
    {
        global $s;

        $s = 10;
        $hideMock = $this->createFunctionMock('hide');
        $hideMock->expects($this->any())->willReturn(true);
        $this->assertNull(previouspage());
        $hideMock->restore();
    }

    /**
     * Tests the link to the next page.
     *
     * @return void
     *
     * @global int   The index of the requested page.
     * @global int   The number of pages.
     */
    public function testNextpage()
    {
        global $s, $cl;

        $s = 0;
        $cl = 10;
        $hideMock = $this->createFunctionMock('hide');
        $hideMock->expects($this->any())->willReturn(false);
        $this->assertXPathContains(
            '//a[@rel="next"]',
            'next', /*$tx['navigator']['next']*/
            nextpage()
        );
        $hideMock->restore();
    }

    /**
     * Tests that there's no link to the next page if there is none.
     *
     * @return void
     *
     * @global int The index of the requested page.
     * @global int The number of pages.
     */
    public function testNoNextPage()
    {
        global $s, $cl;

        $s = 0;
        $cl = 10;
        $hideMock = $this->createFunctionMock('hide');
        $hideMock->expects($this->any())->willReturn(true);
        $this->assertNull(nextpage());
        $hideMock->restore();
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
     *
     * @global array The paths of system files and folders.
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
}
