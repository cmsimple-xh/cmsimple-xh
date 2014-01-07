<?php

/**
 * Meta-Tags - module admin
 *
 * Admin-interface for configuring the plugin
 * via the standard-functions of pluginloader.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Metatags
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/* utf8-marker = äöüß */

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
initvar('meta_tags');
if ($meta_tags) {
    $admin = isset($_POST['admin'])
        ? $_POST['admin']
        : $admin = isset($_GET['admin']) ? $_GET['admin'] : '';
    $action = isset($_POST['action'])
        ? $_POST['action']
        : $action = isset($_GET['action']) ? $_GET['action'] : '';
    $plugin = basename(dirname(__FILE__), "/");
    $o .= print_plugin_admin('off')
        . plugin_admin_common($action, $admin, $plugin);
    if ($admin == '') {
        $o .= "\n" . '<div class="plugintext"><div class="plugineditcaption">'
            . ucfirst(str_replace('_', ' ', $plugin)) . '</div></div>' . tag('br');
    }
}

?>
