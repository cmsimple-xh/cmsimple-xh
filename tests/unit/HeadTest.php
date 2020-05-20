<?php

/**
 * Testing the head() function.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

/**
 * Testing the head() function.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6.3
 */
class HeadTest extends TestCase
{
    /**
     * The XH_title() mock.
     *
     * @var object
     */
    protected $titleMock;

    /**
     * The XH_plugins() mock.
     *
     * @var object
     */
    protected $pluginsMock;

    /**
     * The XH_pluginStylesheet() mock.
     *
     * @var object
     */
    protected $pluginStylesheetMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    protected function setUp()
    {
        global $pth, $cf, $tx;

        $this->setConstant('CMSIMPLE_XH_VERSION', 'CMSimple_XH 1.6.3');
        $this->setConstant('CMSIMPLE_XH_BUILD', '2014081201');
        $pth['file'] = array(
            'corestyle' => 'corestyle',
            'stylesheet' => 'stylesheet'
        );
        $cf = array(
            'meta' => array('robots' => 'index, follow'),
            'site' => array(
                'title' => ''
            )
        );
        $tx = array(
            'meta' => array('keywords' => 'CMSimple, XH')
        );
        $this->titleMock = $this->createFunctionMock('XH_title');
        $this->pluginsMock = $this->createFunctionMock('XH_plugins');
        $this->pluginsMock->expects($this->any())->willReturn(array());
        $this->pluginStylesheetMock = $this->createFunctionMock('XH_pluginStylesheet');
        $this->pluginStylesheetMock->expects($this->once());
    }

    /**
     * Tears down the test fixture.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->titleMock->restore();
        $this->pluginsMock->restore();
        $this->pluginStylesheetMock->restore();
    }

    /**
     * Tests that the title element is rendered without tags.
     *
     * @return void
     */
    public function testRendersTitleWithoutTags()
    {
        $this->titleMock->expects($this->any())
            ->willReturn('<b>Website</b>');
        $this->assertXPathContains('//title', 'Website', head());
    }

    /**
     * Tests that the meta content-type element is rendered.
     *
     * @return void
     */
    public function testRendersContentType()
    {
        $this->assertXPath(
            '//meta[@http-equiv="content-type" and @content="text/html;charset=UTF-8"]',
            head()
        );
    }

    /**
     * Tests that the meta robots element is rendered.
     *
     * @return void
     */
    public function testRendersMetaRobots()
    {
        $this->assertXPath(
            '//meta[@name="robots" and @content="index, follow"]',
            head()
        );
    }

    /**
     * Tests that the meta keyword element is rendered.
     *
     * @return void
     */
    public function testRendersMetaKeywords()
    {
        $this->assertXPath(
            '//meta[@name="keywords" and @content="CMSimple, XH"]',
            head()
        );
    }

    /**
     * Tests that the meta generator is rendered.
     *
     * @return void
     */
    public function testDoesNotRenderMetaGenerator()
    {
        $error_reporting = error_reporting();
        error_reporting(0);
        $this->assertNotXPath(
            '//meta[@name="generator"]',
            head()
        );
        error_reporting($error_reporting);
    }

    /**
     * Tests that the prev link is rendered.
     *
     * @return void
     */
    public function testRendersPrevLink()
    {
        $findPreviousPageMock = $this->createFunctionMock('XH_findPreviousPage');
        $findPreviousPageMock->expects($this->any())->willReturn(0);
        $getPageUrlMock = $this->createFunctionMock('XH_getPageURL');
        $getPageUrlMock->expects($this->any())->willReturn('/xh/?previous');
        $this->assertXPath(
            '//link[@rel="prev" and @href="/xh/?previous"]',
            head()
        );
        $getPageUrlMock->restore();
        $findPreviousPageMock->restore();
    }

    /**
     * Tests that the next page link is rendered.
     *
     * @return void
     */
    public function testRendersNextLink()
    {
        $findNextPageMock = $this->createFunctionMock('XH_findNextPage');
        $findNextPageMock->expects($this->any())->willReturn(0);
        $getPageUrlMock = $this->createFunctionMock('XH_getPageURL');
        $getPageUrlMock->expects($this->any())->willReturn('/xh/?next');
        $this->assertXPath(
            '//link[@rel="next" and @href="/xh/?next"]',
            head()
        );
        $getPageUrlMock->restore();
        $findNextPageMock->restore();
    }

    /**
     * Tests that the template stylesheet link element is rendered.
     *
     * @return void
     */
    public function testRendersTemplateStylesheetLink()
    {
        $getPageUrlMock = $this->createFunctionMock('XH_getPageURL');
        $getPageUrlMock->expects($this->any())->willReturn('some URL');
        $this->assertXPath(
            '//link[@rel="stylesheet" and @type="text/css" and @href="stylesheet"]',
            head()
        );
        $getPageUrlMock->restore();
    }
}
