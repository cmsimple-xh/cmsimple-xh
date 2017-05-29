<?php

/**
 * Testing the controller functionality.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit_Extensions_MockFunction;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the handling of search requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerSearchTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * The search mock.
     *
     * @var Search
     */
    protected $searchMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array The localization of the core.
     */
    public function setUp()
    {
        global $tx;

        $tx['title']['search'] = 'Search';
        $this->subject = $this->getMock('XH\Controller', array('makeSearch'));
        $this->searchMock = $this->getMockBuilder('XH\Search')
            ->disableOriginalConstructor()->getMock();
        $this->subject->expects($this->any())->method('makeSearch')
            ->will($this->returnValue($this->searchMock));
    }

    /**
     * Tests that the title is set.
     *
     * @return void
     *
     * @global string The content of the title element.
     */
    public function testSetsTitle()
    {
        global $title;

        $this->subject->handleSearch();
        $this->assertEquals('Search', $title);
    }

    /**
     * Tests that ::render() is called.
     *
     * @return void
     */
    public function testCallsRender()
    {
        $this->searchMock->expects($this->once())->method('render');
        $this->subject->handleSearch();
    }
}
