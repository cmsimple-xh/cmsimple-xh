<?php

/**
 * Testing the CoreConfigFileEdit class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * The file under test.
 */
require_once '../../cmsimple/classes/FileEdit.php';

define('XH_FORM_NAMESPACE', '');

function utf8_ucfirst($string)
{
    return $string;
}

/**
 * A test case to for the CoreConfigFileEdit classes.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class CoreConfigFileEditTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $pth, $sl, $file, $cf, $tx;

        $sl = 'en';
        $file = 'config';
        $pth['folder']['cmsimple'] = '../../cmsimple/';
        $pth['folder']['language'] = $pth['folder']['cmsimple'] . 'languages/';
        $pth['folder']['templates'] = '../../templates/';
        $pth['file']['config'] = $pth['folder']['cmsimple'] . 'config.php';
        $pth['file']['language'] = $pth['folder']['language'] . $sl . '.php';
        include $pth['file']['config'];
        include $pth['file']['language'];
        $this->editor = new XH_CoreConfigFileEdit();
    }

    public function testAsString()
    {
        global $pth;

        $expected = file_get_contents($pth['file']['config']);
        $actual = $this->editor->asString();
        $this->assertEquals($expected, $actual);
    }

    public function dataForFormField()
    {
        return array(
            array(
                'meta', 'robots', array('val' => 'index, follow', 'type' => 'string', 'vals' => null),
                '<input type="text" name="meta_robots" value="index, follow" class="cmsimplecore_settings">'
            ),
            array(
                'meta', 'robots', array('val' => 'index, follow', 'type' => 'text', 'vals' => null),
                '<textarea name="meta_robots" rows="3" cols="30" class="cmsimplecore_settings cmsimplecore_settings_short">index, follow</textarea>'
            ),
            array(
                'meta', 'robots', array('val' => 'index, follow', 'type' => 'hidden', 'vals' => null),
                '<input type="hidden" name="meta_robots" value="index, follow">'
            ),
            array(
                'locator', 'show_homepage', array('val' => 'true', 'type' => 'bool', 'vals' => null),
                '<input type="checkbox" name="locator_show_homepage" checked="checked">'
            ),
            array(
                'language', 'default', array('val' => 'en', 'type' => 'enum', 'vals' => array('de', 'en')),
                '<select name="language_default"><option>de</option><option selected="selected">en</option></select>'
            )
        );
    }

    /**
     * @dataProvider dataForFormField
     */
    public function testFormField($category, $name, $opt, $expected)
    {
        $actual = $this->editor->formField($category, $name, $opt);
        $this->assertEquals($expected, $actual);
    }
}

?>
