<?php

/**
 * Testing the UTF-8 related string functions.
 *
 * @category  CMSimple_XH
 * @package   Testing
 * @author    Harry Fuecks <hfuecks@gmail.com>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2006-2007 Harry Fuecks
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

class Utf8StrposTest extends TestCase
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
