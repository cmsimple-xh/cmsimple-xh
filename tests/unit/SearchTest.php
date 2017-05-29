<?php

/**
 * Testing the search functionality.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

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
class SearchTest extends TestCase
{
    public function setUp()
    {
        global $c, $cf, $s, $o, $hjs, $bjs, $e, $onload;

        $c = array(
            '<h1>Welcome to CMSimple_XH</h1>',
            'Some arbitrary content',
            'More about CMSimple.',
            '#CMSimple hide# CMSimple again',
            'Bill &amp; Ted',
            "se\xC3\xB1or"
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
            array('&', array(4)),
            array( // testing unicode equivalence
                "sen\xCC\x83or",
                array(5)
            )
        );
    }

    /**
     * @dataProvider dataForSearch
     * @requires extension intl
     */
    public function testSearch($searchString, $expected)
    {
        $search = new Search($searchString);
        $actual = $search->search();
        $this->assertEquals($expected, $actual);
    }
}
