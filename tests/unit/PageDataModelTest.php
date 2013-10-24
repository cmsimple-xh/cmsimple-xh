<?php

/**
 * Testing the page data model.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * The file under test.
 */
require './cmsimple/classes/PageDataModel.php';

/**
 * A test case for the page data model.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PageDataModelTest extends PHPUnit_Framework_TestCase
{
    protected $pd;

    public function setUp()
    {
        $h = array('Welcome', 'News');
        $fields = array('url', 'foo', 'bar', 'list');
        $temp = array('url' => 'deleted', 'foo' => '', 'bar' => '');
        $data = array(
            array('url' => 'wrong', 'foo' => 'foo0', 'bar' => 'bar0'),
            array('foo' => 'foo1', 'list' => 'foo,bar,baz')
        );
        $this->pd = new XH_PageDataModel($h, $fields, $temp, $data);
    }

    public function testFindKey()
    {
        $expected = array('url' => 'wrong', 'foo' => 'foo0', 'bar' => 'bar0', 'list' => '');
        $actual = $this->pd->findKey(0);
        $this->assertEquals($expected, $actual);
    }

    public function testFindFieldValue()
    {
        $expected = array(0 => array('url' => 'wrong', 'foo' => 'foo0', 'bar' => 'bar0', 'list' => ''));
        $actual = $this->pd->findFieldValue('foo', 'foo0');
        $this->assertEquals($expected, $actual);
    }

    public function testFindArrayfieldValue()
    {
        $expected = array(1 => array('foo' => 'foo1', 'list' => 'foo,bar,baz', 'url' => 'News', 'bar' => ''));
        $actual = $this->pd->findArrayfieldValue('list', 'bar', ',');
        $this->assertEquals($expected, $actual);
    }

    public function testFindFieldValueSortkey()
    {
        $expected = array(1, 0);
        $actual = array_keys($this->pd->findFieldValueSortkey('foo', 'foo', 'url', SORT_ASC, ''));
        $this->assertEquals($expected, $actual);
    }

    public function testAddParam()
    {
        $expected = array('url' => 'wrong', 'foo' => 'foo0', 'bar' => 'bar0', 'list' => '', 'new' => '');
        $this->pd->addParam('new');
        $actual = $this->pd->findKey(0);
        $this->assertEquals($expected, $actual);
    }

    public function testRemoveParam()
    {
        $this->pd->removeParam('bar');

        $expected = array('url' , 'foo', 'list');
        $actual = $this->pd->params;
        $this->assertEquals($expected, $actual);

        $expected = array('url' => 'wrong', 'foo' => 'foo0', 'list' => '');
        $actual = $this->pd->findKey(0);
        $this->assertEquals($expected, $actual);

        $expected = array('url' => 'deleted', 'foo' => '');
        $actual = $this->pd->temp_data;
        $this->assertEquals($expected, $actual);
    }

    public function dataForCreate()
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
     * @dataProvider dataForCreate
     */
    public function testCreate($params, $expected)
    {
        $actual = $this->pd->create($params);
        $this->assertEquals($expected, $actual);
    }
}

?>
