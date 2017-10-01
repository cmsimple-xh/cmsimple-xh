<?php

/**
 * Testing the menu functionality.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

/**
 * Test case for the menu functionality.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class MenuTest extends TestCase
{
    private $aStub;

    private $hideStub;

    /**
     * Sets up the default fixture.
     *
     * @return void
     */
    protected function setUp()
    {
        global $pth, $s;

        $pth = array('folder' => array('classes' => './cmsimple/classes/'));
        $s = 0;
        $this->setUpPageStructure();
        $this->setUpConfiguration();
        $this->setUpEditMode(false);
        $this->setUpPageDataRouterMock();
        $this->setUpFunctionStubs();
    }

    /**
     * Sets up the default page structure.
     *
     * @return void
     */
    private function setUpPageStructure()
    {
        global $cl, $h, $u, $l;

        $h = array(
            'Welcome',
            'Blog',
            'July',
            'Hot',
            'Hidden',
            'AlsoHidden',
            'January',
            'Cold',
            'About',
            'Contact',
            'News'
        );
        $u = array(
            'Welcome',
            'Blog',
            'Blog:July',
            'Blog:July:Hot',
            'Blog:Hidden',
            'Blog:Hidden:AlsoHidden',
            'Blog:January',
            'Blog:January:Cold',
            'About',
            'About:Contact',
            'News'
        );
        $l = array(1, 1, 2, 3, 2, 3, 2, 3, 1, 3, 1);
        $cl = count($u);
    }

    /**
     * Sets up the default configuration options.
     *
     * @return void
     */
    private function setUpConfiguration()
    {
        global $cf;

        $cf = array(
            'menu' => array(
                'levelcatch' => '10',
                'levels' => '3',
                'sdoc' => 'parent'
            ),
            'show_hidden' => array(
                'pages_toc' => 'true'
            ),
            'uri' => array(
                'seperator' => ':'
            )
        );
    }

    /**
     * Sets up edit resp. normal mode.
     *
     * @param bool $flag Whether to enable edit mode.
     *
     * @return void
     */
    private function setUpEditMode($flag)
    {
        global $edit;

        $this->setConstant('XH_ADM', $flag);
        $edit = $flag;
    }

    /**
     * Sets up the default page data router mock.
     *
     * @return void
     */
    private function setUpPageDataRouterMock()
    {
        global $pd_router;

        $pd_router = $this->createMock(PageDataRouter::class);
        $pd_router
            ->method('find_page')
            ->will(
                $this->returnCallback(
                    function ($pageIndex) {
                        $useHeaderLocation = $pageIndex == 7 ? '2' : '0';
                        return array('use_header_location' => $useHeaderLocation);
                    }
                )
            );
    }

    /**
     * Sets up the default function stubs.
     *
     * @return void
     */
    private function setUpFunctionStubs()
    {
        $this->aStub = $this->createFunctionMock('a');
        $this->aStub->expects($this->any())->will(
            $this->returnCallback(
                function ($pageIndex, $suffix) {
                    global $u;

                    return '<a href="?' . $u[$pageIndex] . $suffix . '">';
                }
            )
        );
        $this->hideStub = $this->createFunctionMock('hide');
        $this->hideStub->expects($this->any())->will(
            $this->returnCallback(
                function ($pageIndex) {
                    return in_array($pageIndex, array(4, 5));
                }
            )
        );
    }

    protected function tearDown()
    {
        $this->aStub->restore();
        $this->hideStub->restore();
    }

    /**
     * Returns the $pageIndexes argument.
     *
     * @param array $pageIndexes An array of page indexes.
     *
     * @return array
     */
    public function li(array $pageIndexes)
    {
        return $pageIndexes;
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
    public function testToc($start, $end, array $expected)
    {
        global $s;

        $s = 1;
        $this->assertEquals($expected, toc($start, $end, array($this, 'li')));
    }

    /**
     * Provides data for testToc().
     *
     * @return array
     */
    public function dataForToc()
    {
        $pages1 = array(0, 1, 8, 10);
        $pages2 = array(2, 6);
        $pages3 = array(0, 1, 2, 6, 8, 10);
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
     */
    public function testTwoMenuLevelsToc()
    {
        global $l, $cl, $s, $cf;

        $s = 1;
        $l = array(1, 1, 2, 2, 2, 1, 1);
        $cl = count($l);
        $cf['menu']['levels'] = 2;
        $this->assertEquals(array(0, 1, 2, 3, 6), $this->toc());
    }

    /**
     * Tests that show_hidden->pages_toc has the expected result.
     *
     * @return void
     */
    public function testTocShowHiddenPagesShowsHiddenPage()
    {
        global $s, $cf;

        $s = 2;
        $cf['show_hidden']['pages_toc'] = 'true';
        $this->assertEquals(array(0, 1, 2, 3, 6, 8, 10), $this->toc());
    }

    /**
     * Tests that menu_levelcatch doesn't have a far subpage.
     *
     * @return void
     */
    public function testLevelcatchDoesntFarSubpage()
    {
        global $s, $cf;

        $s = 8;
        $cf['menu']['levelcatch'] = '0';
        $this->assertEquals(array(0, 1, 8, 10), $this->toc());
    }

    /**
     * Tests that no selected page has only toplevel pages.
     *
     * @return void
     */
    public function testNoPageSelectedHasToplevelsOnly()
    {
        global $s;

        $s = -1;
        $this->assertEquals(array(0, 1, 8, 10), $this->toc());
    }

    /**
     * Returns the result of calling the default toc().
     *
     * @return array
     */
    private function toc()
    {
        return toc(null, null, array($this, 'li'));
    }
    /**
     * Tests that no menu items display nothing.
     *
     * @return void
     */
    public function testNoMenuItemsDisplayNothing()
    {
        $this->assertEmpty(li(array(), 1));
    }

    /**
     * Tests that a UL has a LI as child.
     *
     * @param string $class A CSS class.
     *
     * @return void
     *
     * @dataProvider dataForUnorderedListlHasListItemChild
     */
    public function testUnorderedListHasListItemChild($class)
    {
        $this->assertXPath(
            sprintf('//ul[@class="%s"]/li', $class),
            $this->renderAllPages()
        );
    }

    /**
     * Provides data for dataForUnorderedListlHasListItemChild().
     *
     * @return array
     */
    public function dataForUnorderedListlHasListItemChild()
    {
        return array(
            array('menulevel1'),
            array('menulevel2'),
            array('menulevel3')
        );
    }

    /**
     * Tests that a LI has a UL child.
     *
     * @param string $class A CSS class.
     *
     * @return void
     *
     * @dataProvider dataForListItemHasUnorderedListChild
     */
    public function testListItemHasUnorderedListChild($class)
    {
        $this->assertXPath(
            sprintf('//li/ul[@class="%s"]', $class),
            $this->renderAllPages()
        );
    }

    /**
     * Provides data for testListItemHasUnorderedListChild().
     *
     * @return array
     */
    public function dataForListItemHasUnorderedListChild()
    {
        return array(
            array('menulevel2'),
            array('menulevel3')
        );
    }

    /**
     * Tests that the selected page is marked up as SPAN.
     *
     * @return void
     */
    public function testSelectedPageHasSpan()
    {
        $this->assertXPathContains('//span', 'Welcome', $this->renderAllPages());
    }

    /**
     * Tests that a not selected page is marked up as ANCHOR.
     *
     * @return void
     */
    public function testNotSelectedPageHasAnchor()
    {
        $this->assertXPathContains('//a', 'Blog', $this->renderAllPages());
    }

    /**
     * Tests that a LI without visible children has the class "doc".
     *
     * @return void
     */
    public function testLiWithoutVisibleChilrenHasClassDoc()
    {
        $this->assertXPathContains(
            '//li[@class="doc"]/a',
            'Hidden',
            $this->renderAllPages()
        );
    }

    /**
     * Tests that UL has the proper class attribute.
     *
     * @param mixed  $forOrFrom A li() view kind or the start level.
     * @param string $class     A CSS class.
     *
     * @return void
     *
     * @dataProvider dataForHasUlWithProperClass
     */
    public function testHasUlWithProperClass($forOrFrom, $class)
    {
        $this->assertXPath(
            sprintf('//ul[@class="%s"]', $class),
            $this->renderAllPages($forOrFrom)
        );
    }

    /**
     * Provides data for testHasUlWithProperClass().
     *
     * @return array
     */
    public function dataForHasUlWithProperClass()
    {
        return array(
            array('menulevel', 'menulevel1'),
            array(1, 'menulevel1'),
            array(1, 'menulevel2'),
            array(1, 'menulevel3'),
            array('sitemaplevel', 'sitemaplevel1'),
            array('sitemaplevel', 'sitemaplevel2'),
            array('sitemaplevel', 'sitemaplevel3'),
            array('submenu', 'submenu'),
            array('search', 'search')
        );
    }

    /**
     * Tests that a selected page with children has the class "docs".
     *
     * @return void
     */
    public function testSelectedPageHasClassSdocs()
    {
        global $s;

        $s = 1;
        $this->assertXPathContains(
            '//li[@class="sdocs"]/span',
            'Blog',
            $this->renderAllPages()
        );
    }

    /**
     * Tests that a selected childless page has the class "doc".
     *
     * @return void.
     */
    public function testSelectedChildlessPageHasClassSdoc()
    {
        $this->assertXPathContains(
            '//li[@class="sdoc"]/span',
            'Welcome',
            $this->renderAllPages()
        );
    }

    /**
     * Tests that a not selected page with children has the class "docs".
     *
     * @return void.
     */
    public function testNotSelectedPageHasClassDocs()
    {
        $this->assertXPathContains(
            '//li[@class="docs"]/a',
            'Blog',
            $this->renderAllPages()
        );
    }

    /**
     * Tests that a not selected childless page has the class "doc".
     *
     * @return void
     */
    public function testNotSelectedChildlessPageHasClassDoc()
    {
        global $s;

        $s = 1;
        $this->assertXPathContains(
            '//li[@class="doc"]/a',
            'Welcome',
            $this->renderAllPages()
        );
    }

    /**
     * Tests that the parent of the a selected page has a class depending on
     * menu_sdoc.
     *
     * @param string $sdoc  A menu_sdoc setting ('' or 'parent').
     * @param string $class A CSS class.
     *
     * @return void
     *
     * @dataProvider dataForParentOfSelectedPageHasClassDependingOnSdoc
     */
    public function testParentOfSelectedPageHasClassDependingOnSdoc($sdoc, $class)
    {
        global $s, $cf;

        $s = 2;
        $cf['menu']['sdoc'] = $sdoc;
        $this->assertXPathContains(
            sprintf('//li[@class="%s"]/a', $class),
            'Blog',
            $this->renderAllPages()
        );
    }

    /**
     * Provides data for testParentOfSelectedPageHasClassDependingOnSdoc().
     *
     * @return array
     */
    public function dataForParentOfSelectedPageHasClassDependingOnSdoc()
    {
        return array(
            array('parent', 'sdocs'),
            array('', 'docs')
        );
    }

    /**
     * Tests that a first level page with a third level child has a class
     * depending on menu_levelcatch.
     *
     * @param string $levelcatch A menu_levelcatch setting.
     * @param string $class      A CSS class.
     *
     * @return void
     *
     * @dataProvider dataForH1WithH3HasClassDependingOnLevelcatch
     */
    public function testH1WithH3HasClassDependingOnLevelcatch($levelcatch, $class)
    {
        global $cf;

        $cf['menu']['levelcatch'] = $levelcatch;
        $this->assertXPathContains(
            sprintf('//li[@class="%s"]/a', $class),
            'About',
            $this->renderAllPages()
        );
    }

    /**
     * Provides data for testH1WithH3HasClassDependingOnLevelcatch().
     *
     * @return array
     */
    public function dataForH1WithH3HasClassDependingOnLevelcatch()
    {
        return array(
            array('10', 'docs'),
            array('0', 'doc')
        );
    }

    /**
     * Tests that a page opens in a new window when in normal mode.
     *
     * @return void
     */
    public function testPageOpensInNewWindowInNormalMode()
    {
        $this->assertXPathContains(
            '//a[@target="_blank"]',
            'Cold',
            $this->renderAllPages()
        );
    }

    /**
     * Tests that a page doesn't open in a new window when in edit mode.
     *
     * @return void
     */
    public function testPageDoesntOpenInNewWindowInEditMode()
    {
        $this->setUpEditMode(true);
        $this->assertNotXPathContains(
            '//a[@target="_blank"]',
            'Cold',
            $this->renderAllPages()
        );
    }

    /**
     * Returns the rendering of all pages.
     *
     * @param mixed $forOrFrom A li() view kind or the start level.
     *
     * @return string HTML
     */
    private function renderAllPages($forOrFrom = 1)
    {
        return li(range(0, 10), $forOrFrom);
    }

    /**
     * Tests that the "Blog" submenu has exactly three items.
     *
     * @return void
     */
    public function testBlogSubmenuHasExactlyThreeItems()
    {
        global $s;

        $s = 1;
        $this->assertXPathCount(
            '//ul/li',
            3,
            li([2, 4, 6], 'submenu')
        );
    }

    /**
     * Tests that the "Blog" submenu has the proper structure.
     *
     * @return void
     */
    public function testBlogSubmenuHasProperStructure()
    {
        $this->assertXPathContains(
            '//ul[@class="submenu"]/li[@class="docs"]/a[@href="?Blog:July"]',
            'July',
            li([2, 4, 6], 'submenu')
        );
    }

    public function testBuildHcForThirdPage()
    {
        global $s, $si, $hc, $hl;

        $s = 3;
        XH_buildHc();
        $this->assertEquals(array(0, 1, 2, 3, 6, 7, 8, 9, 10), $hc);
        $this->assertEquals(4, $si);
        $this->assertEquals(9, $hl);
    }

    public function testBuildHcForFifthPage()
    {
        global $s, $si, $hc, $hl;

        $s = 5;
        XH_buildHc();
        $this->assertEquals(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10), $hc);
        $this->assertEquals(6, $si);
        $this->assertEquals(11, $hl);
    }
}
