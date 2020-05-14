<?php

/**
 * Meta-Tags - main index.php
 *
 * Stores meta-tags (description, keywords, title and robots) per page.
 * index.php is called by pluginloader and returns HTML META ELEMENTS to template.
 *
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

/*
 * Check if PLUGINLOADER is calling and die if not.
 */
if (!defined('PLUGINLOADER')) {
    die(
        'Plugin '. basename(dirname(__FILE__))
        . ' requires a newer version of the Pluginloader. No direct access.'
    );
}

/*
 * Add used interests to router.
 */
$pd_router->add_interest('description');
$pd_router->add_interest('keywords');
$pd_router->add_interest('title');
$pd_router->add_interest('robots');

/*
 * Add a tab for admin-menu.
 */
$pd_router->add_tab(
    $plugin_tx['meta_tags']['tab'],
    $pth['folder']['plugins'] . 'meta_tags/Metatags_view.php'
);

/*
 * Set the meta tags contents.
 */
if (isset($pd_current['title'])) {
    $cf['site']['title'] = $pd_current['title'];
    $cf['title']['format'] = "{SITE}";
}
if (isset($pd_current['description'])) {
    $tx['meta']['description'] = $pd_current['description'];
}
if (isset($pd_current['keywords'])) {
    $tx['meta']['keywords'] = $pd_current['keywords'];
}
if (isset($pd_current['robots'])) {
    $cf['meta']['robots'] = $pd_current['robots'];
}
