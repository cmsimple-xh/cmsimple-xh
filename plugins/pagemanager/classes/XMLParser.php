<?php

/**
 * XMLParser of Pagemanager_XH
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: XMLParser.php 179 2014-01-28 13:24:08Z cmb $
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

/**
 * The XML parser class.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class Pagemanager_XMLParser
{
    /**
     * The original contents array.
     *
     * @var array
     *
     * @access protected
     */
    var $contents;

    /**
     * The new contents array.
     *
     * @var array
     *
     * @access protected
     */
    var $newContents;

    /**
     * The new page data array.
     *
     * @var array
     *
     * @access protected
     */
    var $pageData;

    /**
     * The maximum nesting level.
     *
     * @var int
     *
     * @access protected
     */
    var $levels;

    /**
     * The current nesting level.
     *
     * @var int
     *
     * @access protected
     */
    var $level;

    /**
     * The current page id (number?).
     *
     * @var int
     *
     * @access protected
     */
    var $id;

    /**
     * The current page heading.
     *
     * @var string
     *
     * @access protected
     */
    var $title;

    /**
     * The name of the page data attribute.
     *
     * @var string
     *
     * @access protected
     */
    var $pdattrName;

    /**
     * The current page data attribute.
     *
     * @var bool
     *
     * @access protected
     */
    var $pdattr;

    /**
     * Whether the current page may be renamed.
     *
     * @var bool
     *
     * @access protected
     */
    var $mayRename;

    /**
     * Initializes a newly created object.
     *
     * @param array  $contents   Page contents.
     * @param int    $levels     Maximum page level.
     * @param string $pdattrName Name of a page data attribute.
     */
    function Pagemanager_XMLParser($contents, $levels, $pdattrName)
    {
        $this->contents = $contents;
        $this->levels = $levels;
        $this->pdattrName = $pdattrName;
    }

    /**
     * Parses the given <var>$xml</var>.
     *
     * @param string $xml XML.
     *
     * @return void
     */
    function parse($xml)
    {
        $parser = xml_parser_create('UTF-8');
        xml_set_element_handler(
            $parser, array($this, 'startElementHandler'),
            array($this, 'endElementHandler')
        );
        xml_set_character_data_handler($parser, array($this, 'cDataHandler'));
        $this->level = 0;
        $this->newContents = array();
        $this->pageData = array();
        xml_parse($parser, $xml, true);
    }

    /**
     * Returns the new contents array.
     *
     * @return array
     */
    function getContents()
    {
        return $this->newContents;
    }

    /**
     * Returns the new page data array.
     *
     * @return array
     */
    function getPageData()
    {
        return $this->pageData;
    }

    /**
     * Handles the start elements of the XML.
     *
     * @param resource $parser  An XML parser.
     * @param string   $name    Name of the current element.
     * @param array    $attribs Attributes of the current element.
     *
     * @return void
     *
     * @access protected
     */
    function startElementHandler($parser, $name, $attribs)
    {
        if ($name === 'ITEM') {
            $this->level++;
            $pattern = '/(copy_)?pagemanager-([0-9]*)/';
            $this->id = $attribs['ID'] === ''
                ? null
                : (int) preg_replace($pattern, '$2', $attribs['ID']);
            $this->title = htmlspecialchars(
                $attribs['TITLE'], ENT_NOQUOTES, 'UTF-8'
            );
            $this->pdattr = isset($attribs['DATA-PDATTR'])
                ? $attribs['DATA-PDATTR'] : null;
            $this->mayRename = $attribs['CLASS'] == '';
        }
    }

    /**
     * Handles the end elements of the XML.
     *
     * @param resource $parser An XML parser.
     * @param string   $name   Name of the current element.
     *
     * @return void
     *
     * @access protected
     */
    function endElementHandler($parser, $name)
    {
        if ($name === 'ITEM') {
            $this->level--;
        }
    }

    /**
     * Handles the character data of the XML.
     *
     * @param resource $parser An XML parser.
     * @param string   $data   The current character data.
     *
     * @return void
     *
     * @global object The page data router.
     *
     * @access protected
     */
    function cDataHandler($parser, $data)
    {
        global $pd_router;

        if (trim($data) === '') {
            return;
        }
        $data = htmlspecialchars($data, ENT_NOQUOTES, 'UTF-8');
        if (isset($this->contents[$this->id])) {
            $content = $this->contents[$this->id];
            if ($this->mayRename) {
                $pattern = '/<h[1-' . $this->levels . ']([^>]*)>'
                    . '((<[^>]*>)*)[^<]*((<[^>]*>)*)'
                    . '<\/h[1-' . $this->levels . ']([^>]*)>/i';
                $replacement = '<h' . $this->level . '$1>${2}'
                    . addcslashes($this->title, '$\\') . '$4'
                    . '</h' . $this->level . '$6>';
                $content = preg_replace($pattern, $replacement, $content, 1);
            }
            $this->newContents[] = $content;
        } else {
            $this->newContents[] = '<h' . $this->level . '>' . $this->title
                . '</h' . $this->level . '>';
        }
        if (isset($this->id)) {
            $pageData = $pd_router->find_page($this->id);
        } else {
            $pageData = $pd_router->new_page();
        }
        if ($this->mayRename) {
            $pageData['url'] = uenc($this->title);
        }
        if ($this->pdattrName !== '') {
            $pageData[$this->pdattrName] = $this->pdattr;
        }
        $this->pageData[] = $pageData;
    }
}

?>
