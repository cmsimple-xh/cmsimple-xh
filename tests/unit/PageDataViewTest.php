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
class PageDataViewTest extends TestCase
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
        $actual = $this->pageDataView->tab($title, $filename, null);
        $this->assertXPathContains(
            '//a[@id="xh_tab_Metatags_view" and @class="xh_inactive_tab"]/span',
            $title,
            $actual
        );
    }

    public function testTabs()
    {
        $actual = $this->pageDataView->tabs();
        $this->assertXPathCount(
            '//div[@id="xh_pdtabs"]/a',
            2,
            $actual
        );
    }

    public function testView()
    {
        $filename = 'Metatags_view.php';
        $actual = $this->pageDataView->view($filename);
        $this->assertXPath(
            '//div[@id="xh_view_Metatags_view" and @class="xh_inactive_view"]/div[@class="xh_view_status"]',
            $actual
        );
    }

    public function testViews()
    {
        $actual = $this->pageDataView->views();
        $this->assertXPathCount(
            '//div[@id="xh_pdviews"]/div',
            2,
            $actual
        );
    }
}
