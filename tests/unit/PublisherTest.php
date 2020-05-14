<?php

/**
 * Test case for the Publisher class.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2017-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

class PublisherTest extends TestCase
{
    /**
     * @var Publisher
     */
    private $subject;

    protected function setUp()
    {
        global $pd_router, $edit, $c;

        $this->setConstant('XH_ADM', false);
        $edit = false;
        $pd_router = $this->createMock(PageDataRouter::class);
        $pd_router->method('find_all')->willReturn(array(
            0 => array(
                'linked_to_menu' => '1',
                'published' => '1',
                'publication_date' => '',
                'expires' => ''
            ),
            1 => array(
                'linked_to_menu' => '1',
                'published' => '0',
                'publication_date' => '',
                'expires' => ''
            ),
            2 => array(
                'linked_to_menu' => '0',
                'published' => '1',
                'publication_date' => '',
                'expires' => ''
            ),
            3 => array(
                'linked_to_menu' => '0',
                'published' => '0',
                'publication_date' => '',
                'expires' => ''
            ),
            4 => array(
                'linked_to_menu' => '1',
                'published' => '1',
                'publication_date' => '1970-01-01',
                'expires' => '2037-12-31'
            ),
            5 => array(
                'linked_to_menu' => '1',
                'published' => '1',
                'publication_date' => '2037-12-31',
                'expires' => ''
            ),
            6 => array(
                'linked_to_menu' => '1',
                'published' => '1',
                'publication_date' => '',
                'expires' => '1970-01-01'
            ),
            7 => array(
                'linked_to_menu' => '1',
                'published' => '1',
                'publication_date' => '',
                'expires' => ''
            ),
            8 => array(
                'linked_to_menu' => '1',
                'published' => '1',
                'publication_date' => '',
                'expires' => ''
            )
        ));
        $c = array(
            0 => '',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => '',
            6 => '',
            7 => '',
            8 => '#cmsimple hide#'
        );
        $this->subject = new Publisher([false, false, false, false, false, false, false, true, false]);
    }

    /**
     * @dataProvider provideIsPublishedData
     * @param int $index
     * @param bool $expected
     */
    public function testIsPublished($index, $expected)
    {
        $this->assertSame($expected, $this->subject->isPublished($index));
    }

    /**
     * @return array[]
     */
    public function provideIsPublishedData()
    {
        return array(
            [0, true],
            [1, false],
            [2, true],
            [3, false],
            [4, true],
            [5, false],
            [6, false],
            [7, false]
        );
    }

    /**
     * @dataProvider provideIsHiddenData
     * @param int $index
     * @param bool $expected
     */
    public function testIsHidden($index, $expected)
    {
        $this->assertSame($expected, $this->subject->isHidden($index));
    }

    /**
     * @return array[]
     */
    public function provideIsHiddenData()
    {
        return array(
            [0, false],
            [1, false],
            [2, true],
            [3, true],
            [8, true]
        );
    }

    public function testGetFirstPublishedPage()
    {
        $this->assertSame(0, $this->subject->getFirstPublishedPage());
    }
}
