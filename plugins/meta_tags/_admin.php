<?php
/* utf8-marker = äöüß */
/**
 * Meta-Tags - module admin
 *
 * Admin-interface for configuring the plugin
 * via the standard-functions of pluginloader.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @version 1.0.03
 * @package pluginloader
 * @subpackage meta_tags
 */
/**
 * Check if plugin was called. If so, let the 
 * Loader create and handle the admin-menu 
 */
initvar('meta_tags');
if($meta_tags){
	$admin= isset($_POST['admin']) ? $_POST['admin'] : $admin = isset($_GET['admin']) ? $_GET['admin'] : '';
	$action= isset($_POST['action']) ? $_POST['action'] : $action = isset($_GET['action']) ? $_GET['action'] : '';
	$plugin=basename(dirname(__FILE__),"/");
	$o .= print_plugin_admin('off');
	if($admin<>'plugin_main'){
		$o .= plugin_admin_common($action,$admin,$plugin);
	}
	if ($admin == 'plugin_main') {
		$acturl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?&" . $plugin . "&admin=plugin_main&action=plugin_text";
		$o .= plugin_admin_common($action, $admin, $plugin);
	}
	if($admin == '') {
	   	$o .= "\n".'<div class="plugintext"><div class="plugineditcaption">'.ucfirst(str_replace('_',' ',$plugin)).'</div></div>'. tag('br');
	}
}
?>