<?php


/**
 * A test case to actually check the CSRF protection.
 *
 * If CMSimple_XH is not installed directly in the web root,
 * the environment variable CMSIMPLEDIR has to be set to the installation folder
 * (e.g. cmsimple_xh/).
 *
 * @version SVN: $Id$
 */
class CSRFAttackTest extends PHPUnit_Framework_TestCase
{
    protected $url;

    protected $curlHandle;

    protected $cookieFile;

    /**
     * Log in to back-end and store cookies in a temp file.
     */
    public function setUp()
    {
        $this->url = 'http://localhost/' . getenv('CMSIMPLEDIR');
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'CC');

        $this->curlHandle = curl_init($this->url . '?&login=true&keycut=test');
        curl_setopt($this->curlHandle, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($this->curlHandle);
        curl_close($this->curlHandle);
    }

    protected function setCurlOptions($fields)
    {
        $options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile
        );
        curl_setopt_array($this->curlHandle, $options);
    }

    public function dataForAttack()
    {
        return array(
            array( // content editor
                array(
                    'selected' => 'Languages',
                    'function' => 'save',
                    'text' => '<h1>hacked</h1>'
                )
            )
        );
    }

    /**
     * @dataProvider dataForAttack
     */
    public function testAttack($fields)
    {
        $this->curlHandle = curl_init($this->url);
        $this->setCurlOptions($fields);
        curl_exec($this->curlHandle);
        $actual = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        curl_close($this->curlHandle);
        $this->assertEquals(403, $actual);
    }
}
