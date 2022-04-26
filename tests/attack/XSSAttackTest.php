<?php

/**
 * Testing XSS attacks.
 *
 * The environment variable CMSIMPLEDIR has to be set to the installation folder
 * (e.g. / or /cmsimple_xh/).
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

/**
 * A test case to check XSS attack prevention.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class XSSAttackTest extends PHPUnit_Framework_TestCase
{
    public function testPrintlink()
    {
        $script = '<script>alert("XSS")</script>';
        $url = 'http://localhost' . getenv('CMSIMPLEDIR') . '?">' . $script;
        $curlHandle = curl_init($url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $doc = curl_exec($curlHandle);
        $condition = strpos($doc, $script);
        curl_close($curlHandle);
        $this->assertFalse($condition);
    }
}
