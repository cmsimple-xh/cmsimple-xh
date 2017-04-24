<?php

/**
 * Testing the classic plugin menu.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';

require_once './cmsimple/functions.php';
require_once './cmsimple/adminfuncs.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class ClassicPluginMenuTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
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
        $this->_setUpVFS();
        $_XH_pluginMenu = new XH\ClassicPluginMenu();
    }

    private function _setUpVFS()
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

    public function testPluginMenuInitsAdminAndAction()
    {
        $initvar = new PHPUnit_Extensions_MockFunction('initvar', $this);
        // We'd like to add ->withConsecutive() here, but that's not yet
        // supported by PHPUnit 4.0 which we're relying on for now.
        $initvar->expects($this->exactly(2));
        print_plugin_admin('off');
        $initvar->restore();
    }

    public function testFullPluginMenuHas1Row()
    {
        $matcher = array(
            'tag' => 'table',
            'attributes' => array(
                'class' => 'edit'
            ),
            'children' => array(
                'only' => array('tag' => 'tr'),
                'count' => 1
            )
        );
        $this->_assertPluginMenuMatches($matcher);
    }

    public function testFullPluginMenuHas5Columns()
    {
        $matcher = array(
            'tag' => 'tr',
            'children' => array(
                'only' => array('tag' => 'td'),
                'count' => 5
            ),
            'parent' => array('tag' => 'table')
        );
        $this->_assertPluginMenuMatches($matcher);
    }

    /**
     * @dataProvider dataForMenuItems
     */
    public function testFullPluginMenuHasMainItem($label, $href)
    {
        $matcher = array(
            'tag' => 'a',
            'content' => $label,
            'attributes' => array(
                'href' => $href
            ),
            'ancestor' => array('tag' => 'table')
        );
        $this->_assertPluginMenuMatches($matcher);
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
        $matcher = array(
            'tag' => 'a',
            'content' => $label,
            'ancestor' => array('tag' => 'table')
        );
        $this->_assertPluginMenuMatches($matcher);
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
        $matcher = array(
            'tag' => 'a',
            'content' => 'Main',
            'ancestor' => array('tag' => 'table')
        );
        @$this->assertNotTag($matcher, print_plugin_admin('off'));
    }

    private function _assertPluginMenuMatches($matcher)
    {
        @$this->assertTag($matcher, print_plugin_admin('on'));
    }

    public function testCustomMenuRow()
    {
        $matcher = array(
            'tag' => 'table',
            'attributes' => array('style' => 'color: red'),
            'child' => array('tag' => 'tr')
        );
        $this->_assertCustomMenuMatches($matcher);
    }

    public function testCustomMenuTab()
    {
        $matcher = array(
            'tag' => 'td',
            'attributes' => array('style' => 'color: turquoise'),
            'child' => array(
                'tag' => 'a',
                'attributes' => array(
                    'style' => 'color: lime',
                    'href' => 'http://cmsimple-xh.org',
                    'target' => '_blank'
                ),
                'content' => 'CMSimple_XH'
            )
        );
        $this->_assertCustomMenuMatches($matcher);
    }

    public function testCustomMenuData()
    {
        $matcher = array(
            'tag' => 'td',
            'attributes' => array('style' => 'color: dark'),
            'content' => 'Lorem ipsum'
        );
        $this->_assertCustomMenuMatches($matcher);
    }

    public function testCustomMenuDataWithoutStyle()
    {
        $matcher = array(
            'tag' => 'td',
            'attributes' => array(),
            'content' => 'without style'
        );
        $this->_assertCustomMenuMatches($matcher);
    }

    private function _assertCustomMenuMatches($matcher)
    {
        @$this->assertTag($matcher, $this->_renderCustomMenu());
    }

    private function _renderCustomMenu()
    {
        pluginMenu('ROW', '', '', '', array('row' => 'style="color: red"'));
        pluginMenu(
            'TAB', 'http://cmsimple-xh.org', 'target="_blank"', 'CMSimple_XH',
            array(
                'tab' => 'style="color: turquoise"',
                'link' => 'style="color: lime"'
            )
        );
        pluginMenu(
            'DATA', '', '', 'Lorem ipsum',
            array('data' => 'style="color: dark"')
        );
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

?>
