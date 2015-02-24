<?php

/**
 * Testing the plugin menu that is integrated in the admin menu.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';

require_once './cmsimple/functions.php';
require_once './cmsimple/adminfuncs.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class IntegratedPluginMenuTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
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
        $subject = new XH_IntegratedPluginMenu();
        $registerPluginMenuItem = new PHPUnit_Extensions_MockFunction(
            'XH_registerPluginMenuItem', $subject
        );
        $registerPluginMenuItem->expects($this->exactly(5))->with('filebrowser');
        $subject->render(true);
    }
}

?>
