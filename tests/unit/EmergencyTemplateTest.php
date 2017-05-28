<?php

/**
 * Testing the emergency template.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit_Framework_TestCase;
use PHPUnit_Extensions_MockFunction;

/**
 * Testing the emergency template.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class EmergencyTemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * The function mocks.
     *
     * @var object
     */
    protected $mocks;

    /**
     * Sets up the test fixture.
     *
     * @return void
     */
    public function setUp()
    {
        $mockNames = array(
            'header', 'head', 'onload', 'toc', 'content', 'loginlink', 'XH_exit'
        );
        foreach ($mockNames as $mockName) {
            $this->mocks[$mockName] = new PHPUnit_Extensions_MockFunction(
                $mockName, null
            );
        }
        $this->expectOutputRegex('//');
    }

    /**
     * Tears down the test fixture.
     *
     * @return void
     */
    public function tearDown()
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
            ->will($this->returnValue('HTTP/1.0 503 Service Unavailable'));
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
            ->will($this->returnValue('Content-Type: text/html;charset=UTF-8'));
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

?>
