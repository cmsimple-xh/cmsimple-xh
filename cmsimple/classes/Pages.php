<?php

/**
 * A class for handling of CMSimple pages.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2016 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * Encapsulates access to several page related global variables,
 * and offers some page related utility methods.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_Pages
{
    /**
     * The number of pages.
     *
     * @var int
     *
     * @access protected
     *
     * @see $cl
     */
    var $count;

    /**
     * The headings of the pages.
     *
     * @var array
     *
     * @access protected
     *
     * @see $h
     */
    var $headings;

    /**
     * The URLs of the pages.
     *
     * @var array
     *
     * @access protected
     *
     * @see $u
     */
    var $urls;

    /**
     * The menu levels of the pages.
     *
     * @var array
     *
     * @access protected
     *
     * @see $l
     */
    var $levels;

    /**
     * The contents of the pages.
     *
     * @var array
     *
     * @access protected
     *
     * @see $c
     */
    var $contents;

    /**
     * Constructs an instance.
     *
     * @return void
     *
     * @global array The headings of the pages.
     * @global array The URLs of the pages.
     * @global array The menu levels of the pages.
     * @global array The contents of the pages.
     */
    function XH_Pages()
    {
        global $h, $u, $l, $c;

        $this->count = count($c);
        $this->headings = $h;
        $this->urls = $u;
        $this->contents = $c;
        $this->levels = $l;
    }

    /**
     * Returns whether a page is hidden.
     *
     * CAVEAT: this is not realiable during the loading of plugins.
     *
     * @param int $n A page index.
     *
     * @return bool
     */
    function isHidden($n)
    {
        return hide($n);
    }

    /**
     * Returns the number of pages.
     *
     * @return int
     */
    function getCount()
    {
        return $this->count;
    }

    /**
     * Returns the heading of a page.
     *
     * @param int $n A page index.
     *
     * @return string
     */
    function heading($n)
    {
        return $this->headings[$n];
    }

    /**
     * Returns the URL of a page.
     *
     * @param int $n A page index.
     *
     * @return string
     */
    function url($n)
    {
        return $this->urls[$n];
    }

    /**
     * Returns the menu level of a page.
     *
     * @param int $n A page index.
     *
     * @return int
     */
    function level($n)
    {
        return $this->levels[$n];
    }

    /**
     * Returns the content of a page.
     *
     * @param int $n A page index.
     *
     * @return string
     */
    function content($n)
    {
        return $this->contents[$n];
    }

    /**
     * Returns an array of indexes of the toplevel pages.
     *
     * @param bool $ignoreHidden Whether hidden pages should be ignored.
     *
     * @return array
     */
    function toplevels($ignoreHidden = true)
    {
        $result = array();
        for ($i = 0; $i < $this->count; ++$i) {
            if ($this->levels[$i] == 1
                && (!$ignoreHidden || !$this->isHidden($i))
            ) {
                $result[] = $i;
            }
        }
        return $result;
    }

    /**
     * Returns an array of indexes of direct children of a page.
     *
     * @param int  $n            A page index.
     * @param bool $ignoreHidden Whether hidden pages should be ignored.
     *
     * @return array of int
     *
     * @global array The configuration of the core.
     */
    function children($n, $ignoreHidden = true)
    {
        global $cf;

        $result = array();
        $ll = $cf['menu']['levelcatch'];
        for ($i = $n + 1; $i < $this->count; ++$i) {
            if ($ignoreHidden && $this->isHidden($i)) {
                continue;
            }
            if ($this->levels[$i] <= $this->levels[$n]) {
                break;
            }
            if ($this->levels[$i] <= $ll) {
                $result[] = $i;
                $ll = $this->levels[$i];
            }
        }
        return $result;
    }

    /**
     * Returns the index of the parent page of a page.
     * Returns <var>null</var>, if <var>$n</var> is a toplevel page.
     *
     * @param int  $n            A page index.
     * @param bool $ignoreHidden Whether hidden pages should be ignored.
     *
     * @return int
     */
    function parent($n, $ignoreHidden = true)
    {
        for ($i = $n - 1; $i >= 0; --$i) {
            if ($this->levels[$i] < $this->levels[$n]
                && (!$ignoreHidden || !$this->isHidden($i))
            ) {
                return $i;
            }
        }
        return null;
    }

    /**
     * Returns the page indexes of all ancestors of a certain page.
     *
     * The order of the result is unspecified.
     *
     * @param int  $pageIndex    A page index.
     * @param bool $ignoreHidden Whether hidden pages should be ignored.
     *
     * @return array
     */
    function getAncestorsOf($pageIndex, $ignoreHidden = true)
    {
        $result = array();
        while (true) {
            $parent = $this->parent($pageIndex, $ignoreHidden);
            if ($parent === null) {
                break;
            }
            $result[] = $parent;
            $pageIndex = $parent;
        }
        return $result;
    }

    /**
     * Returns the index of the first page with the heading $heading.
     *
     * @param string $heading The heading of the page.
     *
     * @return int The index of the page, or -1 if not found.
     */
    function pageWithHeading($heading)
    {
        for ($i = 0; $i < $this->count; $i++) {
            if ($this->headings[$i] == $heading) {
                return $i;
            }
        }
        return -1;
    }

    /**
     * Returns an array of pairs of heading/link of all pages. Can be used
     * to build the internal link list for tinyMCE and CKEditor as well as
     * respective selectboxes for other plugins.
     *
     * @param string $prefix       A prefix for every heading.
     * @param bool   $ignoreHidden Whether hidden pages shall be ignored.
     *
     * @return array
     */
    function linkList($prefix = '', $ignoreHidden = true)
    {
        $result = array();
        for ($i = 0; $i < $this->count; $i++) {
            if (!$ignoreHidden || !$this->isHidden($i)) {
                $indent = str_repeat("\xC2\xA0", 4 * ($this->level($i) - 1));
                $heading = $prefix . $indent . $this->heading($i);
                $result[] = array($heading, $this->url($i));
            }
        }
        return $result;
    }
}

?>
