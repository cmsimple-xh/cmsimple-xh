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
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
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
 * @global int    The index of the current page.
 * @global string The script name.
 * @global array  The content of the pages.
 *
 * @since 1.6
 *
 * @todo use CMSIMPLE_URL for relocation
 */
function Pageparams_handleRelocation($index, $data)
{
    global $s, $sn, $c;

    $location = $data['header_location'];
    if ($data['use_header_location'] == '1' && trim($location) !== '' ) {
        $components = parse_url($location);
        if (!$components || !isset($components['scheme'])) {
            $location = 'http://' . $_SERVER['HTTP_HOST'] . $sn . $location;
        }
        if ($index == $s) {
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
        // GMT time and daylight saving time correction
        $timezone = (intval($plugin_tx['page_params']['time_zone'])
            + intval(date("I"))) * 60 * 60;
        $current = strtotime(gmstrftime('%Y-%m-%d %H:%M')) + $timezone;
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
    $pth['folder']['plugins'].'page_params/page_params_view.php'
);

/*
 * Set template, overwrite default.
 */
if (isset($pd_current['template'])
    && $pd_current['template'] !== $cf['site']['template']
    && trim($pd_current['template']) !== ''
    && is_dir($pth['folder']['templates'] . $pd_current['template'])
) {
    $cf['site']['template'] = $pd_current['template'];
    $temp = $pth['folder']['templates'] . $cf['site']['template'] . '/';
    $pth['folder']['template'] = $temp;
    $pth['file']['template'] = $temp . 'template.htm';
    $pth['file']['stylesheet'] = $temp . 'stylesheet.css';
    $pth['folder']['menubuttons'] = $temp . 'menu/';
    $pth['folder']['templateimages'] = $temp . 'images/';
}

/*
 * Overwrite defaults by page-parameters but only if not in edit-mode.
 */
if (!$edit && $pd_current) {
    if ($pd_current['show_heading'] == '1') {
        $temp = '/(<h[1-' . $cf['menu']['levels'] . '].*>).+(<\/h[1-'
            . $cf['menu']['levels'] . ']>)/isU';
        if (trim($pd_current['heading']) == '') {
            $c[$pd_s] = preg_replace($temp, '', $c[$pd_s]);
        } else {
            $c[$pd_s] = preg_replace(
                $temp, '\\1' . $pd_current['heading'] . '\\2', $c[$pd_s]
            );
        }
    }
    if ($pd_current['show_last_edit'] == '1'
        && $pd_current['last_edit'] !== ''
    ) {
        $c[$pd_s] .= '<div id = "pp_last_update">'
            . $plugin_tx['page_params']['last_edit'] .  ' '
            . date($tx['lastupdate']['dateformat'], $pd_current['last_edit'])
            . '</div>';
    }
}

/*
 * Add a #CMSimple hide# if page needs to be viewed eg. in Template as a newsbox
 * (page-parameter 'linked_to_menu'=0). If page is unpublished ('published'=0)
 * content of this page will be overwritten with #CMSimple hide#.
 */
if (!$adm || ($adm && !$edit)) {
    $temp = $pd_router->find_all();
    foreach ($temp as $i => $j) {
        Pageparams_handleRelocation($i, $j);
        if ($j['linked_to_menu'] == '0') {
            $c[$i] = '#CMSimple hide#' . $c[$i];
        } elseif (!Pageparams_isPublished($j)) {
            $c[$i] = '#CMSimple hide#';
        }
    }
}

?>
