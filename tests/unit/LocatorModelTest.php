<?php

/**
 * Testing the locator (breadcrumb navigation) model.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2015-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit_Framework_TestCase;
use PHPUnit_Extensions_MockFunction;

/**
 * A test case for the locator model.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.7
 */
class LocatorModelTest extends PHPUnit_Framework_TestCase
{
    protected $hideMock;

    public function setUp()
    {
        global $sn, $f, $cf, $tx, $xh_publisher;

        $this->setUpContent();
        $sn = '';
        $xh_publisher = $this->getMockBuilder('XH\\Publisher')
            ->disableOriginalConstructor()
            ->getMock();
        $xh_publisher->method('getFirstPublishedPage')->willReturn(0);
        $f = '';
        $cf = array(
            'locator' => array('show_homepage' => 'true'),
            'show_hidden' => array('path_locator' => '')
        );
        $tx = array(
            'locator' => array('home' => 'Home')
        );
        $this->setUpMocks();
    }

    protected function setUpContent()
    {
        global $h, $l, $u;

        $h = array(
            'foo',
            'Dresses',
            'Real Dresses',
            'News'
        );
        $l = array(1, 1, 2, 1);
        $u = array(
            'foo',
            'Dresses',
            'Dresses/Real-Dresses',
            'News'
        );
    }

    protected function setUpMocks()
    {
        $this->hideMock = new PHPUnit_Extensions_MockFunction('hide', null);
        $hideMap = array(
            array(0, false),
            array(1, false),
            array(2, false),
            array(3, true)
        );
        $this->hideMock->expects($this->any())->will($this->returnValueMap($hideMap));
    }

    public function tearDown()
    {
        $this->hideMock->restore();
    }

    public function testHomePage()
    {
        global $s, $h, $title;

        $s = 0;
        $title = $h[$s];
        $this->assertEquals(array(array('foo', '?foo')), XH_getLocatorModel());
    }

    public function testDressesPage()
    {
        global $s, $h, $title;

        $s = 1;
        $title = $h[$s];
        $this->assertEquals(
            array(array('Home', '?foo'), array('Dresses', '?Dresses')),
            XH_getLocatorModel()
        );
    }

    public function testRealDressesPage()
    {
        global $s, $h, $title;

        $s = 2;
        $title = $h[$s];
        $this->assertEquals(
            array(
                array('Home', '?foo'),
                array('Dresses', '?Dresses'),
                array('Real Dresses', '?Dresses/Real-Dresses')
            ),
            XH_getLocatorModel()
        );
    }

    public function testHiddenPage()
    {
        global $s, $h, $title;

        $s = 3;
        $title = $h[$s];
        $this->assertEquals(array(array('News', '?News')), XH_getLocatorModel());
    }

    public function testChangedTitle()
    {
        global $s, $h, $title;

        $s = 1;
        $title = 'Suits';
        $this->assertEquals(
            array(array('Home', '?foo'), array('Suits', null)),
            XH_getLocatorModel()
        );
    }

    public function testSpecialFunction()
    {
        global $s, $f;

        $s = -1;
        $f = 'mailform';
        $this->assertEquals(array(array('Mailform', null)), XH_getLocatorModel());
    }

    public function testUnpublishedHomePage()
    {
        global $s, $xh_publisher;

        $xh_publisher = $this->getMockBuilder('XH\\Publisher')
            ->disableOriginalConstructor()
            ->getMock();
        $xh_publisher->method('getFirstPublishedPage')->willReturn(1);
        $s = 0;
        $this->assertEquals(array(array('&nbsp;', null)), XH_getLocatorModel());
    }

    public function testDoNotShowHomePage()
    {
        global $s, $h, $title, $cf;

        $s = 1;
        $title = $h[$s];
        $cf['locator']['show_homepage'] = '';
        $this->assertEquals(array(array('Dresses', '?Dresses')), XH_getLocatorModel());
    }

    public function testDoShowHiddenPathLocator()
    {
        global $s, $h, $title, $cf;

        $s = 3;
        $title = $h[$s];
        $cf['show_hidden']['path_locator'] = 'true';
        $this->assertEquals(
            array(array('Home', '?foo'), array('News', '?News')),
            XH_getLocatorModel()
        );
    }
}

?>
