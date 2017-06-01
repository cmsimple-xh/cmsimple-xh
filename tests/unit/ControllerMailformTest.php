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

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the handling of mailform requests.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class ControllerMailformTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * The mailform mock.
     *
     * @var Mailform
     */
    protected $mailformMock;

    /**
     * The head() mock.
     *
     * @var object
     */
    protected $sheadMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array The configuration of the core.
     * @global array The localization of the core.
     */
    public function setUp()
    {
        global $cf, $tx;

        $cf['mailform']['email'] = 'devs@cmsimple-xh.org';
        $tx['title']['mailform'] = 'Mailform';
        $this->subject = $this->getMockBuilder(Controller::class)->setMethods(array('makeMailform'))->getMock();
        $this->mailformMock = $this->createMock(Mailform::class);
        $this->subject->method('makeMailform')
            ->willReturn($this->mailformMock);
        $this->sheadMock = $this->getFunctionMock('shead');
    }

    protected function tearDown()
    {
        $this->sheadMock->restore();
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

        $this->subject->handleMailform();
        $this->assertEquals('Mailform', $title);
    }

    /**
     * Tests the rendered HTML.
     *
     * @return void
     *
     * @global string The HTML of the content area.
     */
    public function testRenderedHTML()
    {
        global $o;

        $this->subject->handleMailform();
        $this->assertXPathContains('//div[@id="xh_mailform"]/h1', 'Mailform', $o);
    }

    /**
     * Tests that ::process() is called.
     *
     * @return void
     */
    public function testCallsProcess()
    {
        $this->mailformMock->expects($this->once())->method('process');
        $this->subject->handleMailform();
    }

    /**
     * Tests that shead() is called when the mailform is disabled.
     *
     * @return void
     *
     * @global array The configuration of the core.
     */
    public function testCallsSheadWhenMailformIsDisabled()
    {
        global $cf;

        $cf['mailform']['email'] = '';
        $this->sheadMock->expects($this->once());
        $this->subject->handleMailform();
    }
}
