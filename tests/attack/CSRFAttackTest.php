<?php

/**
 * Testing the CSRF protection.
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
 * A test case to actually check the CSRF protection.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
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
    protected function setUp()
    {
        $this->url = 'http://localhost' . getenv('CMSIMPLEDIR');
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'CC');

        $this->curlHandle = curl_init($this->url . '?&login=true&keycut=test');
        curl_setopt($this->curlHandle, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($this->curlHandle);
        curl_close($this->curlHandle);
    }

    /**
     * @todo Use CURLFile class and get rid of @ operator.
     */
    protected function setCurlOptions($fields)
    {
        if (defined('CURLOPT_SAFE_UPLOAD') && version_compare(PHP_VERSION, '7.0', '<')) {
            curl_setopt($this->curlHandle, CURLOPT_SAFE_UPLOAD, false);
        }
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
            ),
            array( // pagedata tab
                array(
                    'save_page_data' => ''
                ),
                'Start'
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
                    'action' => 'empty'
                )
            ),
            array( // backup restoral
                array(
                    'file' => '20130618_192318_content.htm',
                    'action' => 'restore'
                )
            ),
            array( // page data editing
                array(
                    'xh_pagedata_delete' => 'delete',
                    'description' => 'on'
                ),
                '&xh_pagedata'
            ),
            array( // change password
                array(
                    'xh_password_old' => 'test',
                    'xh_password_new' => 'foo',
                    'xh_password_confirmation' => 'foo',
                    'action' => 'save',
                ),
                '&xh_change_password'
            ),
            array( // filebrowser: create folder
                array(
                    'createFolder' => 'test'
                ),
                '&media'
            ),
            array( // filebrowser: upload file
                array(
                    'fbupload' => version_compare(PHP_VERSION, '7.0', '>=')
                        ? new CURLFile(realpath('./tests/attack/data/hack.txt'))
                        : '@' . realpath('./tests/attack/data/hack.txt'),
                    'upload' => 'upload'
                ),
                '&downloads'
            ),
            array( // filebrowser: rename file
                array(
                    'oldName' => 'XHdebug.txt',
                    'renameFile' => 'XHdebug1.txt'
                ),
                '&downloads'
            ),
            array( // filebrowser: delete folder
                array(
                    'deleteFolder' => '',
                    'folder' => 'userfiles/images/flags'
                ),
                '&images'
            ),
            array( // filebrowser: delete file
                array(
                    'deleteFile' => '',
                    'filebrowser_file' => 'XHdebug.txt'
                ),
                '&downloads'
            ),
            array( // editorbrowser: create folder
                array('createFolder' => 'test'),
                '&filebrowser=editorbrowser&editor=tinymce&prefix=./&base=./&type=image'
            ),
            array( // editorbrowser: upload file
                array(
                    'fbupload' => version_compare(PHP_VERSION, '7.0', '>=')
                        ? new CURLFile(realpath('./tests/attack/data/hack.txt'))
                        : '@' . realpath('./tests/attack/data/hack.txt'),
                    'upload' => 'upload'
                ),
                '&filebrowser=editorbrowser&editor=tinymce&prefix=./&base=./&type=image'
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
