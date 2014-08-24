<?php

/**
 * Top-level functionality.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * Top-level functionality.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Peter Harteg <peter@harteg.dk>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.2
 */
class XH_Controller
{
    /**
     * Handles search requests.
     *
     * @return void
     *
     * @access public
     *
     * @global array  The paths of system files and folders.
     * @global array  The localization of the core.
     * @global string The content of the title element.
     * @global string The (X)HTML of the contents area.
     */
    function handleSearch()
    {
        global $pth, $tx, $title, $o;

        if (file_exists($pth['file']['search'])) {
            // For compatibility with modified search functions and search plugins.
            include $pth['file']['search'];
        } else {
            $title = $tx['title']['search'];
            $o .= $this->makeSearch()->render();
        }
    }

    /**
     * Makes and returns a search object.
     *
     * @return XH_Search
     *
     * @access protected
     *
     * @global array  The paths of system files and folders.
     * @global string The search string.
     */
    function makeSearch()
    {
        global $pth, $search;

        include_once $pth['folder']['classes'] . 'Search.php';
        return new XH_Search(stsl($search));
    }

    /**
     * Handles mailform requests.
     *
     * @return void
     *
     * @access public
     *
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string The content of the title element.
     * @global string The (X)HTML of the contents area.
     */
    function handleMailform()
    {
        global $cf, $tx, $title, $o;

        if ($cf['mailform']['email'] != '') {
            $title = $tx['title']['mailform'];
            $o .= "\n" . '<div id="xh_mailform">' . "\n";
            $o .= '<h1>' . $title . '</h1>' . "\n";
            $o .= $this->makeMailform()->process();
            $o .= '</div>' . "\n";
        } else {
            shead(404);
        }
    }

    /**
     * Makes and returns a mailform object.
     *
     * @return XH_Mailform
     *
     * @access protected
     *
     * @global array The paths of system files and folders.
     */
    function makeMailform()
    {
        global $pth;

        include_once $pth['folder']['classes'] . 'Mailform.php';
        return new XH_Mailform();
    }

    /**
     * Handles sitemap requests.
     *
     * @return void
     *
     * @access public
     *
     * @global int    The number of pages.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string The content of the title element.
     * @global string The (X)HTML of the content area.
     */
    function handleSitemap()
    {
        global $cl, $cf, $tx, $title, $o;

        $title = $tx['title']['sitemap'];
        $pages = array();
        $o .= '<h1>' . $title . '</h1>' . "\n";
        for ($i = 0; $i < $cl; $i++) {
            if (!hide($i) || $cf['show_hidden']['pages_sitemap'] == 'true') {
                $pages[] = $i;
            }
        }
        $o .= li($pages, 'sitemaplevel');
    }

    /**
     * Handles password forgotten requests.
     *
     * @return void
     *
     * @access public
     */
    function handlePasswordForgotten()
    {
        $this->makePasswordForgotten()->dispatch();
    }

    /**
     * Makes and returns a password forgotten object.
     *
     * @return XH_PasswordForgotten
     *
     * @access protected
     *
     * @global array The paths of system files and folders.
     */
    function makePasswordForgotten()
    {
        global $pth;

        include_once $pth['folder']['classes'] . 'PasswordForgotten.php';
        return new XH_PasswordForgotten();
    }
}

?>
