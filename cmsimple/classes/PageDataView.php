<?php
/**
 * The page data view.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

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
class PageDataView
{
    /**
     * The current page data.
     *
     * @var array
     */
    private $page;

    /**
     * The page data tabs.
     *
     * @var array
     */
    private $tabs;

    /**
     * Constructs an instance.
     *
     * @param array $page Data of the page.
     * @param array $tabs The filenames of the views of page data tabs.
     */
    public function __construct(array $page, array $tabs = null)
    {
        $this->page = $page;
        $this->tabs = $tabs;
    }

    /**
     * Returns a single page data tab.
     *
     * @param string $title    Label of the tab.
     * @param string $filename Name of the view file.
     * @param string $cssClass A CSS class.
     *
     * @return string HTML
     */
    public function tab($title, $filename, $cssClass)
    {
        $parts = explode('.', basename($filename), 2);
        $function = $parts[0];
        // TODO: use something more appropriate than an anchor
        return "\n\t" . '<a class="xh_inactive_tab" id="xh_tab_' . $function
            . '"><span class="'
            . $cssClass . '">' . $title . '</span></a>';
    }

    /**
     * Returns the page data tab bar.
     *
     * @return string HTML
     */
    public function tabs()
    {
        $o = "\n" . '<div id="xh_pdtabs">';
        foreach ($this->tabs as $title => $array) {
            list($file, $cssClass) = $array;
            $o .= $this->tab($title, $file, $cssClass);
        }
        $o .= "\n</div>";
        return $o;
    }

    /**
     * Returns a single page data view.
     *
     * @param string $filename Name of the view file.
     *
     * @return string HTML
     *
     * @global array          The paths of system files and folders.
     * @global CSRFProtection The CSRF protector.
     */
    public function view($filename)
    {
        global $pth, $_XH_csrfProtection;

        $parts = explode('.', basename($filename), 2);
        $function = $parts[0];
        // TODO: use something more appropriate than an anchor
        $o = "\n" . '<div id="xh_view_' . $function
            . '" class="xh_inactive_view">'
            . "\n\t" . '<a class="xh_view_toggle">&nbsp;</a>';
        if (file_exists($filename)) {
            include_once $filename;
            $o .= preg_replace(
                '/<(?:input|button)[^>]+name\s*=\s*([\'"])save_page_data\1/',
                $_XH_csrfProtection->tokenInput() . '$0',
                $function($this->page)
            );
        } else {
            // TODO: i18n; or probably better: use $e/e()
            $o .= "Could not find " . $filename;
        }
        $o .= '<div class="xh_view_status">'
            . '<img src="' . $pth['folder']['corestyle']
            . 'ajax-loader-bar.gif" style="display:none" alt="loading">'
            . '<div></div>'
            . '</div>';
        $o .= "\n" . "</div>\n";
        return $o;
    }

    /**
     * Returns the page data views.
     *
     * @return string HTML
     */
    public function views()
    {
        $o = "\n" . '<div id="xh_pdviews">';
        foreach ($this->tabs as $array) {
            $file = $array[0];
            $o .= $this->view($file);
        }
        $o .= "\n" . '</div>';
        return $o;
    }
}
