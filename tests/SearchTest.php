<?php

/**
 * @version $Id$
 */

require_once '../plugins/utf8/utf8.php';
require_once '../cmsimple/classes/Search.php';

class SearchTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $c, $cf;

        $c = array(
            '<h1>Welcome to CMSimple_XH</h1>',
            'Some arbitrary content',
            'More about CMSimple.',
            '#CMSimple hide# CMSimple again'
        );
        $cf['show_hidden']['pages_search'] == 'true';
    }

    public function dataForSearch()
    {
        return array(
            array('cmsimple', array(0, 2)),
            array('wurstsuppe', array()),
            array('cmsimple more', array(2))
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
