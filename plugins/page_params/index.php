<?php

/**
 * Page-Parameters - main index.php
 *
 * Stores page-parameters (heading, template) and let the user take control of
 * visibility of the page.
 * index.php is called by pluginloader and manipulates the respective CMSimple-data.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Pageparams
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    Jerry Jakobsfeld <mail@simplesolutions.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/*
 * Check if PLUGINLOADER is calling and die if not
 */
if (!defined('PLUGINLOADER_VERSION')) {
    die(
        'Plugin '. basename(dirname(__FILE__))
        . ' requires a newer version of the Pluginloader. No direct access.'
    );
}

/**
 * Handles page relocation via the page data.
 *
 * @param int   $index A page index.
 * @param array $data  A page data array.
 *
 * @return void
 *
 * @global int    The preliminary index of the current page.
 * @global string The script name.
 * @global array  The content of the pages.
 *
 * @since 1.6
 */
function Pageparams_handleRelocation($index, $data)
{
    global $pd_s, $sn, $c;

    $location = $data['header_location'];
    if ((int) $data['use_header_location'] > 0 && trim($location) !== '' ) {
        $components = parse_url($location);
        if (!$components || !isset($components['scheme'])) {
            $location = CMSIMPLE_URL . $location;
        }
        if ($index == $pd_s) {
            $c[$index] = '#CMSimple header("Location:'. $location .'"); exit; #';
        }
    }
}

/**
 * Returns whether the page is published.
 *
 * @param array $pd_page The page data of a page.
 *
 * @return bool
 *
 * @global array The localization of the plugins.
 *
 * @author Jerry Jakobsfeld <mail@simplesolutions.dk>
 *
 * @since 1.6
 */
function Pageparams_isPublished($pd_page)
{
    global $plugin_tx;

    if ($pd_page['published'] == '0') {
        return false;
    }
    $publication_date = isset($pd_page['publication_date'])
        ? trim($pd_page['publication_date'])
        : '';
    $expires = isset($pd_page['expires']) ? trim($pd_page['expires']) : '';
    if ($expires != '' || $publication_date != '') {
        $current = time();
        $maxInt = defined('PHP_INT_MAX') ? PHP_INT_MAX : 2147483647;
        $int_publication_date = ($publication_date != '')
            ? strtotime($publication_date) : 0;
        $int_expiration_date = ($expires != '')
            ? strtotime($expires) : $maxInt;
        if ($current <= $int_publication_date
            || $current >= $int_expiration_date
        ) {
            return false;
        }
    }
    return true;
}

/**
 * Switches the template if a page specific is defined. Page specific templates
 * of super pages are inherited if not overridden.
 *
 * @param int $n Index of the current page.
 *
 * @return void
 *
 * @global array  The paths of system files and folders.
 * @global array  The configuration of the core.
 * @global object The page data router.
 *
 * @since 1.6
 */
function Pageparams_switchTemplate($n)
{
    global $pth, $cf, $pd_router;

    include_once $pth['folder']['classes'] . 'Pages.php';
    $pages = new XH_Pages();
    while (true) {
        $data = $pd_router->find_page($n);
        if (isset($data['template']) && trim($data['template']) != ''
            && is_dir($pth['folder']['templates'] . $data['template'])
        ) {
            break;
        }
        $n = $pages->parent($n);
        if (!isset($n)) {
            break;
        }
    }
    if (isset($n) && $data['template'] != $cf['site']['template']) {
        $cf['site']['template'] = $data['template'];
        $dir = $pth['folder']['templates'] . $cf['site']['template'] . '/';
        $pth['folder']['template'] = $dir;
        $pth['file']['template'] = $dir . 'template.htm';
        $pth['file']['stylesheet'] = $dir . 'stylesheet.css';
        $pth['folder']['menubuttons'] = $dir . 'menu/';
        $pth['folder']['templateimages'] = $dir . 'images/';
    }
}

/*
 * Add used interests to router.
 */
$pd_router->add_interest('heading');
$pd_router->add_interest('show_heading');
$pd_router->add_interest('template');
$pd_router->add_interest('published');
$pd_router->add_interest('publication_date');
$pd_router->add_interest('expires');
$pd_router->add_interest('show_last_edit');
$pd_router->add_interest('linked_to_menu');
$pd_router->add_interest('header_location');
$pd_router->add_interest('use_header_location');

/*
 * Add a tab for admin-menu.
 */
$pd_router->add_tab(
    $plugin_tx['page_params']['tab'],
    $pth['folder']['plugins'] . 'page_params/Pageparams_view.php'
);

/*
 * Switche the template if a page specific is defined.
 */
Pageparams_switchTemplate($pd_s);

/*
 * Override defaults by page-parameters but only if not in edit-mode.
 */
if (!$edit && $pd_current) {
    if ($pd_current['show_heading'] == '1') {
        $temp = '/(<h[1-' . $cf['menu']['levels'] . '].*>).+(<\/h[1-'
            . $cf['menu']['levels'] . ']>)/isU';
        if (trim($pd_current['heading']) == '') {
            $c[$pd_s] = preg_replace($temp, '', $c[$pd_s]);
        } else {
            $c[$pd_s] = preg_replace(
                $temp, '${1}' . addcslashes($pd_current['heading'], '$\\') . '$2',
                $c[$pd_s]
            );
        }
    }
    if ($pd_current['show_last_edit'] > 0
        && $pd_current['last_edit'] !== ''
    ) {
        $temp = '<div id = "pp_last_update">'
            . $plugin_tx['page_params']['last_edit'] .  ' '
            . '<time datetime="' . date('c', $pd_current['last_edit']) . '">'
            . XH_formatDate($pd_current['last_edit'])
            . '</time></div>';
        if ($pd_current['show_last_edit'] == 1) {
            $c[$pd_s] .= $temp;
        } else {
            $c[$pd_s] = $temp . $c[$pd_s];
        }
    }
}

/*
 * Add a #CMSimple hide# if page needs to be viewed eg. in Template as a newsbox
 * (page-parameter 'linked_to_menu'=0). If page is unpublished ('published'=0)
 * content of this page will be overwritten with #CMSimple hide#; in case it's
 * the currently requested page, a CMSimple script to show a 404 page is added.
 */
if (!(XH_ADM && $edit)) {
    $temp = $pd_router->find_all();
    foreach ($temp as $i => $j) {
        Pageparams_handleRelocation($i, $j);
        // unpublishing superseedes hiding:
        if (!Pageparams_isPublished($j)) {
            $c[$i] = '#CMSimple hide#';
            if ($_XH_firstPublishedPage == $i) {
                $_XH_firstPublishedPage = ($i < count($temp) - 1 ? $i + 1 : -1);
            }
            if ($s == $i) {
                $s = -1;
            }
            if ($pd_s == $i) {
                $pd_s = ($i < count($temp) - 1 ? $i + 1 : -1);
                $c[$i] .= '#CMSimple shead(404);#';
            }
        } elseif ($j['linked_to_menu'] == '0') {
            $c[$i] = '#CMSimple hide#' . $c[$i];
        }
    }
}

?>
