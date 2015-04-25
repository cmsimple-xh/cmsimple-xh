<?php

/**
 * Testing the locator (breadcrumb menu).
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';
require_once './cmsimple/functions.php';
require_once './cmsimple/tplfuncs.php';

/**
 * A test case for the locator.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.7
 */
class LocatorTest extends PHPUnit_Framework_TestCase
{
    protected $hideMock;

    protected $aMock;

    public function setUp()
    {
        global $_XH_firstPublishedPage, $f, $cf, $tx;

        $this->setUpContent();
        $_XH_firstPublishedPage = 0;
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
        $this->aMock = new PHPUnit_Extensions_MockFunction('a', null);
        $this->aMock->expects($this->any())->will(
            $this->returnCallback(
                function ($i) {
                    global $u;

                    return '<a href="' . $u[$i] . '">';
                }
            )
        );
    }

    public function tearDown()
    {
        $this->hideMock->restore();
        $this->aMock->restore();
    }

    public function testHomePage()
    {
        global $s, $h, $title;

        $s = 0;
        $title = $h[$s];
        $this->assertEquals('foo', locator());
    }

    public function testDressesPage()
    {
        global $s, $h, $title;

        $s = 1;
        $title = $h[$s];
        $this->assertEquals('<a href="foo">Home</a> &gt; Dresses', locator());
    }

    public function testRealDressesPage()
    {
        global $s, $h, $title;

        $s = 2;
        $title = $h[$s];
        $this->assertEquals(
            '<a href="foo">Home</a> &gt; <a href="Dresses">Dresses</a> &gt; Real Dresses',
            locator()
        );
    }

    public function testHiddenPage()
    {
        global $s, $h, $title;

        $s = 3;
        $title = $h[$s];
        $this->assertEquals('News', locator());
    }

    public function testChangedTitle()
    {
        global $s, $h, $title;

        $s = 1;
        $title = 'Suits';
        $this->assertEquals('<a href="foo">Home</a> &gt; Suits', locator());
    }

    public function testSpecialFunction()
    {
        global $s, $f;

        $s = -1;
        $f = 'mailform';
        $this->assertEquals('Mailform', locator());
    }

    public function testUnpublishedHomePage()
    {
        global $_XH_firstPublishedPage, $s;

        $_XH_firstPublishedPage = 1;
        $s = 0;
        $this->assertEquals('&nbsp;', locator());
    }

    public function testDoNotShowHomePage()
    {
        global $s, $h, $title, $cf;

        $s = 1;
        $title = $h[$s];
        $cf['locator']['show_homepage'] = '';
        $this->assertEquals('Dresses', locator());
    }

    public function testDoShowHiddenPathLocator()
    {
        global $s, $h, $title, $cf;

        $s = 3;
        $title = $h[$s];
        $cf['show_hidden']['path_locator'] = 'true';
        $this->assertEquals('<a href="foo">Home</a> &gt; News', locator());
    }
}

?>
