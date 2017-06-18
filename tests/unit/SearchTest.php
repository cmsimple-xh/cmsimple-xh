<?php

/**
 * Testing the search functionality.
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

require_once './cmsimple/functions.php';
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
        global $c, $cf, $pd_router;

        $c = array(
            '<h1>Welcome to CMSimple_XH</h1>',
            'Some arbitrary content',
            'More about CMSimple.',
            '#CMSimple hide# CMSimple again',
            'Bill &amp; Ted',
            "se\xC3\xB1or",
            '<h1>foo</h1>'
        );
        $h = array('a', 'b', 'c', 'd', 'e', 'f');
        $fields = array('show_heading', 'heading');
        $temp = array('show_heading' => '', 'heading' => '');
        $data = array(
            array('show_heading' => '', 'heading' => ''),
            array('show_heading' => '', 'heading' => ''),
            array('show_heading' => '', 'heading' => ''),
            array('show_heading' => '', 'heading' => ''),
            array('show_heading' => '', 'heading' => ''),
            array('show_heading' => '', 'heading' => ''),
            array('show_heading' => '1', 'heading' => 'bar')
        );
        $pd_router = new XH_PageDataRouter($h, $fields, $temp, $data);
        $cf['show_hidden']['pages_search'] == 'true';
        runkit_function_add(
            'Pageparams_replaceAlternativeHeading',
            function ($content, $pageData) {
                if ($pageData['show_heading']) {
                    $content = "<h1>{$pageData['heading']}</h1>";
                }
                return $content;
            }
        );
    }

    public function tearDown()
    {
        runkit_function_remove('Pageparams_replaceAlternativeHeading');
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
                method_exists('Normalizer', 'normalize') ? array(5) : array()
            ),
            array( // alternative heading
                'bar',
                array(6)
            )
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
