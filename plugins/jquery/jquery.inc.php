<?php
/* utf8-marker = äöüß */
/**
 * jQuery for CMSimple
 *
 * Include-file for use in CMSimple-Plugins
 * to enable jQuery, jQueryUI 
 * and other jQuery-based plugins
 *
  * @author Holger Irmler
 * @link http://cmsimple.holgerirmler.de
 * @version 1.3.2 - 2012-08-21
 * @build 2012082101
 * @package jQuery
 **/

//error_reporting(E_ALL);

//load plugin-configuration
require($pth['folder']['plugins'].'jquery/config/config.php');

function include_jQuery($path='') {
	global $pth, $plugin_cf, $hjs;
	
	if(!defined('JQUERY')) {
		if($path == '') {
			$path = $pth['folder']['plugins'].'jquery/lib/jquery/'.$plugin_cf['jquery']['file_core'];
			if(!file_exists($path)) {
				e('missing', 'file', $path);
				return;
			}
		}
		//$hjs .= "\n".'<script type="text/javascript" src="'.$path.'"></script>';
		$hjs = '<script type="text/javascript" src="'.$path.'"></script>' . $hjs; 
		define('JQUERY', $plugin_cf['jquery']['version_core']);
	}
}

function include_jQueryUI($path='') {
	global $pth, $plugin_cf, $hjs;
	
	if(!defined('JQUERY_UI')) {
		if($path == '') {
			$path = $pth['folder']['plugins'].'jquery/lib/jquery_ui/'.$plugin_cf['jquery']['file_ui'];
			if(!file_exists($path)) {
				e('missing', 'file', $path);
				return;
			}
		}
		$hjs .= "\n".'<script type="text/javascript" src="'.$path.'"></script>';
		define('JQUERY_UI', $plugin_cf['jquery']['version_ui']);
		
		if(file_exists($pth['folder']['template'].'jquery_ui/jquery_ui.css')) {
			//load a complete custom ui-theme
			$hjs .= "\n".tag('link rel="stylesheet" type="text/css" media="screen" href="'
				 .$pth['folder']['template'].'jquery_ui/jquery_ui.css"');
		} else {
			//load the default theme
			$hjs .= "\n".tag('link rel="stylesheet" type="text/css" media="screen" href="'.$pth['folder']['plugins']
					.'jquery/lib/jquery_ui/css/'.$plugin_cf['jquery']['file_css'].'"');
			//include a custom css-file to overwrite single selectors
			if(file_exists($pth['folder']['template'].'jquery_ui/stylesheet.css')) {
				$hjs .= "\n".tag('link rel="stylesheet" type="text/css" media="screen" href="'
					.$pth['folder']['template'].'jquery_ui/stylesheet.css"');
			}
		}
	}
}

function include_jQueryPlugin($name='', $path='') {
	global $hjs, $jQueryPlugins;
	
	if(!isset($jQueryPlugins)) {
		$jQueryPlugins = array();
	}
	
	if(defined('JQUERY')) {
		if($name != '') {
			if(!file_exists($path)) {
				e('missing', 'file', $path);
				return;
			}
			$name = strtolower($name);
			if (!in_array($name, $jQueryPlugins)) {
				$hjs .= "\n".'<script type="text/javascript" src="'.$path.'"></script>';
				$jQueryPlugins[] .= $name;
			}
		}
	}
}
?>