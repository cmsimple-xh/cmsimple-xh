<?php

/**
 * Testing the page data router.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './cmsimple/functions.php';
require_once './cmsimple/classes/PageDataModel.php';

/**
 * The file under test.
 */
require_once './cmsimple/classes/PageDataRouter.php';

/**
 * A test case for the page data router.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PageDataRouterTest extends PHPUnit_Framework_TestCase
{
    protected $pd;

    public function setUp()
    {
        $h = array('Welcome', 'News');
        $fields = array('url', 'foo', 'bar', 'list');
        $temp = array('url' => 'deleted', 'foo' => '', 'bar' => '', 'baz' => 42);
        $data = array(
            array('url' => 'wrong', 'foo' => 'foo0', 'bar' => 'bar0'),
            array('foo' => 'foo1', 'list' => 'foo,bar,baz', 'snork' => true)
        );
        $this->pd = new XH_PageDataRouter($h, $fields, $temp, $data);
    }

    public function testStoredFields()
    {
        $expected = array('url', 'foo', 'bar', 'list', 'baz', 'snork');
        $actual = $this->pd->storedFields();
        $this->assertEquals($expected, $actual);
    }

    public function testFindPage()
    {
        $expected = array('url' => 'wrong', 'foo' => 'foo0', 'bar' => 'bar0', 'list' => '');
        $actual = $this->pd->find_page(0);
        $this->assertEquals($expected, $actual);
    }

    public function testFindAll()
    {
        $expected = array(
            array('url' => 'wrong', 'foo' => 'foo0', 'bar' => 'bar0', 'list' => ''),
            array('foo' => 'foo1', 'list' => 'foo,bar,baz', 'url' => 'News', 'bar' => '', 'snork' => true)
        );
        $actual = $this->pd->find_all();
        $this->assertEquals($expected, $actual);
    }

    public function testFindFieldValue()
    {
        $expected = array(0 => array('url' => 'wrong', 'foo' => 'foo0', 'bar' => 'bar0', 'list' => ''));
        $actual = $this->pd->find_field_value('foo', 'foo0');
        $this->assertEquals($expected, $actual);
    }

    public function testFindFieldValueArray()
    {
        $expected = array(1 => array('foo' => 'foo1', 'list' => 'foo,bar,baz', 'url' => 'News', 'bar' => '', 'snork' => true));
        $actual = $this->pd->find_field_value('list', 'bar', ',');
        $this->assertEquals($expected, $actual);
    }

    public function testFindFieldValueSortkey()
    {
        $expected = array(1, 0);
        $actual = array_keys($this->pd->find_field_value_sortkey('foo', 'foo', 'url', SORT_ASC, ''));
        $this->assertEquals($expected, $actual);
    }

    public function testAddInterest()
    {
        $expected = array('url' => 'wrong', 'foo' => 'foo0', 'bar' => 'bar0', 'list' => '', 'new' => '');
        $this->pd->add_interest('new');
        $actual = $this->pd->find_page(0);
        $this->assertEquals($expected, $actual);
    }

    public function testRemoveInterest()
    {
        $this->pd->removeInterest('snork');

        $expected = array('foo' => 'foo1', 'list' => 'foo,bar,baz', 'url' => 'News', 'bar' => '');
        $actual = $this->pd->find_page(1);
        $this->assertEquals($expected, $actual);
    }

    public function dataForNewPage()
    {
        return array(
            array(
                array(),
                array('url' => '', 'foo' => '', 'bar' => '', 'list' => '')
            ),
            array(
                array('foo' => 'foo', 'bar' => 'bar'),
                array('url' => '', 'foo' => 'foo', 'bar' => 'bar', 'list' => '')
            )
        );
    }

    /**
     * @dataProvider dataForNewPage
     */
    public function testNewPage($params, $expected)
    {
        $actual = $this->pd->new_page($params);
        $this->assertEquals($expected, $actual);
    }

    public function testNewPageDoesntAppendPage()
    {
        $before = count($this->pd->find_all());
        $this->pd->new_page();
        $after = count($this->pd->find_all());
        $this->assertEquals(0, $after - $before);
    }

    public function testAppendPage()
    {
        $before = count($this->pd->find_all());
        $this->pd->appendNewPage();
        $after = count($this->pd->find_all());
        $this->assertEquals(1, $after - $before);
    }

    public function testHeadAsPHP()
    {
        $expected = "<?php\n\$page_data_fields=array('url','foo','bar','list');\n"
            . "\$temp_data=array(\n'url'=>'deleted',\n'foo'=>'',\n'bar'=>'',\n'baz'=>'42'\n);\n?>\n";
        $actual = $this->pd->headAsPHP();
        $this->assertEquals($expected, $actual);
    }

    public function testPageAsPHP()
    {
        $expected = "<?php\n\$page_data[]=array(\n'url'=>'wrong',\n'foo'=>'foo0',\n'bar'=>'bar0',\n'list'=>''\n);\n?>\n";
        $actual = $this->pd->pageAsPHP(0);
        $this->assertEquals($expected, $actual);
    }
}

?>
