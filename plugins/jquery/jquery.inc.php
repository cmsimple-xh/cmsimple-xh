<?php

/**
 * jQuery for CMSimple
 *
 * Include-file for use in CMSimple-Plugins
 * to enable jQuery, jQueryUI 
 * and other jQuery-based plugins
 *
 * Version:    1.6.7
 * Build:      2024080501
 * Copyright:  Holger Irmler
 * Email:      CMSimple@HolgerIrmler.de
 * Website:    http://CMSimple.HolgerIrmler.de
 * Copyright:  CMSimple_XH developers
 * Website:    https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team
 * */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

//be sure that all globals are accessible when called from another function
global $hjs, $plugin_cf, $pth;

function include_jQuery($path = '') {
    global $pth, $plugin_cf, $hjs;

    if (!defined('JQUERY')) {
        if ($path == '') {
            $path = $pth['folder']['plugins'] . 'jquery/lib/jquery/' . $plugin_cf['jquery']['version_core'] . '/jquery.min.js';
            if (!is_file($path)) {
                e('missing', 'file', $path);
                return;
            }
        }
        $js = '<script src="' . $path . '"></script>';
        if ($plugin_cf['jquery']['load_migrate'] == 'true') {
            $migrate = $pth['folder']['plugins'] . 'jquery/lib/migrate/' . $plugin_cf['jquery']['version_migrate'];
            if (is_file($migrate)) {
                $js .= "\n" . '<script src="' . $migrate . '"></script>';
            } else {
                e('missing', 'file', $migrate);
                return;
            }
        }
        $hjs = $js . "\n" . $hjs;
        define('JQUERY', TRUE);
    }
}

function include_jQueryUI($path = '') {
    global $pth, $plugin_cf, $hjs;

    if (!defined('JQUERY_UI')) {
        if ($path == '') {
            $path = $pth['folder']['plugins'] . 'jquery/lib/jquery_ui/' . $plugin_cf['jquery']['version_ui'] . '/jquery-ui.min.js';
            if (!is_file($path)) {
                e('missing', 'file', $path);
                return;
            }
        }
        $hjs .= '<script src="' . $path . '"></script>';
        define('JQUERY_UI', TRUE);

        if (file_exists($pth['folder']['template'] . 'jquery_ui/jquery_ui.css')) {
            //load a complete custom ui-theme
            $hjs .= "\n"
                  . '<link rel="stylesheet" type="text/css" media="screen" href="'
                  . $pth['folder']['template']
                  . 'jquery_ui/jquery_ui.css">';
        } else {
            //load the default theme
            $hjs .= "\n"
                  . '<link rel="stylesheet" type="text/css" media="screen" href="'
                  . $pth['folder']['plugins']
                  . 'jquery/lib/jquery_ui/'
                  . $plugin_cf['jquery']['version_ui']
                  . '/css/jquery-ui.min.css">';
            //include a custom css-file to overwrite single selectors
            if (file_exists($pth['folder']['template'] . 'jquery_ui/stylesheet.css')) {
                $hjs .= "\n"
                      . '<link rel="stylesheet" type="text/css" media="screen" href="'
                      . $pth['folder']['template']
                      . 'jquery_ui/stylesheet.css">';
            }
        }
        $hjs .= "\n";
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
                $hjs .= "\n" . '<script src="' . $path . '"></script>';
                $jQueryPlugins[] .= $name;
            }
        }
    }
}