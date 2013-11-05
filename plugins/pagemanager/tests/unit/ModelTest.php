<?php

/**
 * Testing the model class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2013 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: ModelTest.php 109 2013-10-23 18:54:54Z Chistoph Becker $
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

/**
 * The file under test.
 */
require_once './classes/Model.php';

/**
 * A test case to for the model class.
 *
 * @category Testing
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class ModelTest extends PHPUnit_Framework_TestCase
{
    protected $model;

    public function setUp()
    {
        global $c, $cl, $l, $cf, $tx;

        $c = array(
            '<h1>Welcome</h1>',
            '<h2>Subpage</h2>',
            '<h2>Subpage</h2>',
            '<h2></h2>'
        );
        $cl = count($c);
        $l = array(1, 2, 2, 2);

        $cf['menu']['levels'] = '3';
        $tx['toc']['empty'] = 'EMPTY HEADING';

        $this->model = new Pagemanager_Model();
    }

    public function testGetHeadings()
    {
        $expected = array(
            'Welcome',
            'Subpage',
            'Subpage', // not "DUPLICATE HEADING 1"
            'EMPTY HEADING 1'
        );
        $this->model->getHeadings();
        $actual = $this->model->headings;
        $this->assertEquals($expected, $actual);
    }

    public function testIsIrregular()
    {
        global $l;

        $this->assertFalse($this->model->isIrregular());
        $l = array(1, 3);
        $this->assertTrue($this->model->isIrregular());
    }
}


?>
