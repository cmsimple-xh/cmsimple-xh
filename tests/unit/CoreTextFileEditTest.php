<?php

/**
 * Testing the CoreTextFileEdit classe.
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

/**
 * A test case for the CoreTextFileEdit class.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class CoreTextFileEditTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $pth, $file;

        $file = 'template';
        $pth['file']['template'] = '../../templates/cmsimplexh/template.htm';
        $this->editor = new XH_CoreTextFileEdit();
    }

    public function testAsString()
    {
        global $pth;

        $expected = file_get_contents($pth['file']['template']);
        $actual = $this->editor->asString();
        $this->assertEquals($expected, $actual);
    }
}

?>
