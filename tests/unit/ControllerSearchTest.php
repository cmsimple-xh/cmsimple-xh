<?php

/**
 * Testing the controller functionality.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the handling of search requests.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
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
     */
    protected function setUp(): void
    {
        global $pth, $tx;

        $pth['file']['search'] = '';
        $tx['title']['search'] = 'Search';
        $this->subject = $this->getMockBuilder(Controller::class)->setMethods(array('makeSearch'))->getMock();
        $this->searchMock = $this->createMock(Search::class);
        $this->subject->method('makeSearch')
            ->willReturn($this->searchMock);
    }

    /**
     * Tests that the title is set.
     *
     * @return void
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
