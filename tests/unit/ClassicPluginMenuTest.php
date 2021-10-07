<?php

/**
 * Testing the classic plugin menu.
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

class ClassicPluginMenuTest extends TestCase
{
    protected function setUp(): void
    {
        global $sn, $plugin, $sl, $tx, $_XH_pluginMenu;

        $sn = '/xh/';
        $plugin = 'filebrowser';
        $sl = 'en';
        $tx = array(
            'menu' => array(
                'tab_config' => 'Config',
                'tab_css' => 'Stylesheet',
                'tab_help' => 'Help',
                'tab_language' => 'Language',
                'tab_main' => 'Main',
            )
        );
        $this->setUpVFS();
        $_XH_pluginMenu = new ClassicPluginMenu();
    }

    private function setUpVFS()
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('plugins'));
        $pth['folder']['plugins'] = vfsStream::url('plugins/');
        mkdir($pth['folder']['plugins'] . 'filebrowser', 0777, true);
        mkdir($pth['folder']['plugins'] . 'filebrowser/config', 0777, true);
        touch($pth['folder']['plugins'] . 'filebrowser/config/config.php');
        mkdir($pth['folder']['plugins'] . 'filebrowser/css', 0777, true);
        touch($pth['folder']['plugins'] . 'filebrowser/css/stylesheet.css');
        mkdir($pth['folder']['plugins'] . 'filebrowser/help', 0777, true);
        touch($pth['folder']['plugins'] . 'filebrowser/help/help.htm');
        mkdir($pth['folder']['plugins'] . 'filebrowser/languages', 0777, true);
        touch($pth['folder']['plugins'] . 'filebrowser/languages/en.php');
    }

    public function testFullPluginMenuHas1Row()
    {
        $this->assertXPathCount(
            '//table[@class="edit"]/tr',
            1,
            print_plugin_admin('on')
        );
    }

    public function testFullPluginMenuHas5Columns()
    {
        $this->assertXPathCount(
            '//table/tr/td',
            5,
            print_plugin_admin('on')
        );
    }

    /**
     * @dataProvider dataForMenuItems
     */
    public function testFullPluginMenuHasMainItem($label, $href)
    {
        $this->assertXPathContains(
            sprintf('//table//a[@href="%s"]', $href),
            $label,
            print_plugin_admin('on')
        );
    }

    public function dataForMenuItems()
    {
        return array(
            array(
                'Main',
                '/xh/?&filebrowser&admin=plugin_main&action=plugin_text&normal'
            ),
            array(
                'Stylesheet',
                '/xh/?&filebrowser&admin=plugin_stylesheet&action=plugin_text&normal'
            ),
            array(
                'Config',
                '/xh/?&filebrowser&admin=plugin_config&action=plugin_edit&normal'
            ),
            array(
                'Language',
                '/xh/?&filebrowser&admin=plugin_language&action=plugin_edit&normal'
            )
        );
    }

    /**
     * @dataProvider dataForPluginTextMenuItems
     */
    public function testPluginTextOverwritesMenuItemLabels($label)
    {
        global $plugin_tx;

        $plugin_tx = array(
            'filebrowser' => array(
                'menu_main' => 'Plugin-Main',
                'menu_css' => 'Plugin-Stylesheet',
                'menu_config' => 'Plugin-Config',
                'menu_language' => 'Plugin-Language',
                'menu_help' => 'Plugin-Help',
            )
        );
        $this->assertXPathContains('//table//a', $label, print_plugin_admin('on'));
    }

    public function dataForPluginTextMenuItems()
    {
        return array(
            array('Plugin-Main'),
            array('Plugin-Stylesheet'),
            array('Plugin-Config'),
            array('Plugin-Language'),
            array('Plugin-Help')
        );
    }

    public function testOffShowsNoMainItem()
    {
        $this->assertNotXPathContains(
            '//table//a',
            'Main',
            print_plugin_admin('off')
        );
    }

    public function testCustomMenuRow()
    {
        $this->assertXPath(
            '//table[@style="color: red"]/tr',
            $this->renderCustomMenu()
        );
    }

    public function testCustomMenuTab()
    {
        $this->assertXPathContains(
            '//td[@style="color: turquoise"]/a[@style="color: lime"'
            . ' and @href="http://cmsimple-xh.org" and @target="_blank"]',
            'CMSimple_XH',
            $this->renderCustomMenu()
        );
    }

    public function testCustomMenuData()
    {
        $this->assertXPathContains(
            '//td[@style="color: dark"]',
            'Lorem ipsum',
            $this->renderCustomMenu()
        );
    }

    public function testCustomMenuDataWithoutStyle()
    {
        $this->assertXPathContains('//td', 'without style', $this->renderCustomMenu());
    }

    private function renderCustomMenu()
    {
        pluginMenu('ROW', '', '', '', array('row' => 'style="color: red"'));
        pluginMenu(
            'TAB',
            'http://cmsimple-xh.org',
            'target="_blank"',
            'CMSimple_XH',
            array(
                'tab' => 'style="color: turquoise"',
                'link' => 'style="color: lime"'
            )
        );
        pluginMenu('DATA', '', '', 'Lorem ipsum', ['data' => 'style="color: dark"']);
        pluginMenu('DATA', '', '', 'without style', array());
        return pluginMenu('SHOW');
    }

    public function testPluginFiles()
    {
        global $pth;

        $pluginFolder = $pth['folder']['plugins'] . 'filebrowser/';
        $expected = array(
            'folder' => array(
                'plugin' => $pluginFolder,
                'plugin_classes' => $pluginFolder . 'classes/',
                'plugin_config' => $pluginFolder . 'config/',
                'plugin_content' => $pluginFolder . 'content/',
                'plugin_css' => $pluginFolder . 'css/',
                'plugin_help' => $pluginFolder . 'help/',
                'plugin_includes' => $pluginFolder . 'includes/',
                'plugin_languages' => $pluginFolder . 'languages/',
                'plugins' => $pth['folder']['plugins']
            ),
            'file' => array(
                'plugin_index' => $pluginFolder . 'index.php',
                'plugin_admin' => $pluginFolder . 'admin.php',
                'plugin_language' => $pluginFolder . 'languages/en.php',
                'plugin_classes' => $pluginFolder . 'classes/required_classes.php',
                'plugin_config' => $pluginFolder . 'config/config.php',
                'plugin_stylesheet' => $pluginFolder . 'css/stylesheet.css',
                'plugin_help' => $pluginFolder . 'help/help.htm'
            )
        );
        pluginFiles('filebrowser');
        $this->assertEquals($expected, $pth);
    }
}
