<?php

/**
 * Testing the plugin menu that is integrated in the admin menu.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class IntegratedPluginMenuTest extends TestCase
{
    protected function setUp()
    {
        global $pth, $plugin, $sl;

        $plugin = 'filebrowser';
        $sl = 'en';
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

    public function testRegisters5MenuItems()
    {
        $subject = new IntegratedPluginMenu();
        $registerPluginMenuItem = $this->createFunctionMock('XH_registerPluginMenuItem');
        $registerPluginMenuItem->expects($this->exactly(5))->with('filebrowser');
        $subject->render(true);
        $registerPluginMenuItem->restore();
    }
}
