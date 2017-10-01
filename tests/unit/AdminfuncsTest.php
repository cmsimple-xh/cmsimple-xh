<?php

/**
 * Testing the functions in adminfuncs.php.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

/**
 * A test case for the functions in adminfuncs.php.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class AdminfuncsTest extends TestCase
{
    protected function setUp()
    {
        $this->setConstant('XH_ADM', true);
    }

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
     * @dataProvider wantsPluginAdministrationData
     */
    public function testWantsPluginAdministration($expected, $query, $pluginName)
    {
        $_SERVER['QUERY_STRING'] = $query;
        $this->assertSame($expected, XH_wantsPluginAdministration($pluginName));
        unset($_SERVER['QUERY_STRING']);
    }

    public function wantsPluginAdministrationData()
    {
        return array(
            [true, '&pagemanager&normal', 'pagemanager'],
            [false, 'Languages&foldergallery_folder=flags', 'foldergallery']
        );
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
