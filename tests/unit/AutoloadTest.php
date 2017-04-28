<?php

/**
 * Testing XH_autoload().
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

require_once './cmsimple/functions.php';

use PHPUnit_Framework_TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * A test case for XH_autoload().
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.7
 */
class AutoloadTest extends PHPUnit_Framework_TestCase
{
    const CLASS_XH_FOO = <<<'EOT'
<?php
namespace XH;
class Foo {}
EOT;

    const CLASS_XH_FOO_BAR = <<<'EOT'
<?php
namespace XH\Foo;
class Bar {}
EOT;

    const CLASS_FOO_PLUGIN = <<<'EOT'
<?php
namespace Foo;
class Plugin {}
EOT;

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    protected function setUp()
    {
        global $pth;

        $this->root = vfsStream::setup('root/');
        mkdir(vfsStream::url('root/cmsimple/classes/foo'), 0777, true);
        file_put_contents(vfsStream::url('root/cmsimple/classes/Foo.php'), self::CLASS_XH_FOO);
        file_put_contents(vfsStream::url('root/cmsimple/classes/foo/Bar.php'), self::CLASS_XH_FOO_BAR);
        mkdir(vfsStream::url('root/plugins/foo/classes'), 0777, true);
        file_put_contents(vfsStream::url('root/plugins/foo/classes/Plugin.php'), self::CLASS_FOO_PLUGIN);
        $pth = array(
            'folder' => array(
                'classes' => vfsStream::url('root/cmsimple/classes/'),
                'plugins' => vfsStream::url('root/plugins/')
            )
        );
        spl_autoload_register('XH_autoload');
    }

    public function testCoreClass()
    {
        $this->assertInstanceOf('XH\\Foo', new \XH\Foo);
    }

    public function testCoreClassInSubnamespace()
    {
        $this->assertInstanceOf('XH\\Foo\\Bar', new \XH\Foo\Bar);
    }

    public function testPluginClass()
    {
        $this->assertInstanceOf('Foo\\Plugin', new \Foo\Plugin);
    }

    public function testClassAlias()
    {
        $this->assertTrue(class_exists('XH_Foo'));
    }

    public function testNonExistentClass()
    {
        $this->assertFalse(class_exists('XH\\Bar'));
    }
}
