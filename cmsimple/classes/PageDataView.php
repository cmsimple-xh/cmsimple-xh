<?php
/**
 * The page data view.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * Provides an interface for plugins to handle the page_data.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Martin Damken <kontakt@zeichenkombinat.de>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class XH_PageDataView
{
    /**
     * The current page data.
     *
     * @var array
     *
     * @access protected
     */
    var $page;

    /**
     * The page data tabs.
     *
     * @var array
     *
     * @access protected
     */
    var $tabs;

    /**
     * Constructs an instance.
     *
     * @param array $page Data of the page.
     * @param array $tabs The filenames of the views of page data tabs.
     *
     * @return void
     *
     * @access public
     */
    function XH_PageDataView($page, $tabs = null)
    {
        $this->page = $page;
        $this->tabs = $tabs;
    }

    /**
     * Returns a single page data tab.
     *
     * @param string $title    Label of the tab.
     * @param string $filename Name of the view file.
     *
     * @return string (X)HTML.
     *
     * @access protected
     */
    function tab($title, $filename)
    {
        list($function, $dummy) = explode('.', basename($filename), 2);
        // TODO: use something more appropriate than an anchor
        return "\n\t" . '<a class="xh_inactive_tab" id="xh_tab_' . $function
            . '" onclick="XH.toggleTab(\'' . $function . '\');"><span>'
            . $title . '</span></a>';
    }

    /**
     * Returns the page data tab bar.
     *
     * @return string (X)HTML.
     *
     * @access public
     */
    function tabs()
    {
        $o = "\n" . '<div id="xh_pdtabs">';
        foreach ($this->tabs as $title => $file) {
            $o .= $this->tab($title, $file);
        }
        $o .= "\n</div>";
        return $o;
    }

    /**
     * Returns a single page data view.
     *
     * @param string $filename Name of the view file.
     *
     * @return string (X)HTML.
     *
     * @global array             The paths of system files and folders.
     * @global XH_CSRFProtection The CSRF protector.
     *
     * @access protected
     */
    function view($filename)
    {
        global $pth, $_XH_csrfProtection;

        list($function, $dummy) = explode('.', basename($filename), 2);
        // TODO: use something more appropriate than an anchor
        $o = "\n" . '<div id="xh_view_' . $function
            . '" class="xh_inactive_view">'
            . "\n\t" . '<a class="xh_view_toggle"'
            . ' onclick="XH.toggleTab(\'' . $function . '\');">&nbsp;</a>';
        if (file_exists($filename)) {
            include_once $filename;
            $o .= preg_replace(
                '/<(?:input|button)[^>]+name="save_page_data"/',
                $_XH_csrfProtection->tokenInput() . '$0',
                $function($this->page)
            );
        } else {
            // TODO: i18n; or probably better: use $e/e()
            $o .= "Could not find " . $filename;
        }
        $o .= '<div class="xh_view_status">'
            . tag(
                'img src="' . $pth['folder']['corestyle']
                . 'ajax-loader-bar.gif" style="display:none" alt="loading"'
            )
            . '<div></div>'
            . '</div>';
        $o .= "\n" . "</div>\n";
        return $o;
    }

    /**
     * Returns the page data views.
     *
     * @return string (X)HTML.
     *
     * @access public
     */
    function views()
    {
        $o = "\n" . '<div id="xh_pdviews">';
        foreach ($this->tabs as $title => $file) {
            $o .= $this->view($file);
        }
        $o .= "\n" . '</div>';
        return $o;
    }
}

?>
