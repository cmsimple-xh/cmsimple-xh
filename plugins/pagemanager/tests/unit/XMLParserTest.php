<?php

/**
 * Testing the XML parser class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2013 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: XMLParserTest.php 121 2013-11-01 14:38:19Z Chistoph Becker $
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

/**
 * The class under test.
 */
require_once './classes/XMLParser.php';

require_once '../../cmsimple/classes/PageDataRouter.php';
require_once '../../cmsimple/functions.php';

/**
 * A test case to for the XML parser class.
 *
 * @category Testing
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class XMLParserTest extends PHPUnit_Framework_TestCase
{
    var $parser;

    protected function setUpPDRouterStub()
    {
        global $pd_router;

        $pd_router = $this->getMockBuilder('XH_PageDataRouter')
            ->disableOriginalConstructor()
            ->getMock();
        $pd_router->expects($this->any())
            ->method('new_page')
            ->will($this->returnValue(array('url' => '', 'foo' => 'bar')));
        $map = array(
            array(0, array('url' => 'Welcome', 'foo' => 'bar')),
            array(1, array('url' => 'About', 'foo' => 'bar')),
            array(2, array('url' => 'News', 'foo' => 'bar'))
        );
        $pd_router->expects($this->any())
            ->method('find_page')
            ->will($this->returnValueMap($map));
    }

    protected function setUp()
    {
        $contents = array(
            '<h1>Welcome</h1>Welcome to my website!',
            '<h2>About</h2>About me',
            '<h1>News</h1>Here are some news.'
        );
        $levels = 3;
        $pdattrName = 'show';
        $this->setUpPDRouterStub();
        $this->parser = new Pagemanager_XMLParser($contents, $levels, $pdattrName);
    }

    public function dataForParse()
    {
        return array(
            array( // unmodified
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" rel="" state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="1" rel="">
            <content><name><![CDATA[About]]></name></content>
        </item>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="0" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // insert page
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" rel="" state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="1" rel="">
            <content><name><![CDATA[About]]></name></content>
        </item>
        <item id="" title="New Page" data-pdattr="1" rel="">
            <content><name><![CDATA[New Page]]></name></content>
        </item>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="0" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h2>New Page</h2>',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'New_Page', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // delete page
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" rel="" state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="0" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // move page
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" rel="" state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="1" rel="">
            <content><name><![CDATA[About]]></name></content>
            <item id="pagemanager-2" title="News" data-pdattr="0" rel="">
                <content><name><![CDATA[News]]></name></content>
            </item>
        </item>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h3>News</h3>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // copy page
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="1" rel="" state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="1" rel="">
            <content><name><![CDATA[About]]></name></content>
        </item>
        <item id="copy_pagemanager-1" title="About" data-pdattr="1" rel="new">
            <content><name><![CDATA[DUPLICATE HEADING 1]]></name></content>
        </item>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="0" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h2>About</h2>About me',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '1'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '0')
                )
            ),
            array( // flip page data attribute
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item id="pagemanager-0" title="Welcome" data-pdattr="0" rel="" state="closed">
        <content><name><![CDATA[Welcome]]></name></content>
        <item id="pagemanager-1" title="About" data-pdattr="0" rel="">
            <content><name><![CDATA[About]]></name></content>
        </item>
    </item>
    <item id="pagemanager-2" title="News" data-pdattr="1" rel="">
        <content><name><![CDATA[News]]></name></content>
    </item>
</root>'
XML
                , array(
                    '<h1>Welcome</h1>Welcome to my website!',
                    '<h2>About</h2>About me',
                    '<h1>News</h1>Here are some news.'
                ),
                array(
                    array('url' => 'Welcome', 'foo' => 'bar', 'show' => '0'),
                    array('url' => 'About', 'foo' => 'bar', 'show' => '0'),
                    array('url' => 'News', 'foo' => 'bar', 'show' => '1')
                )
            )
        );
    }

    /**
     * @dataProvider dataForParse
     */
    public function testParse($xml, $expectedContent, $expectedPageData)
    {
        global $pd_router;
        $this->parser->parse($xml);
        $this->assertEquals($expectedContent, $this->parser->getContents());
        $this->assertEquals($expectedPageData, $this->parser->getPageData());
    }

}

?>
