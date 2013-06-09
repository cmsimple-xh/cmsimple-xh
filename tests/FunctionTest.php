<?php

/**
 * @version $Id$
 */


include '../cmsimple/functions.php';


class FunctionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $var;

        $_SERVER['SERVER_NAME'] = 'example.com';
        $var = 'baz';
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

    public function dataForTestEvaluatePluginCall()
    {
        return array(
            array('foo bar','foo bar'),
            array('foo {{{PLUGIN:trim(\'baz\');}}} bar', 'foo baz bar'),
            array('foo {{{PLUGIN:trim($var);}}} bar', 'foo baz bar')
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
        $this->assertEquals('baz', $GLOBALS['keywords']);
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
            array('./FunctionTest.php', __FILE__),
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

    public function dataForTestEncodeMIMEFieldBody()
    {
        return array(
            array('foo bar', 'foo bar'),
            array(str_repeat('foo bar ', 20), str_repeat('foo bar ', 20)),
            array("f\xC3\xB6o", '=?UTF-8?B?ZsO2bw==?='),
            array(
                str_repeat("\xC3\xA4\xC3\xB6\xC3\xBC", 10),
                "=?UTF-8?B?w6TDtsO8w6TDtsO8w6TDtsO8w6TDtsO8w6TDtsO8w6TDtsO8w6TDtsO8w6Q=?="
                . "\r\n =?UTF-8?B?w7bDvMOkw7bDvMOkw7bDvA==?="
            )
        );
    }

    /**
     * @dataProvider dataForTestEncodeMIMEFieldBody
     */
    public function testEncodeMIMEFieldBody($str, $expected)
    {
        $actual = XH_encodeMIMEFieldBody($str);
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestIsValidEmail()
    {
        return array(
            array('post@example.com', true),
            array("me@\xC3A4rger.de", false),
            array("hacker@example.com\r\n\r\n", false)
        );
    }

    /**
     * @dataProvider dataForTestIsValidEmail
     */
    public function testIsValidEmail($address, $expected)
    {
        $actual = XH_isValidEmail($address);
        $this->assertEquals($expected, $actual);
    }
}

?>
