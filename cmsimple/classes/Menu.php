<?php

/**
 * Handling of the menus.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * The menu renderer.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Peter Harteg <peter@harteg.dk>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class XH_Li
{
    /**
     * The relevant page indexes.
     *
     * @var array
     *
     * @access protected
     */
    var $ta;

    /**
     * The menu level to start with or the type of menu.
     *
     * @var mixed
     *
     * @access protected
     */
    var $st;

    /**
     * Whether the current menu item is not representing the requested page.
     *
     * @var bool
     *
     * @access protected
     */
    var $tf;

    /**
     * Renders a menu structure of certain pages.
     *
     * @param array $ta The indexes of the pages.
     * @param mixed $st The menu level to start with or the type of menu.
     *
     * @return string (X)HTML.
     *
     * @global int    The index of the current page.
     * @global array  The menu levels of the pages.
     * @global int    The number of pages.
     * @global array  The configuration of the core.
     * @global array  The URLs of the pages.
     *
     * @access public
     */
    function render($ta, $st)
    {
        global $s, $l, $cl, $cf, $u;

        $this->ta = (array) $ta;
        $this->st = $st;
        $tl = count($this->ta);
        if ($tl < 1) {
            return;
        }
        $t = '';
        if ($this->st == 'submenu' || $this->st == 'search') {
            $t .= '<ul class="' . $this->st . '">' . "\n";
        }
        $b = 0;
        if ($this->st > 0) {
            $b = $this->st - 1;
            $this->st = 'menulevel';
        }
        $lf = array();
        for ($i = 0; $i < $tl; $i++) {
            $this->tf = ($s != $this->ta[$i]);
            if ($this->st == 'menulevel' || $this->st == 'sitemaplevel') {
                for ($k = (isset($this->ta[$i - 1]) ? $l[$this->ta[$i - 1]] : $b);
                     $k < $l[$this->ta[$i]];
                     $k++
                ) {
                    $t .= "\n" . '<ul class="' . $this->st . ($k + 1) . '">' . "\n";
                }
            }
            $t .= '<li class="';
            if (!$this->tf) {
                $t .= 's';
            } elseif ($cf['menu']['sdoc'] == "parent" && $s > -1) {
                if ($l[$this->ta[$i]] < $l[$s]) {
                    $hasChildren = substr($u[$s], 0, 1 + strlen($u[$this->ta[$i]]))
                        == $u[$this->ta[$i]] . $cf['uri']['seperator'];
                    if ($hasChildren) {
                        $t .= 's';
                    }
                }
            }
            $t .= 'doc';
            for ($j = $this->ta[$i] + 1; $j < $cl; $j++) {
                if (!hide($j)
                    && $l[$j] - $l[$this->ta[$i]] < 2 + $cf['menu']['levelcatch']
                ) {
                    if ($l[$j] > $l[$this->ta[$i]]) {
                        $t .= 's';
                    }
                    break;
                }
            }
            $t .= '">';
            $t .= $this->renderMenuItem($i);
            if ($this->st == 'menulevel' || $this->st == 'sitemaplevel') {
                $temp = isset($this->ta[$i + 1]) ? $l[$this->ta[$i + 1]] : $b;
                if ($temp > $l[$this->ta[$i]]) {
                    $lf[$l[$this->ta[$i]]] = true;
                } else {
                    $t .= '</li>' . "\n";
                    $lf[$l[$this->ta[$i]]] = false;
                }
                for ($k = $l[$this->ta[$i]];
                    $k > (isset($this->ta[$i + 1]) ? $l[$this->ta[$i + 1]] : $b);
                    $k--
                ) {
                    $t .= '</ul>' . "\n";
                    if (isset($lf[$k - 1])) {
                        if ($lf[$k - 1]) {
                            $t .= '</li>' . "\n";
                            $lf[$k - 1] = false;
                        }
                    }
                }
            } else {
                $t .= '</li>' . "\n";
            }
        }
        if ($this->st == 'submenu' || $this->st == 'search') {
            $t .= '</ul>' . "\n";
        }
        return $t;
    }

    /**
     * Renders a menu item.
     *
     * @param int $i The index of the current item.
     *
     * @return string (X)HTML.
     *
     * @global array  The headings of the pages.
     *
     * @access protected
     */
    function renderMenuItem($i)
    {
        global $h;

        if ($this->tf) {
            $html = $this->renderAnchorStartTag($i);
        } else {
            $html ='<span>';
        }
        $html .= $h[$this->ta[$i]];
        if ($this->tf) {
            $html .= '</a>';
        } else {
            $html .='</span>';
        }
        return $html;
    }

    /**
     * Renders an anchor start tag.
     *
     * @param int $i The index of the current item.
     *
     * @return string (X)HTML.
     *
     * @access protected
     */
    function renderAnchorStartTag($i)
    {
        $x = $this->shallOpenInNewWindow($i) ? '" target="_blank' : '';
        return a($this->ta[$i], $x);
    }

    /**
     * Returns whether a link shall be opened in a new window.
     *
     * @param int $i The index of the current item.
     *
     * @return bool
     *
     * @global array  Whether we are in edit mode.
     * @global object The page data router.
     *
     * @access protected
     */
    function shallOpenInNewWindow($i)
    {
        global $edit, $pd_router;

        $pageData = $pd_router->find_page($this->ta[$i]);
        return !(XH_ADM && $edit) && $pageData['use_header_location'] === '2';
    }
}

?>
