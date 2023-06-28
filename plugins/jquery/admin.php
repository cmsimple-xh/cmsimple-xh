<?php

/**
 * jQuery for CMSimple
 *
 * Admin-interface for configuring the plugin
 * via the standard-functions of pluginloader.
 *
 * Version:    1.6.6
 * Build:      2023062101
 * Copyright:  Holger Irmler
 * Email:      CMSimple@HolgerIrmler.de
 * Website:    http://CMSimple.HolgerIrmler.de
 * */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/*
 * Register the plugin menu items.
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(false);
}

if (isset($_GET['jquery'])) {

    //Helper-functions
    function jquery_getCoreVersions() {
        global $pth;
        $versions = array();
        $handle = opendir($pth['folder']['plugins'] . 'jquery/lib/jquery/');
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                $versions[] = $entry;
            }
        }
        closedir($handle);
        sort($versions);
        return $versions;
    }

    function jquery_getUiVersions() {
        global $pth;
        $versions = array();
        $handle = opendir($pth['folder']['plugins'] . 'jquery/lib/jquery_ui/');
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                $versions[] = $entry;
            }
        }
        closedir($handle);
        sort($versions);
        return $versions;
    }

    function jquery_getMigrateVersions() {
        global $pth;
        $temp = glob($pth['folder']['plugins'] . 'jquery/lib/migrate/*.js');
        $versions = array();
        foreach ($temp as $version) {
            $versions[] = basename($version);
        }
        sort($versions);
        return $versions;
    }

    $admin = isset($_POST['admin']) ? $_POST['admin'] : $admin = isset($_GET['admin']) ? $_GET['admin'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : $action = isset($_GET['action']) ? $_GET['action'] : '';
    $plugin = basename(dirname(__FILE__), "/");
    include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
    include_jQuery();
    include_jQueryUI();

    $o .= print_plugin_admin('off');
    if ($admin <> 'plugin_main') {
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
    if ($admin == 'plugin_main') {
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
    if ($admin == '') {
        $o .= PHP_EOL . '<div>';
        $o .= PHP_EOL . '<h1>jQuery for CMSimple</h1>';
        $o .= PHP_EOL . '<p>Version 1.6.6 - 2023-06-21</p>';
        $o .= PHP_EOL . '<p>&copy;2011-2023 <a href="http://cmsimple.holgerirmler.de/" target="_blank">http://CMSimple.HolgerIrmler.de</a></p>';
        $o .= PHP_EOL . '<p>';
        $o .= PHP_EOL . 'jQuery Version: ';
        $o .= '<script>
                    var migrate = " & Migrate-Plugin";
                    if (typeof jQuery.migrateWarnings === \'undefined\') {
                        migrate = "";
                    }
                    document.write(jQuery.fn.jquery + migrate)
               </script>';
        $o .= PHP_EOL . '<br>';
        $o .= PHP_EOL . 'jQueryUI Version: ';
        $o .= '<script>document.write(jQuery.ui.version)</script>';
        $o .= PHP_EOL . '</p>';
        $o .= PHP_EOL . '</div>';
    }
}