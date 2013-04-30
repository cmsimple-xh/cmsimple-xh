<?php

/**
 * @version $Id$
 */


include '../cmsimple/functions.php';


class FunctionTest extends PHPUnit_Framework_TestCase
{
    public function dataForTestRp()
    {
        return array(
            array('./FunctionTest.php', __FILE__),
            array('./DoesNotExist', './DoesNotExist')
        );
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testRpIsDeprecated()
    {
        rp('');
    }

    /**
     * @dataProvider dataForTestRp
     */
    public function testRp($filename, $expected)
    {
        $actual = @rp($filename); // suppress deprecated warning
        $this->assertEquals($expected, $actual);
    }
}

?>
