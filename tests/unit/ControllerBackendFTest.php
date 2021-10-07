<?php

/**
 * Testing the controller functionality.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the setting of backend $f.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerBackendFTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * Sets up the test fixture.
     */
    protected function setUp(): void
    {
        $this->subject = new Controller();
    }

    /**
     * Tests $f == 'validate'.
     *
     * @return void
     */
    public function testValidate()
    {
        global $validate, $f;

        $validate = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('validate', $f);
    }

    /**
     * Tests $f == 'do_validate'.
     *
     * @return void
     */
    public function testDoValidate()
    {
        global $xh_do_validate, $f;

        $xh_do_validate = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('do_validate', $f);
    }

    /**
     * Tests $f == 'settings'.
     *
     * @return void
     */
    public function testSettings()
    {
        global $settings, $f;

        $settings = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('settings', $f);
    }

    /**
     * Tests $f == 'xh_backups'.
     *
     * @return void
     */
    public function testBackups()
    {
        global $xh_backups, $f;

        $xh_backups = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('xh_backups', $f);
    }

    /**
     * Tests $f == 'xh_pagedata'.
     *
     * @return void
     */
    public function testPagedata()
    {
        global $xh_pagedata, $f;

        $xh_pagedata = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('xh_pagedata', $f);
    }

    /**
     * Tests $f == 'sysinfo'.
     *
     * @return void
     */
    public function testSysinfo()
    {
        global $sysinfo, $f;

        $sysinfo = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('sysinfo', $f);
    }

    /**
     * Tests $f == 'phpinfo'.
     *
     * @return void
     */
    public function testPhpinfo()
    {
        global $phpinfo, $f;

        $phpinfo = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('phpinfo', $f);
    }

    /**
     * Tests $f == 'file'.
     *
     * @return void
     */
    public function testFile()
    {
        global $file, $f;

        $file = 'config';
        $this->subject->setBackendF();
        $this->assertEquals('file', $f);
    }

    /**
     * Tests $f == 'userfiles'.
     *
     * @return void
     */
    public function testUserfiles()
    {
        global $userfiles, $f;

        $userfiles = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('userfiles', $f);
    }

    /**
     * Tests $f == 'images'.
     *
     * @return void
     */
    public function testImages()
    {
        global $images, $f;

        $images = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('images', $f);
    }

    /**
     * Tests $f == 'downloads'.
     *
     * @return void
     */
    public function testDownloads()
    {
        global $downloads, $f;

        $downloads = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('downloads', $f);
    }

    /**
     * Tests $f == 'save'.
     *
     * @return void
     */
    public function testSave()
    {
        global $function, $f;

        $function = 'save';
        $this->subject->setBackendF();
        $this->assertEquals('save', $f);
    }

    /**
     * Tests $f == 'save' when system info and saving are requested.
     *
     * @return void
     */
    public function testSysinfoAndSave()
    {
        global $function, $sysinfo, $f;

        $function = 'save';
        $sysinfo = 'true';
        $this->subject->setBackendF();
        $this->assertEquals('save', $f);
    }
}
