<?php

/**
 * Testing the admin menu.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';

require_once './plugins/utf8/utf8.php';
require_once UTF8 . '/ucfirst.php';

require_once './cmsimple/adminfuncs.php';

class AdminMenuTest extends PHPUnit_Framework_TestCase
{
    private $_plugins;

    private $_ucfirstMock;

    public function setUp()
    {
        global $edit;

        $edit = false;
        $this->_setUpPageStructure();
        $this->_plugins = array('plugin');
        $this->_setUpLocalization();
        $this->_ucfirstMock = new PHPUnit_Extensions_MockFunction(
            'utf8_ucfirst', $this
        );
        $this->_ucfirstMock->expects($this->any())->will($this->returnArgument(0));
    }

    private function _setUpPageStructure()
    {
        global $sn, $s, $su, $u;

        $sn = '/';
        $s = 0;
        $su = 'Welcome';
        $u = array('Welcome');
    }

    private function _setUpLocalization()
    {
        global $tx, $plugin_tx;

        $tx = array(
            'editmenu' => array(
                'backups' => 'Backups',
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

    public function tearDown()
    {
        $this->_ucfirstMock->restore();
    }

    /**
     * @dataProvider itemData
     */
    public function testShowsItem($item, $url, $edit = false)
    {
        $GLOBALS['edit'] = $edit;
        $matcher = array(
            'tag' => 'a',
            'attributes' => array('href' => $url),
            'content' => $item,
            'ancestor' => array(
                'tag' => 'div',
                'id' => 'xh_adminmenu'
            )
        );
        $this->_assertMatches($matcher);
    }

    public function itemData()
    {
        global $tx;

        return array(
            array($tx['editmenu']['normal'], '/?Welcome&normal', true),
            array($tx['editmenu']['edit'], '/?Welcome&edit'),
            array($tx['editmenu']['pagemanager'], '/?&normal&xhpages'),
            array($tx['editmenu']['files'], '/?&normal&userfiles'),
            array($tx['editmenu']['images'], '/?&normal&images'),
            array($tx['editmenu']['downloads'], '/?&normal&downloads'),
            array($tx['editmenu']['media'], '/?&normal&media'),
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

    public function testShowsPluginsItem()
    {
        global $tx;

        $matcher = array(
            'tag' => 'span',
            'content' => $tx['editmenu']['plugins'],
            'ancestor' => array(
                'tag' => 'div',
                'id' => 'xh_adminmenu'
            )
        );
        $this->_assertMatches($matcher);
    }

    /**
     * @dataProvider pluginData
     */
    public function testShowsPluginsInColumns($count, $style)
    {
        $this->_plugins = range(1, $count);
        $matcher = array(
            'tag' => 'a',
            'content' => '1',
            'ancestor' => array(
                'tag' => 'ul',
                'attributes' => array('style' => $style)
            )
        );
        $this->_assertMatches($matcher);
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
        $this->_plugins = range(1, 10);
        $matcher = array(
            'tag' => 'ul',
            'attributes' => array('style' => 'width:125px; margin-left: 0px'),
            'children' => array(
                'count' => 10,
                'only' => array('tag' => 'li')
            )
        );
        $this->_assertMatches($matcher);
    }

    public function testShowsAllVisiblePluginItems()
    {
        global $cf;

        $cf = array('plugins' => array('hidden' => '1, 5, 10'));
        $this->_plugins = range(1, 10);
        $matcher = array(
            'tag' => 'ul',
            'attributes' => array('style' => 'width:125px; margin-left: 0px'),
            'children' => array(
                'count' => 7,
                'only' => array('tag' => 'li')
            )
        );
        $this->_assertMatches($matcher);
    }

    public function testEditModeLinksToStartPage()
    {
        global $s, $tx;

        $s = -1;
        $matcher = array(
            'tag' => 'a',
            'content' => $tx['editmenu']['edit'],
            'attributes' => array('href' => '/?Welcome&edit')
        );
        $this->_assertMatches($matcher);
    }

    private function _assertMatches($matcher)
    {
        $this->assertTag($matcher, XH_adminMenu($this->_plugins));
    }
}


?>
