<?php

/**
 * jQuery for CMSimple
 *
 * Admin-interface for configuring the plugin
 * via the standard-functions of pluginloader.
 *
 * @author Holger Irmler
 * @link http://cmsimple.holgerirmler.de
 * @version 1.4 - 2013-03-30
 * @build 2013033001
 * @package jQuery
 * */
//initvar('jquery');
//if ($jquery) {
if (isset($_GET['jquery'])) {
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
        $o .= "\n" . '<div class="plugintext">';
        $o .= "\n" . '<div class="plugineditcaption">jQuery for CMSimple v. 1.4 - 2013-03-30</div>';
        $o .= '<p>&copy;2011-2013 <a href="http://cmsimple.holgerirmler.de/" target="_blank">http://CMSimple.HolgerIrmler.de</a></p>';
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
        if (@include_once $pth['folder']['plugins'].'hi_updatecheck/updatecheck.php')
            $o .= hi_updateCheck($plugin);
    }
}
?>