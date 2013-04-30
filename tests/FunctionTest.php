<?php

/**
 * @version $Id$
 */


include '../cmsimple/functions.php';


class FunctionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['SERVER_NAME'] = 'example.com';
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
}

?>
