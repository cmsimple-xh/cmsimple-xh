<?php

/**
 * Testing the functions in adminfuncs.php.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';
require_once './cmsimple/functions.php';

/**
 * The file under test.
 */
require_once './cmsimple/tplfuncs.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

if (!defined('XH_ADM')) {
    define('XH_ADM', true);
}

/**
 * A test case for the functions in tplfuncs.php.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class TplfuncsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $cf, $tx, $onload;

        include './cmsimple/config.php';
        include './cmsimple/languages/en.php';
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
        $matcher = array(
            'tag' => 'div',
            'id' => 'searchbox',
            'parent' => array(
                'tag' => 'form',
                'attributes' => array('method' => 'get')
            )
        );
        $actual = searchbox();
        @$this->assertTag($matcher, $actual);
    }

    public function testSitemaplink()
    {
        global $tx;

        $matcher = array(
            'tag' => 'a',
            'content' => $tx['menu']['sitemap']
        );
        $actual = sitemaplink();
        @$this->assertTag($matcher, $actual);
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
        $matcher = array(
            'tag' => 'a',
            'content' => $tx['menu']['mailform']
        );
        $actual = mailformlink();
        @$this->assertTag($matcher, $actual);
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

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testLegallinkIsDeprecated()
    {
        legallink();
    }

    public function testEditmenu()
    {
        $actual = editmenu();
        $this->assertEmpty($actual);
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
        $hideMock = new PHPUnit_Extensions_MockFunction('hide', null);
        $hideMock->expects($this->any())->will($this->returnValue(false));
        $matcher = array(
            'tag' => 'a',
            'attributes' => array('rel' => 'prev'),
            'content' => $tx['navigator']['previous']
        );
        @$this->assertTag($matcher, previouspage());
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
        $hideMock = new PHPUnit_Extensions_MockFunction('hide', null);
        $hideMock->expects($this->any())->will($this->returnValue(true));
        $this->assertNull(previouspage());
        $hideMock->restore();
    }

    /**
     * Tests the link to the next page.
     *
     * @return void
     *
     * @global array The localization of the core.
     * @global int   The index of the requested page.
     * @global int   The number of pages.
     */
    public function testNextpage()
    {
        global $tx, $s, $cl;

        $s = 0; $cl = 10;
        $hideMock = new PHPUnit_Extensions_MockFunction('hide', null);
        $hideMock->expects($this->any())->will($this->returnValue(false));
        $matcher = array(
            'tag' => 'a',
            'attributes' => array('rel' => 'next'),
            'content' => 'next' /*$tx['navigator']['next']*/
        );
        @$this->assertTag($matcher, nextpage());
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

        $s = 0; $cl = 10;
        $hideMock = new PHPUnit_Extensions_MockFunction('hide', null);
        $hideMock->expects($this->any())->will($this->returnValue(true));
        $this->assertNull(nextpage());
        $hideMock->restore();
    }

    public function testTop()
    {
        $matcher = array(
            'tag' => 'a',
            'attributes' => array('href' => '#TOP')
        );
        $actual = top();
        @$this->assertTag($matcher, $actual);
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
            'folder' => array('base' => './', 'flags' => vfsStream::url('test/'))
        );
        touch($pth['folder']['flags'] . 'da.gif');
        $secondLanguagesMock = new PHPUnit_Extensions_MockFunction(
            'XH_secondLanguages', null
        );
        $secondLanguagesMock->expects($this->any())->will(
            $this->returnValue(array('da', 'de'))
        );
        @$this->assertTag(
            array(
                'tag' => 'a',
                'attributes' => array('href' => './de/'),
                'content' => 'Deutsch'
            ),
            languagemenu()
        );
        @$this->assertTag(
            array(
                'tag' => 'a',
                'attributes' => array('href' => './da/'),
                'child' => array(
                    'tag' => 'img',
                    'attributes' => array(
                        'class' => 'flag',
                        'alt' => 'Dansk',
                        'title' => 'Dansk',
                        'src' => $pth['folder']['flags'] . 'da.gif'
                    )
                )
            ),
            languagemenu()
        );
        $secondLanguagesMock->restore();
    }
}

?>
