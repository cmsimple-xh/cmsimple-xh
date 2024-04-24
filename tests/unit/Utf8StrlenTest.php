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

class Utf8StrlenTest extends TestCase
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
