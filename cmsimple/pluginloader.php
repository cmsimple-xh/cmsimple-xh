<?php

/* utf8-marker = äöüß */
/**
 * Pluginloader of $CMSIMPLE_XH_VERSION$
 * Handles loading of pluginloader-2.0 and -2.1 compatible plugins.
 *
 * Created after discussion at CMSimpleforum.com with:
 * Martin, mvwd, Till, johnjdoe, Holger and Gert in May 2009.
 * 
 * @author Developer-Team at CMSimpleforum.com
 * @link http://www.cmsimpleforum.com
 * @version $Id: index.php 317 2012-10-31 00:21:59Z cmb69 $
 * @package pluginloader
 *
 * Plugin Loader version 2.0 beta 11 for CMSimple v2.6, v2.7 . . . 3.0, 3.2 .....
 * Modified by Till after discussion in the German CMSimple forum with Holger and 
 * Gert (October 2008)
 * 
 * Edited by © Jan Neugebauer (December, 1st 2006) 
 * http://www.internet-setup.de/cmsimple/
 * 
 * Based on original script by © Michael Svarrer 
 * http://cmsimpleplugins.svarrer.dk
 * 
 * Updated 22. September 2006 by Peter Andreas Harteg and released under the CMSimple 
 * license in the CMSimple distrubution with permission by copyright holders.
 * 
 * For licence see notice in /cmsimple/cms.php and http://www.cmsimple.com/?Licence
 */

/*
 * Deny direct access of Plugin Loader file
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    die('access denied');
}

define('PLUGINLOADER', TRUE);
define('PLUGINLOADER_VERSION', 2.111);


if (!isset($hjs)) {
    $hjs = '';
}


define('XH_FORM_NAMESPACE', 'PL3bbeec384_');


/*
 * If admin is logged, generate fake output to suppress later adjustment of $s.
 */
if ($adm) {
    $o .= ' ';
}


// BOF page_data

require_once($pth['folder']['classes'] . 'page_data_router.php');
require_once($pth['folder']['classes'] . 'page_data_model.php');
require_once($pth['folder']['classes'] . 'page_data_views.php');

/**
 * Check if page-data-file exists, if not: try to
 * create a new one with basic data-fields.
 */
if (!file_exists($pth['file']['pagedata'])) {
    if ($fh = fopen($pth['file']['pagedata'], 'w')) {
        fwrite($fh, '<?php' . "\n" . '$page_data_fields[] = \'url\';' . "\n" . '$page_data_fields[] = \'last_edit\';' . "\n" . '?>');
        chmod($pth['file']['pagedata'], 0666);
        fclose($fh);
    } else {
        e('cntwriteto', 'file', $pth['file']['pagedata']);
    }
}

/**
 * Create an instance of PL_Page_Data_Router
 */
$pd_router = new PL_Page_Data_Router($pth['file']['pagedata'], $h);

if ($adm) {

    /**
     * Check for any changes to handle
     * First: check for changes from texteditor
     */
    if ($function == 'save') {
        /**
         * Collect the headings and pass them over to the router
         */
        $text = preg_replace("/<h[1-" . $cf['menu']['levels'] . "][^>]*>(&nbsp;|&#160;|\xC2\xA0| )?<\/h[1-" . $cf['menu']['levels'] . "]>/is", "", stsl($text));
        preg_match_all('/<h[1-' . $cf['menu']['levels'] . '].*>(.+)<\/h[1-' . $cf['menu']['levels'] . ']>/isU', $text, $matches);
        $pd_router->refresh_from_texteditor($matches[1], $s);
    }

    /**
     * Second: check for changes from MenuManager
     */
    if (isset($menumanager) && $menumanager && $action == 'saverearranged' && (isset($text) ? strlen($text) : 0 ) > 0) {
        $pd_router->refresh_from_menu_manager($text);
    }

    /**
     * Finally check for some changed page infos
     */
    if ($s > -1 && isset($_POST['save_page_data'])) {
        $params = $_POST;
        if (get_magic_quotes_gpc() === 1) {
            array_walk($params, create_function('&$data', '$data=stripslashes($data);'));
        }
        unset($params['save_page_data']);
        $pd_router->update($s, $params);
    }
}
/**
 * Now we are up to date
 * If no page has been selected yet, we
 * are on the start page: Get its index
 */
if ($s == -1 && !$f && $o == '' && $su == '') {
    $pd_s = 0;
} else {
    $pd_s = $s;
}

/**
 * Get the infos about the current page
 */
$pd_current = $pd_router->find_page($pd_s);

// EOF page_data

/**
 * Include plugin (and plugin files)
 */
foreach (XH_plugins() as $plugin) {
    PluginFiles($plugin);
    if (is_readable($pth['file']['plugin_classes'])) {
	include($pth['file']['plugin_classes']);
    }
}

foreach (XH_plugins() as $plugin) {
    PluginFiles($plugin);

    // Load plugin config
    if (file_exists($pth['folder']['plugins'].$plugin.'/config/defaultconfig.php')) {
	include($pth['folder']['plugins'].$plugin.'/config/defaultconfig.php');
    }
    if (file_exists($pth['file']['plugin_config'])) {
	include($pth['file']['plugin_config']);
    }
    
    XH_createLanguageFile($pth['file']['plugin_language']);
    
    // Load default plugin language
    if (file_exists($pth['folder']['plugins'] . $plugin . '/languages/default.php')) {
        include $pth['folder']['plugins'] . $plugin . '/languages/default.php';
    }
    // Load plugin language
    if (file_exists($pth['file']['plugin_language'])) {
	include($pth['file']['plugin_language']);
    }

    // Load plugin index.php or die
    if (file_exists($pth['file']['plugin_index']) AND !include($pth['file']['plugin_index'])) {
	die($tx['error']['plugin_error'] . $tx['error']['cntopen'] . $pth['file']['plugin_index']);
    }

    // Add plugin css to the header of CMSimple/Template
    if (file_exists($pth['file']['plugin_stylesheet'])) {
	$hjs .= tag('link rel="stylesheet" href="' . $pth['file']['plugin_stylesheet'] . '" type="text/css"') . "\n";
    }
}


/**
 * Load admin functions (admin.php, if exists) of plugin
 */
if ($adm) {
    foreach (XH_plugins(true) as $plugin) {
	PluginFiles($plugin);
	if (is_readable($pth['file']['plugin_admin'])) {
	    include($pth['file']['plugin_admin']);
	}
    }
    // ########## bridge to page data ##########
    $o .= $pd_router->create_tabs($s);
    // #########################################
}

/**
 * Pre-Call Plugins
 */
preCallPlugins();

// Plugin functions
unset($plugin);

?>
