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

class Utf8StrtolowerTest extends TestCase
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
