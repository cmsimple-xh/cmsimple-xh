<?php

/**
 * Testing the admin menu.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2015-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

class AdminMenuTest extends TestCase
{
    private $plugins;

    private $ucfirstMock;

    protected function setUp()
    {
        global $edit;

        $edit = false;
        $this->setUpPageStructure();
        $this->plugins = array('plugin');
        $this->setUpLocalization();
        $this->ucfirstMock = $this->getFunctionMock('utf8_ucfirst');
        $this->ucfirstMock->expects($this->any())->will($this->returnArgument(0));
    }

    private function setUpPageStructure()
    {
        global $sn, $s, $su, $u;

        $sn = '/';
        $s = 0;
        $su = 'Welcome';
        $u = array('Welcome');
    }

    private function setUpLocalization()
    {
        global $tx, $plugin_tx;

        $tx = array(
            'editmenu' => array(
                'backups' => 'Backups',
                'change_password' => 'Password',
                'configuration' => 'Configuration',
                'downloads' => 'Downloads',
                'edit' => 'Edit mode',
                'files' => 'Files',
                'images' => 'Images',
                'language' => 'Language',
                'log' => 'Log',
                'logout' => 'logout',
                'media' => 'Media',
                'normal' => 'View mode',
                'pagedata' => 'Page data',
                'pagemanager' => 'Pages',
                'plugins' => 'Plugins',
                'settings' => 'Settings',
                'stylesheet' => 'Stylesheet',
                'sysinfo' => 'Info',
                'template' => 'Template',
                'validate' => 'Check links'
            )
        );
        $plugin_tx = array();
    }

    protected function tearDown()
    {
        $this->ucfirstMock->restore();
    }

    /**
     * @dataProvider itemData
     */
    public function testShowsItem($item, $url, $edit = false)
    {
        $GLOBALS['edit'] = $edit;
        $this->assertXPathContains(
            sprintf('//div[@id="xh_adminmenu"]//a[@href="%s"]', $url),
            $item,
            XH_adminMenu($this->plugins)
        );
    }

    public function itemData()
    {
        global $tx;

        return array(
            array($tx['editmenu']['normal'], '/?Welcome&normal', true),
            array($tx['editmenu']['edit'], '/?Welcome&edit'),
            array($tx['editmenu']['pagemanager'], '/?&normal&xhpages'),
            array($tx['editmenu']['files'], '/?&edit&userfiles'),
            array($tx['editmenu']['images'], '/?&edit&images'),
            array($tx['editmenu']['downloads'], '/?&edit&downloads'),
            array($tx['editmenu']['media'], '/?&edit&media'),
            array($tx['editmenu']['settings'], '/?&settings'),
            array($tx['editmenu']['configuration'], '/?file=config&action=array'),
            array($tx['editmenu']['lanugage'], '/?file=language&action=array'),
            array($tx['editmenu']['template'], '/?file=template&action=edit'),
            array($tx['editmenu']['stylesheet'], '/?file=stylesheet&action=edit'),
            array($tx['editmenu']['log'], '/?file=log&action=view'),
            array($tx['editmenu']['validate'], '/?&validate'),
            array($tx['editmenu']['backups'], '/?&xh_backups'),
            array($tx['editmenu']['pagedata'], '/?&xh_pagedata'),
            array($tx['editmenu']['sysinfo'], '/?&sysinfo'),
            array('Plugin', '/?plugin&normal'),
            array($tx['editmenu']['logout'], '/?&logout')
        );
    }

    /**
     * @dataProvider pluginData
     */
    public function testShowsPluginsInColumns($count, $style)
    {
        $this->plugins = range(1, $count);
        $this->assertXPathContains(
            sprintf('//ul[@style="%s"]//a', $style),
            '1',
            XH_adminMenu($this->plugins)
        );
    }

    public function pluginData()
    {
        return array(
            array(12, 'width:125px; margin-left: 0px'),
            array(13, 'width:250px; margin-left: 0px'),
            array(24, 'width:250px; margin-left: 0px'),
            array(25, 'width:375px; margin-left: -125px')
        );
    }

    public function testShowsAllPluginItems()
    {
        $this->plugins = range(1, 10);
        $this->assertXPathCount(
            '//ul[@style="width:125px; margin-left: 0px"]/li',
            10,
            XH_adminMenu($this->plugins)
        );
    }

    public function testShowsAllVisiblePluginItems()
    {
        global $cf;

        $cf = array('plugins' => array('hidden' => '1, 5, 10'));
        $this->plugins = range(1, 10);
        $this->assertXPathCount(
            '//ul[@style="width:125px; margin-left: 0px"]/li',
            7,
            XH_adminMenu($this->plugins)
        );
    }

    public function testRegisterPluginMenuItemReturnsRegisteredItems()
    {
        $fooItems = array(
            array(
                'label' => 'Config',
                'url' => '?&foo&admin=plugin_config&action=plugin_edit',
                'target' => null
            ),
            array(
                'label' => 'Stylesheet',
                'url' => '?&foo&admin=plugin_stylesheet&action=plugin_text',
                'target' => '_blank'
            )

        );
        $barItems = array(
            array(
                'label' => 'Language',
                'url' => '?&foo&admin=plugin_language&action=plugin_edit',
                'target' => '_blank'
            )
        );
        foreach ($fooItems as $item) {
            XH_registerPluginMenuItem('foo', $item['label'], $item['url'], $item['target']);
        }
        foreach ($barItems as $item) {
            XH_registerPluginMenuItem('bar', $item['label'], $item['url'], $item['target']);
        }
        $this->assertEquals($fooItems, XH_registerPluginMenuItem('foo'));
        $this->assertEquals($barItems, XH_registerPluginMenuItem('bar'));
        $this->assertEmpty(XH_registerPluginMenuItem('baz'));
    }

    public function testShowsRegisteredPluginMenuItem()
    {
        $this->plugins = array('foo');
        $this->assertXPathContains(
            '//a[@href="?&foo&admin=plugin_config&action=plugin_edit"]',
            'Config',
            XH_adminMenu($this->plugins)
        );
    }

    public function testEditModeLinksToStartPage()
    {
        global $s, $tx;

        $s = -1;
        $this->assertXPathContains(
            '//a[@href="/?Welcome&edit"]',
            $tx['editmenu']['edit'],
            XH_adminMenu($this->plugins)
        );
    }
}
