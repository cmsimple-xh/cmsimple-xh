<?php

/**
 * @version $Id$
 */


include '../cmsimple/functions.php';


class FunctionTest extends PHPUnit_Framework_TestCase
{
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
