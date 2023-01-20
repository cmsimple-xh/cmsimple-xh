<?php

/**
 * Testing the pages class.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

/**
 * A test case for the pages class.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class PagesTest extends TestCase
{
    private $subject;

    protected function setUp(): void
    {
        global $h, $u, $l, $c, $cf;

        $h = array(
            'Welcome',
            'Blog',
            'July',
            'Hot',
            'Hidden',
            'Visible',
            'January',
            'Cold',
            'About',
            'Contact',
            'News'
        );
        $u = array(
            '?Welcome',
            '?Blog',
            '?Blog:July',
            '?Blog:July:Hot',
            '?Blog:Hidden',
            '?Blog:Hidden:Visible',
            '?Blog:January',
            '?Blog:January:Cold',
            '?About',
            '?About:Contact',
            '?News'
        );
        $l = array(
            1,
            1,
            2,
            3,
            2,
            3,
            2,
            3,
            1,
            2,
            1
        );
        $c = array(
            '<h1>Welcome</h1>',
            '<h1>Blog</h1>',
            '<h2>July</h2>',
            '<h3>Hot</h3>',
            '<h2>Hidden</h2> #CMSimple hide#',
            '<h3>Visible</h3>',
            '<h2>January</h2>',
            '<h3>Cold</h3>',
            '<h1>About</h1>',
            '<h2>Contact</h2>',
            '<h1>News</h1> #CMSimple hide#'
        );
        $cf['menu']['levelcatch'] = 10;
        $this->subject = new Pages();
    }

    public function dataForIsHidden()
    {
        return array(
            array(1, false),
            array(4, true)
        );
    }

    /**
     * @dataProvider dataForIsHidden
     */
    public function testIsHidden($n, $expected)
    {
        $actual = $this->subject->isHidden($n);
        $this->assertEquals($expected, $actual);
    }

    public function testGetCount()
    {
        global $c;

        $expected = count($c);
        $actual = $this->subject->getCount();
        $this->assertEquals($expected, $actual);
    }

    public function testHeading()
    {
        global $h;

        for ($i = 0; $i < $this->subject->getCount(); ++$i) {
            $expected = $h[$i];
            $actual = $this->subject->heading($i);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testName()
    {
        global $h;

        for ($i = 0; $i < $this->subject->getCount(); ++$i) {
            $expected = html_entity_decode(strip_tags($h[$i]), ENT_QUOTES, 'UTF-8');
            $actual = $this->subject->name($i);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testUrl()
    {
        global $u;

        for ($i = 0; $i < $this->subject->getCount(); ++$i) {
            $expected = $u[$i];
            $actual = $this->subject->url($i);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testLevel()
    {
        global $l;

        for ($i = 0; $i < $this->subject->getCount(); ++$i) {
            $expected = $l[$i];
            $actual = $this->subject->level($i);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testContent()
    {
        global $c;

        for ($i = 0; $i < $this->subject->getCount(); ++$i) {
            $expected = $c[$i];
            $actual = $this->subject->content($i);
            $this->assertEquals($expected, $actual);
        }
    }

    public function dataForToplevels()
    {
        return array(
            array(false, array(0, 1, 8, 10)),
            array(true, array(0, 1, 8))
        );
    }

    /**
     * @dataProvider dataForToplevels
     */
    public function testToplevels($ignoreHidden, $expected)
    {
        $actual = $this->subject->toplevels($ignoreHidden);
        $this->assertEquals($expected, $actual);
    }

    public function testToplevelsDefaults()
    {
        $expected = array(0, 1, 8);
        $actual = $this->subject->toplevels();
        $this->assertEquals($expected, $actual);
    }

    public function dataForChildren()
    {
        return array(
            array(0, false, array()),
            array(1, false, array(2, 4, 6)),
            array(1, true, array(2, 6))
        );
    }

    /**
     * @dataProvider dataForChildren
     */
    public function testChilren($n, $ignoreHidden, $expected)
    {
        $actual = $this->subject->children($n, $ignoreHidden);
        $this->assertEquals($expected, $actual);
    }

    public function testChildrenDefaults()
    {
        $expected = array(2, 6);
        $actual = $this->subject->children(1);
        $this->assertEquals($expected, $actual);
    }

    public function dataForParent()
    {
        return array(
            array(7, false, 6),
            array(0, false, null),
            array(5, false, 4),
            array(5, true, 2)
        );
    }

    /**
     * @dataProvider dataForParent
     */
    public function testParent($n, $ignoreHidden, $expected)
    {
        $actual = $this->subject->parent($n, $ignoreHidden);
        $this->assertEquals($expected, $actual);
    }

    public function testParentDefaults()
    {
        $expected = 2;
        $actual = $this->subject->parent(5);
        $this->assertEquals($expected, $actual);
    }

    public function dataForPageWithHeading()
    {
        return array(
            array('Hot', 3),
            array('Cold', 7),
            array('Not there', -1)
        );
    }

    /**
     * @dataProvider dataForPageWithHeading
     */
    public function testPageWithHeading($heading, $expected)
    {
        $actual = $this->subject->pageWithHeading($heading);
        $this->assertEquals($expected, $actual);
    }

    public function testDefaultLinkList()
    {
        $indent = "\xC2\xA0\xC2\xA0\xC2\xA0\xC2\xA0";
        $expected = array(
            array('Welcome', '?Welcome'),
            array('Blog', '?Blog'),
            array("{$indent}July", '?Blog:July'),
            array("$indent{$indent}Hot", '?Blog:July:Hot'),
            array("$indent{$indent}Visible", '?Blog:Hidden:Visible'),
            array("{$indent}January", '?Blog:January'),
            array("$indent{$indent}Cold", '?Blog:January:Cold'),
            array("About", '?About'),
            array("{$indent}Contact", '?About:Contact'),
        );
        $this->assertEquals($expected, $this->subject->linkList());
    }

    public function testCustomLinkList()
    {
        $indent = "\xC2\xA0\xC2\xA0\xC2\xA0\xC2\xA0";
        $expected = array(
            array('* Welcome', '?Welcome'),
            array('* Blog', '?Blog'),
            array("* {$indent}July", '?Blog:July'),
            array("* $indent{$indent}Hot", '?Blog:July:Hot'),
            array("* $indent}Hidden", '?Blog:Hidden'),
            array("* $indent{$indent}Visible", '?Blog:Hidden:Visible'),
            array("* {$indent}January", '?Blog:January'),
            array("* $indent{$indent}Cold", '?Blog:January:Cold'),
            array("* About", '?About'),
            array("* {$indent}Contact", '?About:Contact'),
            array('* News', '?News')
        );
        $this->assertEquals($expected, $this->subject->linkList('* ', false));
    }

    public function testGetAncestorsOf()
    {
        $this->assertEquals(array(2, 1), $this->subject->getAncestorsOf(5));
    }

    public function testGetAncestorsOfEvenIfHidden()
    {
        $this->assertEquals(
            array(4, 1),
            $this->subject->getAncestorsOf(5, false)
        );
    }
}
