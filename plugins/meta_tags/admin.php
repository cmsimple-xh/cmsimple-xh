<?php

/**
 * Meta-Tags - module admin
 *
 * Admin-interface for configuring the plugin
 * via the standard-functions of pluginloader.
 *
 * @category  CMSimple_XH
 * @package   Metatags
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
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
 * Check if plugin was called.
 * If so, let the Loader create and handle the admin-menu.
 */
if (XH_wantsPluginAdministration('meta_tags')) {
    $o .= print_plugin_admin('off');
    if ($admin == '') {
        $o .= "\n" . '<h1>'
            . ucfirst(str_replace('_', ' ', $plugin)) . '</h1>';
    }
    $o .= plugin_admin_common($action, $admin, $plugin);
}
