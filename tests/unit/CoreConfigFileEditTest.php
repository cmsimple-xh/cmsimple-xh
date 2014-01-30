<?php

/**
 * Testing the CoreConfigFileEdit class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './cmsimple/functions.php';
require_once './plugins/utf8/utf8.php';
require_once UTF8 . '/ucfirst.php';

/**
 * The file under test.
 */
require_once './cmsimple/classes/FileEdit.php';

const XH_FORM_NAMESPACE = '';


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
        $pth['folder']['cmsimple'] = './cmsimple/';
        $pth['folder']['language'] = $pth['folder']['cmsimple'] . 'languages/';
        $pth['folder']['templates'] = './templates/';
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
                'meta', 'robots',
                array('val' => 'index, follow', 'type' => 'string', 'vals' => null),
                array(
                    'tag' => 'input',
                    'attributes' => array(
                        'type' => 'text',
                        'name' => 'meta_robots',
                        'value' => 'index, follow',
                        'class' => 'xh_setting'
                    )
                )
            ),
            array(
                'meta', 'robots',
                array('val' => 'index, follow', 'type' => 'text', 'vals' => null),
                array(
                    'tag' => 'textarea',
                    'attributes' => array(
                        'name' => 'meta_robots',
                        'class' => 'xh_setting'
                    ),
                    'content' => 'index, follow'
                )
            ),
            array(
                'meta', 'robots',
                array('val' => 'index, follow', 'type' => 'hidden', 'vals' => null),
                array(
                    'tag' => 'input',
                    'attributes' => array(
                        'type' => 'hidden',
                        'name' => 'meta_robots',
                        'value' => 'index, follow'
                    )
                )
            ),
            array(
                'locator', 'show_homepage',
                array('val' => 'true', 'type' => 'bool', 'vals' => null),
                array(
                    'tag' => 'input',
                    'attributes' => array(
                        'type' => 'checkbox',
                        'name' => 'locator_show_homepage',
                        'checked' => 'checked'
                    )
                )
            ),
            array(
                'language', 'default',
                array('val' => 'en', 'type' => 'enum', 'vals' => array('de', 'en')),
                array(
                    'tag' => 'select',
                    'attributes' => array('name' => 'language_default'),
                    'children' => array('count' => 2)
                )
            )
        );
    }

    /**
     * @dataProvider dataForFormField
     */
    public function testFormField($category, $name, $opt, $matcher)
    {
        $actual = $this->editor->formField($category, $name, $opt);
        $this->assertTag($matcher, $actual);
    }
}

?>
