<?php

/**
 * Testing the CSRF protection.
 *
 * If CMSimple_XH is not installed directly in the web root,
 * the environment variable CMSIMPLEDIR has to be set to the installation folder
 * (e.g. cmsimple_xh/).
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * A test case to actually check the CSRF protection.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
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
        $this->url = 'http://localhost' . getenv('CMSIMPLEDIR');
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
            CURLOPT_COOKIEFILE => $this->cookieFile,
            //CURLOPT_COOKIEJAR => $this->cookieFile
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
            ),
            array( // core configuration
                array(
                    'form' => 'array',
                    'file' => 'config',
                    'action' => 'save'
                )
            ),
            array( // core language configuration
                array(
                    'form' => 'array',
                    'file' => 'language',
                    'action' => 'save'
                )
            ),
            array( // pagemanager configuration
                array(
                      'admin' => 'plugin_config',
                      'action' => 'plugin_save'
                ),
                '&pagemanager'
            ),
            array( // pagemanager lanugage configuration
                array(
                    'admin' => 'plugin_language',
                    'action' => 'plugin_save'
                ),
                '&pagemanager'
            ),
            array( // content
                array(
                    'file' => 'content',
                    'action' => 'save'
                )
            ),
            array( // template
                array(
                    'file' => 'template',
                    'action' => 'save'
                )
            ),
            array( // stylesheet
                array(
                    'file' => 'stylesheet',
                    'action' => 'save'
                )
            ),
            array( // pagemanager stylesheet
                array(
                    'admin' => 'plugin_stylesheet',
                    'action' => 'plugin_textsave'
                ),
                '&pagemanager'
            ),
            array( // content deletion
                array(
                    'file' => 'content',
                    'action' => 'delete'
                )
            ),
            array( // backup restoral
                array(
                    'file' => '20130618_192318_content.htm',
                    'action' => 'restore'
                )
            )
        );
    }

    /**
     * @dataProvider dataForAttack
     */
    public function testAttack($fields, $queryString = null)
    {
        $url = $this->url . (isset($queryString) ? '?' . $queryString : '');
        $this->curlHandle = curl_init($url);
        $this->setCurlOptions($fields);
        curl_exec($this->curlHandle);
        $actual = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        curl_close($this->curlHandle);
        $this->assertEquals(403, $actual);
    }
}

?>
