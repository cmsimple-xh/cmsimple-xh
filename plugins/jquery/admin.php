<?php
/* utf8-marker = äöüß */
/**
 * jQuery for CMSimple
 *
 * Admin-interface for configuring the plugin
 * via the standard-functions of pluginloader.
 *
 * @author Holger Irmler
 * @link http://cmsimple.holgerirmler.de
 * @version 1.3.1 - 2011-09-30
 * @build 2011093001
 * @package jQuery
 **/

initvar('jquery');
if($jquery){
	$admin= isset($_POST['admin']) ? $_POST['admin'] : $admin = isset($_GET['admin']) ? $_GET['admin'] : '';
	$action= isset($_POST['action']) ? $_POST['action'] : $action = isset($_GET['action']) ? $_GET['action'] : '';
	$plugin=basename(dirname(__FILE__),"/");
	$o .= print_plugin_admin('off');
	if($admin<>'plugin_main'){
		$o .= plugin_admin_common($action,$admin,$plugin);
	}
	if ($admin == 'plugin_main') {
		$o .= plugin_admin_common($action, $admin, $plugin);
	}
	if($admin == '') {
	   	$o .= "\n".'<div class="plugintext">';
		$o .= "\n".'<div class="plugineditcaption">jQuery for CMSimple v. 1.3.1 - 2011-09-30</div>';
		$o .= '<p>&copy;2011 <a href="http://cmsimple.holgerirmler.de/" target="_blank">http://CMSimple.HolgerIrmler.de</a></p>';
		
		if (!$lines = @$lines = file($pth['folder']['plugins'].'jquery/lib/jquery/'.$plugin_cf['jquery']['file_core'])){
			e('missing', 'file', $pth['folder']['plugins'].'jquery/lib/jquery/'.$plugin_cf['jquery']['file_core']);
		} else {
			$o .= "\n".$lines[1].tag('br');
		}
		if (!$lines = @file($pth['folder']['plugins'].'jquery/lib/jquery_ui/'.$plugin_cf['jquery']['file_ui'])){
			e('missing', 'file', $pth['folder']['plugins'].'jquery/lib/jquery_ui/'.$plugin_cf['jquery']['file_ui']);
		} else {
			$o .= "\n".$lines[1].tag('br');
		}
		$o .= "\n".'</div>'. tag('br');
	}
}
?>