<?php
/**
 * Page-Data - Module page_data_views
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: page_data_views.php 314 2012-10-30 23:43:19Z cmb69 $
 * @link      http://cmsimple-xh.org/
 */


/* utf8-marker = äöüß */


/**
 * Provides an interface for plugins to handle the page_data.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/

 * @access  public
 */
class PL_Page_Data_View
{
    /**
     * The current page.
     *
     * @var int
     */
    var $page;

    /**
     * The page data tabs.
     *
     * @var array
     */
    var $tabs;

    /**
     * Constructs an instance.
     *
     * @param int   $page The index of the page.
     * @param array $tabs The filenames of the views of page data tabs.
     *
     * @return void
     */
    function PL_Page_Data_View($page, $tabs = null)
    {
        $this->page = $page;
        $this -> tabs = $tabs;
    }

    /**
     * Returns the page data tabs.
     *
     * @return string The (X)HTML.
     */
    function pd_forms()
    {
        $view = "\n" . '<div id = "pd_tabs">';

        foreach ($this->tabs as $title => $file) {
            list($function, $dummy) = explode('.', basename($file), 2);
            // TODO: use something more appropriate than an anchor
            $view .= "\n\t" . '<a class="inactive_tab" id="tab_' . $function
                . '" onclick="xh.toggleTab(\'' . $function . '\');"><span>'
                . $title . '</span></a>';
        }

        $view .= "\n</div>\n" . '<div id="pd_views">';

        foreach ($this->tabs as $title => $file) {
            list($function, $dummy) = explode('.', basename($file), 2);
            // TODO: use something more appropriate than an anchor
            $view .= "\n" . '<div id="PLTab_' . $function
                . '" class="inactive_view">'
                . "\n\t" . '<a class="pd_editor_toggle pd_open"'
                . ' onclick="xh.toggleTab(\'' . $function . '\');">&nbsp;</a>';
            if (file_exists($file)) {
                include_once $file;
                $view .= $function($this->page);
            } else {
                // TODO: i18n; or probably better: use $e/e()
                $view .= "Could not find " . $file;
            }
            $view .= "\n" . "</div>\n";
        }
        $view .= "\n" . '</div>';

        return $view;
    }
}

?>
