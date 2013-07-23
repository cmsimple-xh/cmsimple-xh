<?php

/**
 * Testing the functions in functions.php.
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
include '../../cmsimple/functions.php';

/**
 * A helper to test multiple evaluation of a function with side effects.
 */
function counter()
{
    static $count = 0;

    return ++$count;
}

/**
 * A test case for the functions in functions.php.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class FunctionsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $var, $cf, $tx;

        include '../../cmsimple/config.php';
        include '../../cmsimple/languages/en.php';
        $_SERVER['SERVER_NAME'] = 'example.com';
        $var = 'baz';
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testAutogalleryIsDeprecated()
    {
        autogallery('');
    }

    /**
     * @todo add more tests
     */
    public function dataForTestEvaluateCmsimpleScripting()
    {
        return array(
            array('foo bar', true, 'foo bar'),
            array('foo #CMSimple $output .= \'baz\';# bar', true, 'foo  barbaz'),
            array('foo #CMSimple $output .= $var;# bar', true, 'foo  barbaz'),
            array('foo #CMSimple hide# bar', true, 'foo #CMSimple hide# bar')
        );
    }

    /**
     * @dataProvider dataForTestEvaluateCmsimpleScripting
     */
    public function testEvaluateCmsimpleScripting($str, $compat, $expected)
    {
        $actual = evaluate_cmsimple_scripting($str, $compat);
        $this->assertEquals($expected, $actual);
    }

    public function testEvaluateCmsimpleScriptingKeywords()
    {
        $str = 'foo #CMSimple $keywords = \'foo, bar\';# bar';
        $expected = 'foo  bar';
        $actual = evaluate_cmsimple_scripting($str, true);
        $this->assertEquals($expected, $actual);
        $this->assertEquals('foo, bar', $GLOBALS['keywords']);
    }

    public function dataForSpliceString()
    {
        return array(
            array('foobarbaz', 3, 3, 'test', 'bar', 'footestbaz'),
            array('foobarbaz', 3, 3, '', 'bar', 'foobaz'),
            array('foobaz', 3, 0, 'bar', '', 'foobarbaz')
        );
    }

    /**
     * @dataProvider dataForSpliceString
     */
    public function testSpliceString($string, $offset, $length, $replacement, $expectedResult, $expectedString)
    {
        $actual = XH_spliceString($string,$offset, $length, $replacement);
        $this->assertEquals($expectedResult, $actual);
        $this->assertEquals($expectedString, $string);
    }

    public function dataForTestEvaluatePluginCall()
    {
        return array(
            array('foo bar','foo bar'),
            array('foo {{{PLUGIN:trim(\'baz\');}}} bar', 'foo baz bar'),
            array('foo {{{PLUGIN:trim($var);}}} bar', 'foo baz bar'),
            array( // evaluation of plugin calls in order of their appearance
                'foo {{{PLUGIN:counter();}}} bar {{{PLUGIN:counter();}}} baz',
                'foo 1 bar 2 baz'
            ),
            array( // function does not exist
                'foo {{{PLUGIN:doesnotexist();}}} bar',
                'foo <span class="cmsimplecore_fail">Function doesnotexist()'
                . ' is not defined!</span> bar'
            ),
            array('foo {{{PLUGIN:trim(\':\');}}} bar', 'foo : bar')
        );
    }

    /**
     * @dataProvider dataForTestEvaluatePluginCall
     */
    public function testEvaluatePluginCall($str, $expected)
    {
        $actual = evaluate_plugincall($str);
        $this->assertEquals($expected, $actual);
    }

    public function testEvaluatePluginCallKeywords()
    {
        $str = 'foo {{{PLUGIN:sscanf(\'baz\', \'%s\', $keywords);}}} bar';
        $expected = 'foo 1 bar';
        $actual = evaluate_plugincall($str, true);
        $this->assertEquals($expected, $actual);
        $this->assertFalse(isset($GLOBALS['keywords']));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testAmpIsDeprecated()
    {
        amp();
    }

    public function dataForTestAmp()
    {
        return array(
            array('true', '&amp;'),
            array('', '&')
        );
    }

    /**
     * @dataProvider dataForTestAmp()
     */
    public function testAmp($xhtmlAmp, $expected)
    {
        global $cf;

        $cf['xhtml']['amp'] = $xhtmlAmp;
        $actual = @amp(); // suppress deprecated warning
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestRmanl()
    {
        return array(
            array("\r\nFoo\r\n\n\rBar\rBaz\n", "FooBarBaz"),
            array('Foo Bar', 'Foo Bar')
        );
    }

    /**
     * @dataProvider dataForTestRmanl
     */
    public function testRmanl($str, $expected)
    {
        $actual = rmanl($str);
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestRmws()
    {
        return array(
            array('  Foo  Bar ', ' Foo Bar '),
            array("\t Foo \t Bar \t", ' Foo Bar ')
        );
    }

    /**
     * @dataProvider dataForTestRmws
     */
    public function testRmws($str, $expected)
    {
        $actual = xh_rmws($str);
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestRmnl()
    {
        return array(
            array("\r\nFoo\r\n\n\rBar\rBaz\n", "\nFoo\nBar\nBaz\n"),
            array('Foo Bar', 'Foo Bar')
        );
    }

    /**
     * @dataProvider dataForTestRmnl
     */
    public function testRmnl($str, $expected)
    {
        $actual = rmnl($str);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testRpIsDeprecated()
    {
        rp('');
    }

    public function dataForTestRp()
    {
        return array(
            array('./FunctionsTest.php', __FILE__),
            array('./DoesNotExist', './DoesNotExist')
        );
    }

    /**
     * @dataProvider dataForTestRp
     */
    public function testRp($filename, $expected)
    {
        $actual = @rp($filename); // suppress deprecated warning
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestSv()
    {
        return array(
            array('', ''),
            array('SERVER_NAME', 'example.com')
        );
    }

    /**
     * @dataProvider dataForTestSv
     */
    public function testSv($key, $expected)
    {
        $actual = sv($key);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testChkdlIsDeprecated()
    {
        chkdl('dummy');
    }

    public function dataForTestTag()
    {
        return array(
            array('', 'br', '<br>'),
            array('true', 'br', '<br />')
        );
    }

    /**
     * @dataProvider dataForTestTag
     */
    public function testTag($xhtmlEndtags, $str, $expected)
    {
        global $cf;

        $cf['xhtml']['endtags'] = $xhtmlEndtags;
        $actual = tag($str);
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestAdjustStylesheetURLs()
    {
        return array(
            array(
                'pagemanager',
                '#pagemanager {background-url: url(images/bg.jpg)}',
                '#pagemanager {background-url: url(../plugins/pagemanager/css/'
                . 'images/bg.jpg)}'
            ),
            array(
                'filebrowser',
                '.filebrowser {background-url: url("http://www.example.com/img.png")',
                '.filebrowser {background-url: url("http://www.example.com/img.png")'
            ),
            array(
                'plugin',
                'div {whatever: url( \'/images/anim.gif\' )}',
                'div {whatever: url( \'/images/anim.gif\' )}'
            ),
            array(
                'test',
                'body {background: url("./images/bg.jpg)}',
                'body {background: url("../plugins/test/css/./images/bg.jpg)}'
            )
        );
    }

    /**
     * @dataProvider dataForTestAdjustStylesheetURLs
     */
    public function testAdjustStylesheetURLs($plugin, $css, $expected)
    {
        $actual = XH_adjustStylesheetURLs($plugin, $css);
        $this->assertEquals($expected, $actual);
    }

    public function testMeta()
    {
        $matcher = array(
            'tag' => 'meta',
            'attributes' => array('name' => 'robots', 'content' => 'index, follow')
        );
        $actual = meta('robots');
        $this->assertTag($matcher, $actual);
    }

    public function dataForIsContentBackup()
    {
        return array(
            array('20130711_010203_content.htm', true),
            array('2013-07-11-01-02-03-content.htm', false)
        );
    }

    /**
     * @dataProvider dataForIsContentBackup
     */
    public function testIsContentBackup($filename, $expected)
    {
        $actual = XH_isContentBackup($filename);
        $this->assertEquals($expected, $actual);
    }
}

?>
