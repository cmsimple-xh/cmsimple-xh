<?php

/**
 * Testing the functions in adminfuncs.php.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/**
 * The file under test.
 */
require_once './cmsimple/adminfuncs.php';

if (!defined('XH_ADM')) {
    define('XH_ADM', true);
}

/**
 * A test case for the functions in adminfuncs.php.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class AdminfuncsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSaveContentsRequiresEditMode()
    {
        global $edit;

        $edit = false;
        XH_saveContents();
    }

    /**
     * Test XH_wantsPluginAdministration().
     *
     * @return void
     *
     * @global string Whether the pagemanager administration is requested.
     */
    public function testWantsPluginAdministration()
    {
        global $pagemanager;

        $pagemanager = 'true';
        $this->assertTrue(XH_wantsPluginAdministration('pagemanager'));
    }

    /**
     * Tests XH_wantsPluginAdministration().
     *
     * @return void
     */
    public function testDoesNotWantPluginAdministration()
    {
        $this->assertFalse(XH_wantsPluginAdministration('pagemanager'));
    }
}

?>
