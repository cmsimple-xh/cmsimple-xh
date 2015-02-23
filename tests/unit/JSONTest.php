<?php

/**
 * Testing JSON encoding/decoding.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2012-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

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
    private $_json;

    public function setUp()
    {
        $this->_json = new XH_JSON();
    }

    /**
     * @dataProvider dataForTestEncode
     */
    public function testEncode($value)
    {
        $expected = json_encode($value, JSON_UNESCAPED_UNICODE);
        $actual = $this->_json->encode($value);
        $this->assertEquals($expected, $actual);
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
            array((object) $dict),
            array(array($list, $dict, $list))
        );
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testEncodeResourceTriggersWarning()
    {
        $file = fopen(__FILE__, 'r');
        $this->_json->encode($file);
        fclose($file);
    }

    public function testEncodeResourceReturnsNull()
    {
        $errorReporting = error_reporting(0);
        $stream = fopen(__FILE__, 'r');
        $this->_json->encode($stream);
        fclose($stream);
        error_reporting($errorReporting);
    }

    /**
     * Tests encoding of control characters.
     *
     * @link <http://cmsimpleforum.com/viewtopic.php?f=10&t=8236>
     */
    public function testEncodeControlCharacters()
    {
        $this->assertEquals('"\u0009\u000A"', $this->_json->encode("\t\n"));
    }

    /**
     * @dataProvider dataForTestDecode
     */
    public function testDecode($string)
    {
        $expected = json_decode($string);
        $actual = $this->_json->decode($string);
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestDecode()
    {
        return array(
            array(file_get_contents('./tests/unit/data/example.json')),
            array("\"\xC3\xA4\xC3\xB6\xC3\xBC\""),
            array('"\u0061\u00E4\uFEFC"'),
            array('"\ufeff"'),
            array('"\uD834\uDD1E"'),
            array('"\uDD1E"'),
            array('t'),
            array('f'),
            array('n'),
            array('null'),
            array('bar'),
            array('foo bar')
        );
    }

    public function testDecodeSyntaxError()
    {
        $string = '{true}';
        $expected = json_decode($string);
        $actual = $this->_json->decode($string);
        $this->assertEquals($expected, $actual);
        $expected = !!json_last_error();
        $actual = $this->_json->lastError();
        $this->assertEquals($expected, $actual);
    }

    public function testEncodeAndDecode()
    {
        $string = file_get_contents('./tests/unit/data/example.json');
        $value = json_decode($string);
        $new = $this->_json->decode($this->_json->encode($value));
        $this->assertEquals($value, $new);
    }
}

?>
