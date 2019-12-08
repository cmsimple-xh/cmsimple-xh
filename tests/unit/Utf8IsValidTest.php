<?php

/**
 * Testing the UTF-8 related string functions.
 *
 * @category  CMSimple_XH
 * @package   Testing
 * @author    Harry Fuecks <hfuecks@gmail.com>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2006-2007 Harry Fuecks
 * @copyright 2009-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

class Utf8IsValidTest extends TestCase
{
    public function testValidUtf8()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertTrue(utf8_is_valid($string));
    }

    public function testValidUtf8Ascii()
    {
        $string = 'ABC 123';
        $this->assertTrue(utf8_is_valid($string));
    }

    public function testInvalidUtf8()
    {
        $string = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertFalse(utf8_is_valid($string));
    }

    public function testInvalidUtf8Ascii()
    {
        $string = "this is an invalid char '\xe9' here";
        $this->assertFalse(utf8_is_valid($string));
    }

    public function testEmptyString()
    {
        $string = '';
        $this->assertTrue(utf8_is_valid($string));
    }

    public function testValidTwoOctetId()
    {
        $string = "\xc3\xb1";
        $this->assertTrue(utf8_is_valid($string));
    }

    public function testInvalidTwoOctetSequence()
    {
        $string = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($string));
    }

    public function testInvalidIdBetweenTwoAndThree()
    {
        $string = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($string));
    }

    public function testValidThreeOctetId()
    {
        $string = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
        $this->assertTrue(utf8_is_valid($string));
    }

    public function testInvalidThreeOctetSequenceSecond()
    {
        $string = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($string));
    }

    public function testInvalidThreeOctetSequenceThird()
    {
        $string = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($string));
    }

    public function testValidFourOctetId()
    {
        $string = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertTrue(utf8_is_valid($string));
    }

    public function testInvalidFourOctetSequence()
    {
        $string = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($string));
    }

    public function testInvalidFiveOctetSequence()
    {
        $string = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($string));
    }

    public function testInvalidSixOctetSequence()
    {
        $string = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $this->assertFalse(utf8_is_valid($string));
    }

    public function testInvalidAtTheEnd()
    {
        $string = "\xE2\x82\xAC\xE2\x82";
        $this->assertFalse(utf8_is_valid($string));
    }
}
