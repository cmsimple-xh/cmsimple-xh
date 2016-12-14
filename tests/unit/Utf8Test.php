<?php

/**
 * Testing the UTF-8 related string functions.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Testing
 * @author    Harry Fuecks <hfuecks@gmail.com>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2006-2007 Harry Fuecks
 * @copyright 2009-2016 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './cmsimple/utf8.php';

class Utf8StrlenTest extends PHPUnit_Framework_TestCase
{
    public function testUtf8()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals(utf8_strlen($string), 20);
    }

    public function testUtf8Invalid()
    {
        $string = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEquals(utf8_strlen($string), 20);
    }

    public function testAscii()
    {
        $string = 'ABC 123';
        $this->assertEquals(utf8_strlen($string), 7);
    }

    public function testEmptyStr()
    {
        $string = '';
        $this->assertEquals(utf8_strlen($string), 0);
    }
}

class Utf8SubstrTest extends PHPUnit_Framework_TestCase
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

class Utf8StrtolowerTest extends PHPUnit_Framework_TestCase
{
    public function testLower()
    {
        $string = 'IÑTËRNÂTIÔNÀLIZÆTIØN';
        $this->assertEquals('iñtërnâtiônàlizætiøn', utf8_strtolower($string));
    }

    public function testEmptyString()
    {
        $string = '';
        $this->assertEquals('', utf8_strtolower($string));
    }
}

class Utf8StrtoupperTest extends PHPUnit_Framework_TestCase
{
    public function testUpper()
    {
        $string = 'iñtërnâtiônàlizætiøn';
        $this->assertEquals('IÑTËRNÂTIÔNÀLIZÆTIØN', utf8_strtoupper($string));
    }

    public function testEmptyString()
    {
        $string = '';
        $this->assertEquals('', utf8_strtoupper($string));
    }
}

class Utf8StrposTest extends PHPUnit_Framework_TestCase
{
    public function testUtf8()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals(6, utf8_strpos($string, 'â'));
    }

    public function testUtf8Offset()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals(19, utf8_strpos($string, 'n', 11));
    }

    public function testUtf8Invalid()
    {
        $string = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEquals(16, utf8_strpos($string, 'æ'));
    }

    public function testAscii()
    {
        $string = 'ABC 123';
        $this->assertEquals(1, utf8_strpos($string, 'B'));
    }

    public function testVsStrpos()
    {
        $string = 'ABC 123 ABC';
        $this->assertEquals(strpos($string, 'B', 3), utf8_strpos($string, 'B', 3));
    }

    public function testEmptyStr()
    {
        $string = '';
        $this->assertFalse(utf8_strpos($string, 'x'));
    }
}

class Utf8UcfirstTest extends PHPUnit_Framework_TestCase
{
    public function testUcfirst()
    {
        $string = 'ñtërnâtiônàlizætiøn';
        $this->assertEquals('Ñtërnâtiônàlizætiøn', utf8_ucfirst($string));
    }

    public function testUcfirstSpace()
    {
        $string = ' iñtërnâtiônàlizætiøn';
        $this->assertEquals(' iñtërnâtiônàlizætiøn', utf8_ucfirst($string));
    }

    public function testUcfirstUpper()
    {
        $string = 'Ñtërnâtiônàlizætiøn';
        $this->assertEquals('Ñtërnâtiônàlizætiøn', utf8_ucfirst($string));
    }

    public function testEmptyString()
    {
        $string = '';
        $this->assertEquals('', utf8_ucfirst($string));
    }

    public function testOneChar()
    {
        $string = 'ñ';
        $this->assertEquals('Ñ', utf8_ucfirst($string));
    }

    public function testLinefeed()
    {
        $string = "ñtërn\nâtiônàlizætiøn";
        $this->assertEquals("Ñtërn\nâtiônàlizætiøn", utf8_ucfirst($string));
    }
}

class Utf8IsValidTest extends PHPUnit_Framework_TestCase
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

class Utf8BadReplaceTest extends PHPUnit_Framework_TestCase
{
    public function testValidUtf8()
    {
        $string = 'Iñtërnâtiônàlizætiøn';
        $this->assertEquals($string, utf8_bad_replace($string));
    }

    public function testValidUtf8Ascii()
    {
        $string = 'testing';
        $this->assertEquals($string, utf8_bad_replace($string));
    }

    public function testInvalidUtf8()
    {
        $string = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEquals('Iñtërnâtiôn?àlizætiøn', utf8_bad_replace($string));
    }

    public function testInvalidUtf8WithX()
    {
        $string = "Iñtërnâtiôn\xe9àlizætiøn";
        $this->assertEquals('IñtërnâtiônXàlizætiøn', utf8_bad_replace($string, 'X'));
    }

    public function testInvalidUtf8Ascii()
    {
        $string = "this is an invalid char '\xe9' here";
        $this->assertEquals("this is an invalid char '?' here", utf8_bad_replace($string));
    }

    public function testInvalidUtf8Multiple()
    {
        $string = "\xe9Iñtërnâtiôn\xe9àlizætiøn\xe9";
        $this->assertEquals('?Iñtërnâtiôn?àlizætiøn?', utf8_bad_replace($string));
    }

    public function testValidTwoOctetId()
    {
        $string = "abc\xc3\xb1";
        $this->assertEquals($string, utf8_bad_replace($string));
    }

    public function testInvalidTwoOctetSequence()
    {
        $string = "Iñtërnâtiônàlizætiøn \xc3\x28 Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn ?( Iñtërnâtiônàlizætiøn";
        $this->assertEquals($replaced, utf8_bad_replace($string));
    }

    public function testInvalidIdBetweenTwoAndThree()
    {
        $string = "Iñtërnâtiônàlizætiøn\xa0\xa1Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn??Iñtërnâtiônàlizætiøn";
        $this->assertEquals($replaced, utf8_bad_replace($string));
    }

    public function testValidThreeOctetId()
    {
        $string = "Iñtërnâtiônàlizætiøn\xe2\x82\xa1Iñtërnâtiônàlizætiøn";
        $this->assertEquals($string, utf8_bad_replace($string));
    }

    public function testInvalidThreeOctetSequenceSecond()
    {
        $string = "Iñtërnâtiônàlizætiøn\xe2\x28\xa1Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn?(?Iñtërnâtiônàlizætiøn";
        $this->assertEquals($replaced, utf8_bad_replace($string));
    }

    public function testInvalidThreeOctetSequenceThird()
    {
        $string = "Iñtërnâtiônàlizætiøn\xe2\x82\x28Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn??(Iñtërnâtiônàlizætiøn";
        $this->assertEquals($replaced, utf8_bad_replace($string));
    }

    public function testValidFourOctetId()
    {
        $string = "Iñtërnâtiônàlizætiøn\xf0\x90\x8c\xbcIñtërnâtiônàlizætiøn";
        $this->assertEquals($string, utf8_bad_replace($string));
    }

    public function testInvalidFourOctetSequence()
    {
        $string = "Iñtërnâtiônàlizætiøn\xf0\x28\x8c\xbcIñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn?(??Iñtërnâtiônàlizætiøn";
        $this->assertEquals($replaced, utf8_bad_replace($string));
    }

    public function testInvalidFiveOctetSequence()
    {
        $string = "Iñtërnâtiônàlizætiøn\xf8\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn?????Iñtërnâtiônàlizætiøn";
        $this->assertEquals($replaced, utf8_bad_replace($string));
    }

    public function testInvalidSixOctetSequence()
    {
        $string = "Iñtërnâtiônàlizætiøn\xfc\xa1\xa1\xa1\xa1\xa1Iñtërnâtiônàlizætiøn";
        $replaced= "Iñtërnâtiônàlizætiøn??????Iñtërnâtiônàlizætiøn";
        $this->assertEquals($replaced, utf8_bad_replace($string));
    }
}

?>
