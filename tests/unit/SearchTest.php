<?php

/**
 * Testing the search functionality.
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
 * Helper functions.
 *
 * Might be stubbed.
 */
require_once './plugins/utf8/utf8.php';

/**
 * The file under test.
 */
require_once './cmsimple/classes/Search.php';

/**
 * A test case for the search functionality.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class SearchTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $c, $cf;

        $c = array(
            '<h1>Welcome to CMSimple_XH</h1>',
            'Some arbitrary content',
            'More about CMSimple.',
            '#CMSimple hide# CMSimple again',
            'Bill &amp; Ted'
        );
        $cf['show_hidden']['pages_search'] == 'true';
    }

    public function dataForSearch()
    {
        return array(
            array('cmsimple', array(0, 2)),
            array('wurstsuppe', array()),
            array('cmsimple more', array(2)),
            array(' ', array()),
            array('&', array(4))
        );
    }

    /**
     * @dataProvider dataForSearch
     */
    public function testSearch($searchString, $expected)
    {
        $search = new XH_Search($searchString);
        $actual = $search->search();
        $this->assertEquals($expected, $actual);
    }
}

?>
