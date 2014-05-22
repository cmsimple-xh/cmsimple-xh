<?php

/*
 * @version $Id: admin.php 242 2014-05-06 20:20:18Z hi $
 *
 */

/**
 * jQuery for CMSimple
 *
 * Admin-interface for configuring the plugin
 * via the standard-functions of pluginloader.
 *
 * Version:    1.5.2
 * Build:      2014050601
 * Copyright:  Holger Irmler
 * Email:      CMSimple@HolgerIrmler.de
 * Website:    http://CMSimple.HolgerIrmler.de
 * */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
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
        return $versions;
    }

    function jquery_getMigrateVersions() {
        global $pth;
        $temp = glob($pth['folder']['plugins'] . 'jquery/lib/migrate/*.js');
        $versions = array();
        foreach ($temp as $version) {
            $versions[] = basename($version);
        }
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
        $o .= "\n" . '<div>';
        $o .= "\n" . '<h1>jQuery for CMSimple</h1>';
        $o .= "\n" . '<p>Version 1.5.2 - 2014-05-06</p>';
        $o .= "\n" . '<p>&copy;2011-2013 <a href="http://cmsimple.holgerirmler.de/" target="_blank">http://CMSimple.HolgerIrmler.de</a></p>';
        $o .= "\n" . '<p>';
        $o .= "\n" . 'jQuery Version: ';
        $o .= '<script type="text/javascript">
					var migrate = " & Migrate-Plugin";
					if (typeof jQuery.migrateWarnings === \'undefined\') {
						migrate = "";
					}
					document.write(jQuery.fn.jquery + migrate)
			   </script>';
        $o .= "\n" . tag('br');
        $o .= "\n" . 'jQueryUI Version: ';
        $o .= '<script type="text/javascript">document.write(jQuery.ui.version)</script>';
        $o .= "\n" . '</p>';
        $o .= "\n" . '</div>';
    }
}
?>