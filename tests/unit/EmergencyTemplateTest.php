<?php

/**
 * Testing the emergency template.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

/**
 * Testing the emergency template.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6.3
 */
class EmergencyTemplateTest extends TestCase
{
    /**
     * The function mocks.
     *
     * @var object
     */
    protected $mocks;

    /**
     * Sets up the test fixture.
     */
    protected function setUp(): void
    {
        $mockNames = array(
            'header', 'head', 'onload', 'toc', 'content', 'loginlink', 'XH_exit'
        );
        foreach ($mockNames as $mockName) {
            $this->mocks[$mockName] = $this->createFunctionMock($mockName);
        }
        $this->expectOutputRegex('//');
    }

    /**
     * Tears down the test fixture.
     */
    protected function tearDown(): void
    {
        foreach ($this->mocks as $mock) {
            $mock->restore();
        }
    }

    /**
     * Tests that it responds with 503.
     *
     * @return void
     */
    public function testRespondsWith503()
    {
        $this->mocks['header']->expects($this->atLeastOnce())
            ->willReturn('HTTP/1.0 503 Service Unavailable');
        XH_emergencyTemplate();
    }

    /**
     * Tests that the correct content type is set.
     *
     * @return void
     */
    public function testSetsCorrectContentType()
    {
        $this->mocks['header']->expects($this->atLeastOnce())
            ->willReturn('Content-Type: text/html;charset=UTF-8');
        XH_emergencyTemplate();
    }

    /**
     * Tests that head() is called.
     *
     * @return void
     */
    public function testCallsHead()
    {
        $this->mocks['head']->expects($this->once());
        XH_emergencyTemplate();
    }

    /**
     * Tests that onload() is called.
     *
     * @return void
     */
    public function testCallsOnload()
    {
        $this->mocks['onload']->expects($this->once());
        XH_emergencyTemplate();
    }

    /**
     * Tests that toc() is called.
     *
     * @return void
     */
    public function testCallsToc()
    {
        $this->mocks['toc']->expects($this->once());
        XH_emergencyTemplate();
    }

    /**
     * Tests that content() is called.
     *
     * @return void
     */
    public function testCallsContent()
    {
        $this->mocks['content']->expects($this->once());
        XH_emergencyTemplate();
    }

    /**
     * Tests that loginlink() is called.
     *
     * @return void
     */
    public function testCallsLoginlink()
    {
        $this->mocks['loginlink']->expects($this->once());
        XH_emergencyTemplate();
    }

    /**
     * Tests that the script is exited.
     *
     * @return void
     */
    public function testExitsScript()
    {
        $this->mocks['XH_exit']->expects($this->once());
        XH_emergencyTemplate();
    }
}
