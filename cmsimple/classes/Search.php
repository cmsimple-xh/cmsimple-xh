<?php

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
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
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
        $this->searchString = trim(preg_replace('/\s+/u', ' ', $searchString));
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
        /*
         * $GLOBALS['xh_searching']
         * This allows plugins to be excluded from the search.
         * Not every plugin returns content relevant for the search (e.g.: Shariff_XH).
         * with:
         * if (isset($GLOBALS['xh_searching']) && $GLOBALS['xh_searching']) return;
         * is integrated before the actual function of the plugin,
         * the plugin is no longer executed during the search.
         */
        $GLOBALS['xh_searching'] = true;
        $content = strip_tags(evaluate_plugincall($content));
        unset($GLOBALS['xh_searching']);
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
     */
    public function render()
    {
        global $h, $cf, $tx, $pd_router;

        $cf['meta']['robots'] = 'noindex, nofollow';
        $o = '<h1>' . $tx['search']['result'] . '</h1>';
        $words = $this->getWords();
        $pages = $this->search();
        $count = count($pages);
        $o .= $this->foundMessage($count) . "\n";
        if ($count > 0) {
            $o .= '<ul class="xh_search_results">' . "\n";
            $words = implode(' ', $words);
            foreach ($pages as $i) {
                $pageData = $pd_router->find_page($i);
                $site = isset($pageData['title']) ? $pageData['title'] : '';
                $title = XH_title($site, $h[$i]);
                $o .= '    <li>' . a($i, '&amp;search=' . urlencode($words)) . $title . '</a>';
                $description = isset($pageData['description'])
                    ? $pageData['description'] : '';
                if ($cf['search']['description'] == 'true'
                && $description != '') {
                    $o .= '<div>' . XH_hsc($description) . '</div>';
                }
                $o .= '</li>' . "\n";
            }
            $o .= '</ul>' . "\n";
        }
        return $o;
    }
}
