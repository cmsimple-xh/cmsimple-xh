<?php

/**
 * Testing the functions in adminfuncs.php.
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

/**
 * The file under test.
 */
require_once '../../cmsimple/tplfuncs.php';

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

        include '../../cmsimple/config.php';
        include '../../cmsimple/languages/en.php';
        $onload = 'foo()';
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
        $this->assertTag($matcher, $actual);
    }

    public function testSitemaplink()
    {
        global $tx;

        $matcher = array(
            'tag' => 'a',
            'content' => $tx['menu']['sitemap']
        );
        $actual = sitemaplink();
        $this->assertTag($matcher, $actual);
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
        $this->assertTag($matcher, $actual);
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

    public function testEditmenu()
    {
        $actual = editmenu();
        $this->assertEmpty($actual);
    }

    public function testPreviouspage()
    {
        global $tx, $s;

        $s = 1;
        $matcher = array(
            'tag' => 'a',
            'content' => $tx['navigator']['previous']
        );
        $actual = previouspage();
        $this->assertTag($matcher, $actual);
    }

    public function testNextpage()
    {
        global $tx, $s, $cl;

        $s = -1; $cl = 1;
        $matcher = array(
            'tag' => 'a'/*,
            'content' => $tx['navigator']['next']*/
        );
        $actual = nextpage();
        $this->assertTag($matcher, $actual);
    }

    public function testTop()
    {
        $matcher = array(
            'tag' => 'a',
            'attributes' => array('href' => '#TOP')
        );
        $actual = top();
        $this->assertTag($matcher, $actual);
    }
}

?>
