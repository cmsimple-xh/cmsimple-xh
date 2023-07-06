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

class Utf8BadReplaceTest extends TestCase
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
