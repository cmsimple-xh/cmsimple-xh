<?php
/**
 * jQuery for CMSimple
 *
 * Include-file for use in CMSimple-Plugins
 * to enable jQuery, jQueryUI 
 * and other jQuery-based plugins
 *
 * @author Holger Irmler
 * @link http://cmsimple.holgerirmler.de
 * @version 1.4 - 2013-03-30
 * @build 2013032101
 * @package jQuery
 * */

//be sure that all globals are accessible when called from another function
global $hjs, $plugin_cf, $pth;

//load plugin-configuration
require($pth['folder']['plugins'] . 'jquery/config/config.php');

function include_jQuery($path = '') {
    global $pth, $plugin_cf, $hjs;

    if (!defined('JQUERY')) {
        if ($path == '') {
            $path = $pth['folder']['plugins'] . 'jquery/lib/jquery/' . $plugin_cf['jquery']['file_core'];
            if (!is_file($path)) {
                e('missing', 'file', $path);
                return;
            }
        }
		$js = '<script type="text/javascript" src="' . $path . '"></script>';
		$migrate = $pth['folder']['plugins'] . 'jquery/lib/jquery/' . $plugin_cf['jquery']['file_migrate'];
		if (is_file($migrate)) {
			$js .= "\n" . '<script type="text/javascript" src="' . $migrate . '"></script>';
		}
        //$hjs .= "\n".'<script type="text/javascript" src="'.$path.'"></script>';
        $hjs = $js . $hjs;
        define('JQUERY', TRUE);
    }
}

function include_jQueryUI($path = '') {
    global $pth, $plugin_cf, $hjs;

    if (!defined('JQUERY_UI')) {
        if ($path == '') {
            $path = $pth['folder']['plugins'] . 'jquery/lib/jquery_ui/' . $plugin_cf['jquery']['file_ui'];
            if (!is_file($path)) {
                e('missing', 'file', $path);
                return;
            }
        }
        $hjs .= "\n" . '<script type="text/javascript" src="' . $path . '"></script>';
        define('JQUERY_UI', TRUE);

        if (file_exists($pth['folder']['template'] . 'jquery_ui/jquery_ui.css')) {
            //load a complete custom ui-theme
            $hjs .= "\n" . tag('link rel="stylesheet" type="text/css" media="screen" href="'
                            . $pth['folder']['template'] . 'jquery_ui/jquery_ui.css"');
        } else {
            //load the default theme
            $hjs .= "\n" . tag('link rel="stylesheet" type="text/css" media="screen" href="' . $pth['folder']['plugins']
                            . 'jquery/lib/jquery_ui/css/' . $plugin_cf['jquery']['file_css'] . '"');
            //include a custom css-file to overwrite single selectors
            if (file_exists($pth['folder']['template'] . 'jquery_ui/stylesheet.css')) {
                $hjs .= "\n" . tag('link rel="stylesheet" type="text/css" media="screen" href="'
                                . $pth['folder']['template'] . 'jquery_ui/stylesheet.css"')
								. "\n";
            }
        }
    }
}

function include_jQueryPlugin($name = '', $path = '') {
    global $hjs, $jQueryPlugins;

    if (!isset($jQueryPlugins)) {
        $jQueryPlugins = array();
    }

    if (defined('JQUERY')) {
        if ($name != '') {
            if (!file_exists($path)) {
                e('missing', 'file', $path);
                return;
            }
            $name = strtolower($name);
            if (!in_array($name, $jQueryPlugins)) {
                $hjs .= "\n" . '<script type="text/javascript" src="' . $path . '"></script>';
                $jQueryPlugins[] .= $name;
            }
        }
    }
}

?>