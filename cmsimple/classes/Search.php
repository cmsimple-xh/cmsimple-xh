<?php

/**
 * The search function of CMSimple_XH.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

/*
  ======================================
  @CMSIMPLE_XH_VERSION@
  @CMSIMPLE_XH_DATE@
  based on CMSimple version 3.3 - December 31. 2009
  For changelog, downloads and information please see http://www.cmsimple-xh.com
  ======================================
  -- COPYRIGHT INFORMATION START --
  Based on CMSimple version 3.3 - December 31. 2009
  Small - simple - smart
  (c) 1999-2009 Peter Andreas Harteg - peter@harteg.dk

  This file is part of CMSimple_XH
  For licence see notice in /cmsimple/cms.php
  -- COPYRIGHT INFORMATION END --
  ======================================
 */

namespace XH;

/**
 * The search class.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class Search
{
    /**
     * The search String.
     *
     * @var string
     */
    private $searchString;

    /**
     * The search words.
     *
     * @var array
     */
    private $words;

    /**
     * Constructs an instance.
     *
     * @param string $searchString String The search string.
     */
    public function __construct($searchString)
    {
        $this->searchString = $searchString;
    }

    /**
     * Returns the array of search words.
     *
     * @return array
     */
    private function getWords()
    {
        if (!isset($this->words)) {
            $words = explode(' ', $this->searchString);
            $this->words = array();
            foreach ($words as $word) {
                $word = trim($word);
                if ($word != '') {
                    if (class_exists('\Normalizer', false)
                        && method_exists('\Normalizer', 'normalize')
                    ) {
                        $word = \Normalizer::normalize($word);
                    }
                    $this->words[] = $word;
                }
            }
        }
        return $this->words;
    }

    /**
     * Returns an array of page indexes
     * where all words of the search string are contained.
     *
     * @return array
     *
     * @global array The content of the pages.
     * @global array The configuration of the core.
     */
    public function search()
    {
        global $c, $cf;

        $result = array();
        $words = $this->getWords();
        if (empty($words)) {
            return $result;
        }
        foreach ($c as $i => $content) {
            if (!hide($i) || $cf['show_hidden']['pages_search'] == 'true') {
                $found  = true;
                $content = $this->prepareContent($content, $i);
                foreach ($words as $word) {
                    if (utf8_stripos($content, $word) === false) {
                        $found = false;
                        break;
                    }
                }
                if ($found) {
                    $result[] = $i;
                }
            }
        }
        return $result;
    }

    /**
     * Prepares content to be searched.
     *
     * @param string $content   A content.
     * @param string $pageIndex A page index.
     *
     * @return string
     */
    private function prepareContent($content, $pageIndex)
    {
        global $s;

        $vars = array('s', 'o', 'hjs', 'bjs', 'e', 'onload');
        foreach ($vars as $var) {
            $old[$var] = $GLOBALS[$var];
        }
        $s = $pageIndex;
        $content = strip_tags(evaluate_plugincall($content));
        foreach ($vars as $var) {
            $GLOBALS[$var] = $old[$var];
        }
        if (method_exists('\Normalizer', 'normalize')) {
            $content = \Normalizer::normalize($content);
        }
        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns a message how often the search string was found.
     *
     * @param int $count How often the search string was found.
     *
     * @return string HTML
     *
     * @global array The localization of the core.
     */
    private function foundMessage($count)
    {
        global $tx;

        if ($count == 0) {
            $key = 'notfound';
        } elseif ($count == 1) {
            $key = 'found_1';
        } elseif (2 <= $count && $count <= 4) {
            $key = 'found_2-4';
        } else {
            $key = 'found_5';
        }
        $message = sprintf($tx['search'][$key], $this->searchString, $count);
        $message = XH_hsc($message);
        $message = '<p>' . $message . '</p>';
        return $message;
    }

    /**
     * Returns the search results view.
     *
     * @return string HTML
     *
     * @global array  The headings of the pages.
     * @global array  The URLs of the pages.
     * @global string The script name.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global object The page data router.
     */
    public function render()
    {
        global $h, $u, $sn, $cf, $tx, $pd_router;

        $cf['meta']['robots'] = 'noindex, nofollow';
        $o = '<h1>' . $tx['search']['result'] . '</h1>';
        $words = $this->getWords();
        $pages = $this->search();
        $count = count($pages);
        $o .= $this->foundMessage($count) . PHP_EOL;
        if ($count > 0) {
            $o .= '<ul>' . PHP_EOL;
            $words = implode(' ', $words);
            foreach ($pages as $i) {
                $pageData = $pd_router->find_page($i);
                $site = isset($pageData['title']) ? $pageData['title'] : '';
                $title = XH_title($site, $h[$i]);
                $url = $sn . '?' . $u[$i] . '&amp;search=' . urlencode($words);
                $o .= '    <li><a href="' . $url . '">' . $title . '</a>';
                $description = isset($pageData['description'])
                    ? $pageData['description'] : '';
                if ($description != '') {
                    $o .= '<div>' . XH_hsc($description) . '</div>';
                }
                $o .= '</li>' . PHP_EOL;
            }
            $o .= '</ul>' . PHP_EOL;
        }
        return $o;
    }
}
