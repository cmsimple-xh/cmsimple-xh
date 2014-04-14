<?php

/**
 * Testing the toc() function.
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

require_once './cmsimple/functions.php';
require_once './cmsimple/tplfuncs.php';

/**
 * A stub for hide().
 *
 * @param int $pageIndex A page index.
 *
 * @return bool
 */
function hideStubForToc($pageIndex)
{
    return $pageIndex == 2;
}

/**
 * Returns the $pageIndexes argument.
 *
 * @param array $pageIndexes An array of page indexes.
 * @param mixed $forOrFrom   A li() view kind or the start level.
 *
 * @return array
 */
function Testing_li($pageIndexes, $forOrFrom)
{
    return $pageIndexes;
}

/**
 * Test case for the toc() function.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class TocTest extends PHPUnit_Framework_TestCase
{
    /**
     * Sets up the default fixture.
     *
     * @return void
     *
     * @global int The index of the selected page.
     */
    public function setUp()
    {
        global $s;

        $s = 1;
        $this->_setUpPageStructure();
        $this->_setUpConfiguration();
        $this->_setUpFunctionStubs();
    }

    /**
     * Sets up the default page structure.
     *
     * @return void
     *
     * @global int   The number of pages.
     * @global array The levels of the pages.
     */
    private function _setUpPageStructure()
    {
        global $cl, $l;

        $l = array(1, 1, 2, 3, 2, 3, 2, 3, 1, 3, 1);
        $cl = count($l);
    }

    /**
     * Sets up the default configuration options.
     *
     * @return void
     *
     * @global array The configuration of the core.
     */
    private function _setUpConfiguration()
    {
        global $cf;

        $cf = array(
            'menu' => array(
                'levelcatch' => '10',
                'levels' => '3'
            ),
            'show_hidden' => array(
                'pages_toc' => 'true'
            )
        );
    }

    /**
     * Sets up the default function stubs.
     *
     * @return void
     */
    private function _setUpFunctionStubs()
    {
        runkit_function_rename('hide', 'hide_orig');
        runkit_function_rename('hideStubForToc', 'hide');
    }

    /**
     * Tears down the default fixture.
     *
     * @return void
     */
    public function tearDown()
    {
        runkit_function_rename('hide', 'hideStubForToc');
        runkit_function_rename('hide_orig', 'hide');
    }

    /**
     * Tests that the default fixture is handled correctly.
     *
     * @param int   $start    A start menu level.
     * @param int   $end      An end menu level.
     * @param array $expected An expected result.
     *
     * @return void
     *
     * @dataProvider dataForToc
     */
    public function testToc($start, $end, $expected)
    {
        $this->assertEquals($expected, toc($start, $end, 'Testing_li'));
    }

    /**
     * Provides data for testToc().
     *
     * @return array
     */
    public function dataForToc()
    {
        $pages1 = array(0, 1, 8, 10);
        $pages2 = array(4, 6);
        $pages3 = array(0, 1, 4, 6, 8, 10);
        return array(
            array(null, null, $pages3),
            array(1, null, $pages1),
            array(1, 1, $pages1),
            array(1, 3, $pages3),
            array(2, 2, $pages2),
            array(2, 3, $pages2),
            array(3, 3, array())
        );
    }

    /**
     * Tests that two menu levels have the expected result.
     *
     * @return void
     *
     * @global array The levels of the pages.
     * @global int   The number of pages.
     * @global array The configuration of the core.
     */
    public function testTwoMenuLevels()
    {
        global $l, $cl, $cf;

        $l = array(1, 1, 2, 2, 2, 1, 1);
        $cl = count($l);
        $cf['menu']['levels'] = 2;
        $this->assertEquals(array(0, 1, 3, 4, 5, 6), $this->_toc());
    }

    /**
     * Tests that show_hidden->pages_toc has the expected result.
     *
     * @return void
     *
     * @global int   The index of the selected page.
     * @global array The configuration of the core.
     */
    public function testShowHiddenPagesShowsHiddenPage()
    {
        global $s, $cf;

        $s = 2;
        $cf['show_hidden']['pages_toc'] = 'true';
        $this->assertEquals(array(0, 1, 2, 3, 4, 6, 8, 10), $this->_toc());
    }

    /**
     * Tests that menu_levelcatch doesn't have a far subpage.
     *
     * @return void
     *
     * @global int   The index of the selected page.
     * @global array The configuration of the core.
     */
    public function testLevelcatchDoesntFarSubpage()
    {
        global $s, $cf;

        $s = 8;
        $cf['menu']['levelcatch'] = '0';
        $this->assertEquals(array(0, 1, 8, 10), $this->_toc());
    }

    /**
     * Tests that no selected page has only toplevel pages.
     *
     * @return void
     *
     * @global int The index of the selected page.
     */
    public function testNoPageSelectedHasToplevelsOnly()
    {
        global $s;

        $s = -1;
        $this->assertEquals(array(0, 1, 8, 10), $this->_toc());
    }

    /**
     * Returns the result of calling the default toc().
     *
     * @return array
     */
    private function _toc()
    {
        return toc(null, null, 'Testing_li');
    }
}

?>
