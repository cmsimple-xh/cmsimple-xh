<?php

/**
 * Handling of the menus.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

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
class Li
{
    /**
     * The relevant page indexes.
     *
     * @var array
     */
    protected $ta;

    /**
     * The menu level to start with or the type of menu.
     *
     * @var mixed
     */
    protected $st;

    /**
     * Whether the current menu item is not representing the requested page.
     *
     * @var bool
     */
    protected $tf;

    /**
     * The "default" menu level.
     *
     * @var int
     */
    protected $b;

    /**
     * The array of flags, signalling whether a certain menu level is open.
     *
     * @var array
     */
    protected $lf;

    /**
     * Renders a menu structure of certain pages.
     *
     * @param array $ta The indexes of the pages.
     * @param mixed $st The menu level to start with or the type of menu.
     *
     * @return string HTML
     *
     * @global int The index of the current page.
     */
    public function render(array $ta, $st)
    {
        global $s;

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
        $this->b = 0;
        if ($this->st > 0) {
            $this->b = $this->st - 1;
            $this->st = 'menulevel';
        }
        $this->lf = array();
        for ($i = 0; $i < $tl; $i++) {
            $this->tf = ($s != $this->ta[$i]);
            if ($this->st == 'menulevel' || $this->st == 'sitemaplevel') {
                $t .= $this->renderULStartTags($i);
            }
            $t .= '<li class="' . $this->getClassName($i) . '">';
            $t .= $this->renderMenuItem($i);
            if ($this->st == 'menulevel' || $this->st == 'sitemaplevel') {
                if ($this->getMenuLevel($i + 1) > $this->getMenuLevel($i)) {
                    $this->lf[$this->getMenuLevel($i)] = true;
                } else {
                    $t .= '</li>' . "\n";
                    $this->lf[$this->getMenuLevel($i)] = false;
                }
                $t .= $this->renderEndTags($i);
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
     * Renders the ul start tags.
     *
     * @param int $i The index of the current item.
     *
     * @return string HTML
     */
    protected function renderULStartTags($i)
    {
        $lines = array();
        for ($k = $this->getMenuLevel($i - 1); $k < $this->getMenuLevel($i); $k++) {
            $lines[] = "\n" . '<ul class="' . $this->st . ($k + 1) . '">' . "\n";
        }
        return implode('<li>' . "\n", $lines);
    }

    /**
     * Renders the ul and li end tags.
     *
     * @param int $i The index of the current item.
     *
     * @return string HTML
     */
    protected function renderEndTags($i)
    {
        $html = '';
        for ($k = $this->getMenuLevel($i); $k > $this->getMenuLevel($i + 1); $k--) {
            $html .= '</ul>' . "\n";
            if (isset($this->lf[$k - 1]) && $this->lf[$k - 1]) {
                $html .= '</li>' . "\n";
                $this->lf[$k - 1] = false;
            }
        }
        return $html;
    }

    /**
     * Returns the menu level of a menu item.
     *
     * @param int $i The index of the current item.
     *
     * @return int
     *
     * @global array  The menu levels of the pages.
     */
    protected function getMenuLevel($i)
    {
        global $l;

        return isset($this->ta[$i]) ? $l[$this->ta[$i]] : $this->b;
    }

    /**
     * Returns the class name of the current item.
     *
     * @param int $i The index of the current item.
     *
     * @return string
     *
     * @global array  The configuration of the core.
     */
    protected function getClassName($i)
    {
        $className = '';
        if ($this->isSelected($i)) {
            $className .= 's';
        }
        $className .= 'doc';
        if ($this->hasChildren($i)) {
            $className .= 's';
        }
        return $className;
    }

    /**
     * Returns whether the current menu item is selected.
     *
     * @param int $i The index of the current item.
     *
     * @return bool
     */
    protected function isSelected($i)
    {
        global $cf;

        return !$this->tf
            || $cf['menu']['sdoc'] == "parent"
            && $this->isAnchestorOfSelectedPage($i);
    }

    /**
     * Returns whether the current item is an anchestor of the selected page.
     *
     * @param int $i The index of the current item.
     *
     * @return bool
     *
     * @global int    The index of the current page.
     * @global array  The URLs of the pages.
     * @global array  The menu levels of the pages.
     * @global array  The configuration of the core.
     */
    protected function isAnchestorOfSelectedPage($i)
    {
        global $s, $u, $l, $cf;

        return $s > -1 && $l[$this->ta[$i]] < $l[$s]
            && substr($u[$s], 0, 1 + strlen($u[$this->ta[$i]]))
            == $u[$this->ta[$i]] . $cf['uri']['seperator'];
    }

    /**
     * Returns whether the current item has children.
     *
     * @param int $i The index of the current item.
     *
     * @return bool
     *
     * @global int    The number of pages.
     * @global array  The menu levels of the pages.
     * @global array  The configuration of the core.
     */
    protected function hasChildren($i)
    {
        global $cl, $l, $cf;

        for ($j = $this->ta[$i] + 1; $j < $cl; $j++) {
            if (!hide($j)
                && $l[$j] - $l[$this->ta[$i]] < 2 + $cf['menu']['levelcatch']
            ) {
                if ($l[$j] > $l[$this->ta[$i]]) {
                    return true;
                }
                break;
            }
        }
        return false;
    }

    /**
     * Renders a menu item.
     *
     * @param int $i The index of the current item.
     *
     * @return string HTML
     *
     * @global array  The headings of the pages.
     */
    protected function renderMenuItem($i)
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
     * @return string HTML
     */
    protected function renderAnchorStartTag($i)
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
     */
    protected function shallOpenInNewWindow($i)
    {
        global $edit, $pd_router;

        $pageData = $pd_router->find_page($this->ta[$i]);
        return !(XH_ADM && $edit) && $pageData['use_header_location'] === '2';
    }
}
