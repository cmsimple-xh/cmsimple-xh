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

class Utf8StrtoupperTest extends TestCase
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
