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
 * Testing the rendering of error messages.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6.5
 */
class ControllerRenderErrorMessagesTest extends TestCase
{
    /**
     * The test subject.
     *
     * @var Controller
     */
    protected $subject;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    protected function setUp()
    {
        global $e;

        $e = '<li>First error</li>'
            . '<li>Second error</li>';
        $this->subject = new Controller();
    }

    /**
     * Tests render error messages.
     *
     * @return void
     */
    public function testRenderErrorMessages()
    {
        $this->assertXPathCount(
            '//div[@class="xh_warning"]/ul/li',
            2,
            $this->subject->renderErrorMessages()
        );
    }
}
