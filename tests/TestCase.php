<?php

/**
 * Our test case base class.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @param object $scopeObject
     * @return PHPUnit_Extension_MockFunction
     */
    protected function createFunctionMock($name)
    {
        return new UopzFunctionMock($name, $this);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setConstant($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        } else {
            uopz_redefine($name, $value);
        }
    }

    /**
     * Asserts that the XPath $query matches at least one node in $html
     *
     * @param string $query
     * @param string $html
     */
    protected function assertXPath($query, $html)
    {
        $this->assertTrue($this->queryXPath($query, $html)->length > 0);
    }

    /**
     * Assert that the XPath $query doesn't match a node in $html
     *
     * @param string $query
     * @param string $html
     */
    protected function assertNotXPath($query, $html)
    {
        $this->assertTrue($this->queryXPath($query, $html)->length === 0);
    }

    /**
     * Assert that the XPath $query matches $count nodes in $html
     *
     * @param string $query
     * @param int $count
     * @param string $html
     */
    protected function assertXPathCount($query, $count, $html)
    {
        $this->assertEquals($count, $this->queryXPath($query, $html)->length);
    }

    /**
     * Asserts that the nodeValue of one of the nodes that matches the XPath
     * $query on $html contains the string $text
     *
     * @param string $query
     * @param string $text
     * @param string $html
     */
    protected function assertXPathContains($query, $text, $html)
    {
        $nodes = $this->queryXPath($query, $html);
        if ($nodes && $nodes->length > 0) {
            foreach ($nodes as $node) {
                if (strpos($node->nodeValue, $text) !== false) {
                    $this->assertTrue(true);
                    return;
                }
            }
        }
        $this->assertTrue(false);
    }

    /**
     * Asserts that the nodeValue of none of the nodes that matches the XPath
     * $query on $html contains the string $text
     *
     * @param string $query
     * @param string $text
     * @param string $html
     */
    protected function assertNotXPathContains($query, $text, $html)
    {
        $nodes = $this->queryXPath($query, $html);
        if ($nodes && $nodes->length > 0) {
            foreach ($nodes as $node) {
                if (!$text || strpos($node->nodeValue, $text) !== false) {
                    $this->assertTrue(false);
                    return;
                }
            }
        }
        $this->assertTrue(true);
    }

    /**
     * @param string $query
     * @param string $html
     * @return DOMNodeList
     */
    private function queryXPath($query, $html)
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument;
        $doc->loadHTML($html);
        $xpath = new \DOMXPath($doc);
        return $xpath->query($query);
    }
}
