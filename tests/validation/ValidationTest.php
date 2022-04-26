<?php

/**
 * Testing valid HTML.
 *
 * We're using the W3C markup validation service (http://validator.w3.org/),
 * so don't run these test unnecessarily to avoid wasting its resources.
 *
 * The environment variable CMSIMPLEDIR has to be set to the installation folder
 * (e.g. / or /cmsimple_xh/).
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

/**
 * A test case to check the validity of the produced HTML.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class ValidationTest extends TestCase
{
    public function validate($html)
    {
        $url = 'http://validator.w3.org/check';
        $curlHandle = curl_init($url);
        curl_setopt_array($curlHandle, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                'fragment' => $html,
                'prefill' => '0',
                'doctype' => 'inline',
                'prefill_doctype' => 'html401',
                'group' => '0'
            ),
            CURLOPT_USERAGENT => 'CMSimple_XH (<http://www.cmsimple-xh.org/>) test suite',
            CURLOPT_RETURNTRANSFER => true
        ));
        $actual = curl_exec($curlHandle);
        curl_close($curlHandle);
        $this->assertXPath('//h2[@class="valid"]', $actual);
    }

    public function testStartPage()
    {
        $url = 'http://localhost' . getenv('CMSIMPLEDIR');
        $curlHandle = curl_init($url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($curlHandle);
        curl_close($curlHandle);
        $this->validate($html);
    }

    public function testPrintview()
    {
        $url = 'http://localhost' . getenv('CMSIMPLEDIR') . '?&print';
        $curlHandle = curl_init($url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($curlHandle);
        curl_close($curlHandle);
        $this->validate($html);
    }
}
