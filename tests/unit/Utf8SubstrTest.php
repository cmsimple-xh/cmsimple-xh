<?php

/**
 * Testing the UTF-8 related string functions.
 *
 * @author    Harry Fuecks <hfuecks@gmail.com>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2006-2007 Harry Fuecks
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

class Utf8SubstrTest extends TestCase
{
    public function testUtf8()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('Iñ', utf8_substr($string, 0, 2));
    }

    public function testUtf8Two()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('të', utf8_substr($string, 2, 2));
    }

    public function testUtf8Zero()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('Iñtërnâtiônàlizætiøn', utf8_substr($string, 0));
    }

    public function testUtf8ZeroZero()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('', utf8_substr($string, 0, 0));
    }

    public function testStartGreaterThanLength()
    {
        $string = 'Iñt';
        $this->assertEquals('', utf8_substr($string, 4));
    }

    public function testCompareStartGreaterThanLength()
    {
        $string = 'abc';
        $this->assertEquals(substr($string, 4), utf8_substr($string, 4));
    }

    public function testLengthBeyondString()
    {
        $string = 'Iñt';
        $this->assertEquals('ñt', utf8_substr($string, 1, 5));
    }

    public function testCompareLengthBeyondString()
    {
        $string = 'abc';
        $this->assertEquals(substr($string, 1, 5), utf8_substr($string, 1, 5));
    }

    public function testStartNegative()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('tiøn', utf8_substr($string, -4));
    }

    public function testLengthNegative()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('nàlizæti', utf8_substr($string, 10, -2));
    }

    public function testStartLengthNegative()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('ti', utf8_substr($string, -4, -2));
    }

    public function testLinefeed()
    {
        $string = "Iñ\ntërnâtiônàlizætiøn";
        $this->assertEquals("ñ\ntër", utf8_substr($string, 1, 5));
    }

    public function testLongLength()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals('Iñtërnâtiônàlizætiøn', utf8_substr($string, 0, 15536));
    }
}
