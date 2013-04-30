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
        $actual = @amp();
        $this->assertEquals($expected, $actual);
    }
}

?>
