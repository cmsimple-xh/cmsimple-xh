<?php

/**
 * Testing the powered-by-link functionality.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2015-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit_Extensions_MockFunction;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the powered-by-link functionality.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.7
 */
class PoweredByTest extends TestCase
{
    protected $templatesMock;

    protected $pluginsMock;

    protected $uencMock;

    public function setUp()
    {
        global $sn, $pth;

        $this->setConstant('CMSIMPLE_XH_VERSION', 'CMSimple_XH 1.7');
        $this->setUpVirtualFileSystem();
        $this->setUpMocks();
        $sn = '/xh/';
        $pth['folder'] = array(
            'plugins' => vfsStream::url('root/plugins/'),
            'templates' => vfsStream::url('root/templates')
        );
    }

    protected function setUpVirtualFileSystem()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));
        mkdir(vfsStream::url('root/plugins/memberpages'), 0755, true);
        file_put_contents(
            vfsStream::url('root/plugins/memberpages/version.nfo'),
            'Memberpages,3.5,3.5,,,http://svasti.de/?Start:Memberpages,'
            . 'http://svasti.de/downloads/versioninfo/memberpages_version.nfo'
        );
        mkdir(vfsStream::url('root/templates/mini1'), 0755, true);
        file_put_contents(
            vfsStream::url('root/templates/mini1/template.nfo'),
            'test<br>info'
        );
    }

    protected function setUpMocks()
    {
        $this->templatesMock = new PHPUnit_Extensions_MockFunction(
            'XH_templates', null
        );
        $this->templatesMock->expects($this->any())->willReturn(array('mini1'));
        $this->pluginsMock = new PHPUnit_Extensions_MockFunction(
            'XH_plugins', null
        );
        $this->pluginsMock->expects($this->any())->willReturn(array('memberpages'));
        $this->uencMock = new PHPUnit_Extensions_MockFunction('uenc', null);
        $this->uencMock->expects($this->any())->willReturn('site-info');
    }

    public function tearDown()
    {
        $this->templatesMock->restore();
        $this->pluginsMock->restore();
        $this->uencMock->restore();
    }

    public function testInternalPluginUrlIsFalse()
    {
        $this->assertFalse(XH_pluginURL('meta_tags'));
    }

    public function testMemberpagesURL()
    {
        $this->assertEquals(
            'http://svasti.de/?Start:Memberpages',
            XH_pluginURL('memberpages')
        );
    }

    public function testPluginUrlOnMissingVersionInfoIsFalse()
    {
        $this->assertFalse(XH_pluginURL('foo'));
    }

    public function testViewHasCMSSection()
    {
        @$this->assertTag(
            array(
                'tag' => 'a',
                'attributes' => array('href' => 'http://cmsimple-xh.org'),
                'content' => 'CMSimple_XH'
            ),
            XH_poweredBy()
        );
    }

    public function testViewShowsTemplateInfo()
    {
        $this->assertStringMatchesFormat(
            '%A<dt>Mini1</dt>%A',
            XH_poweredBy()
        );
    }

    public function testViewShowsPluginInfo()
    {
        $this->assertStringMatchesFormat(
            '%A<li><a href="http://svasti.de/?Start:Memberpages">Memberpages</a></li>%A',
            XH_poweredBy()
        );
    }

    public function testPoweredByLink()
    {
        $this->assertEquals(
            '<a href="/xh/?site-info">site-info</a>', poweredByLink('site-info')
        );
    }
}

?>
