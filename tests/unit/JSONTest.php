<?php

/**
 * Testing JSON encoding/decoding.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2012-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * The file under test.
 */
require_once '../../cmsimple/classes/JSON.php';

/**
 * A test case for XH_JSON.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class JSONTest extends PHPUnit_Framework_TestCase
{
    var $json;

    public function setUp()
    {
        $this->json = new XH_JSON();
    }

    public function dataForTestEncode()
    {
        $null = null;
        $true = true;
        $false = false;
        $int = -12345;
        $float = -123.456;
        $string = 'foo';
        $path = 'foo/bar/';
        $quote = 'foo"bar';
        $backslash = 'foo\\bar';
        $list = array($null, $true, $false, $int, $float, $string, $path, $quote, $backslash);
        $dict = array('null' => $null, 'true' => $true, 'false' => $false);

        return array(
            array($null),
            array($true),
            array($false),
            array($int),
            array($float),
            array($string),
            array($path),
            array($quote),
            array($backslash),
            array($list),
            array($dict),
            array(array($list, $dict, $list))
        );
    }

    /**
     * @dataProvider dataForTestEncode
     */
    public function testEncode($value)
    {
        $expected = json_encode($value, JSON_UNESCAPED_UNICODE);
        $actual = $this->json->encode($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testEncodeRessource()
    {
        $file = fopen('./JsonTest.php', 'r');
        $this->json->encode($file);
        fclose($f);
    }

    public function dataForTestDecode()
    {
        return array(
            array(file_get_contents('./data/example.json')),
            array("\"\xC3\xA4\xC3\xB6\xC3\xBC\""),
            array('"\u0061\u00E4\uFEFC"')
        );
    }

    /**
     * @dataProvider dataForTestDecode
     */
    public function testDecode($string)
    {
        $expected = json_decode($string, true);
        $actual = $this->json->decode($string);
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeSyntaxError()
    {
        $string = '{true}';
        $expected = json_decode($string, true);
        $actual = $this->json->decode($string);
        $this->assertEquals($expected, $actual);
        $expected = !!json_last_error();
        $actual = $this->json->lastError();
        $this->assertEquals($expected, $actual);
    }

    public function testEncodeAndDecode()
    {
        $string = file_get_contents('./data/example.json');
        $value = json_decode($string, true);
        $new = $this->json->decode($this->json->encode($value));
        $this->assertEquals($value, $new);
    }
}

?>
