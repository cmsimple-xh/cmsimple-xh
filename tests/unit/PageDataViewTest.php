<?php

/**
 * Testing the page data views.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit_Framework_TestCase;

/**
 * A test case for the page data views.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PageDataViewTest extends PHPUnit_Framework_TestCase
{
    protected $pageDataView;

    public function setUp()
    {
        $tabs = array(
            'Meta' => array('Metatags_view.php', null),
            'Page' => array('Pageparams_view.php', null)
        );
        $this->pageDataView = new PageDataView(array(), $tabs);
    }

    public function testTab()
    {
        $title = 'Meta';
        $filename = 'Metatags_view.php';
        $matcher = array(
            'tag' => 'a',
            'attributes' => array(
                'id' => 'xh_tab_Metatags_view',
                'class' => 'xh_inactive_tab'
            ),
            'child' => array(
                'tag' => 'span',
                'content' => $title
            )
        );
        $actual = $this->pageDataView->tab($title, $filename, null);
        @$this->assertTag($matcher, $actual);
    }

    public function testTabs()
    {
        $matcher = array(
            'tag' => 'div',
            'attributes' => array(
                'id' => 'xh_pdtabs'
            ),
            'children' => array(
                'count' => 2,
                'only' => array('tag' => 'a')
            )
        );
        $actual = $this->pageDataView->tabs();
        @$this->assertTag($matcher, $actual);
    }

    public function testView()
    {
        $filename = 'Metatags_view.php';
        $matcher = array(
            'tag' => 'div',
            'attributes' => array(
                'id' => 'xh_view_Metatags_view',
                'class' => 'xh_inactive_view'
            ),
            'child' => array(
                'tag' => 'div',
                'class' => 'xh_view_status'
            )
        );
        $actual = $this->pageDataView->view($filename);
        @$this->assertTag($matcher, $actual);
    }

    public function testViews()
    {
        $matcher = array(
            'tag' => 'div',
            'attributes' => array(
                'id' => 'xh_pdviews'
            ),
            'children' => array(
                'count' => 2,
                'only' => array('tag' => 'div')
            )
        );
        $actual = $this->pageDataView->views();
        @$this->assertTag($matcher, $actual);
    }
}

?>
