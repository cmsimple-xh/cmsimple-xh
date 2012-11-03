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

/**
 * Function PluginFiles()
 * Set plugin filenames.
 *
 * @param string $plugin Name of the plugin, the filenames 
 * will be set for.
 *
 * @global array $cf CMSimple's Config-Array
 * @global string $pth CMSimple's configured pathes in an array
 * @global string $sl CMSimple's selected language
 */
function PluginFiles($plugin) {

    global $cf, $pth, $sl;

    $pth['folder']['plugin'] = $pth['folder']['plugins'] . $plugin . '/';
    $pth['folder']['plugin_classes'] = $pth['folder']['plugins'] . $plugin . '/classes/';
    $pth['folder']['plugin_config'] = $pth['folder']['plugins'] . $plugin . '/config/';
    $pth['folder']['plugin_content'] = $pth['folder']['plugins'] . $plugin . '/content/';
    $pth['folder']['plugin_css'] = $pth['folder']['plugins'] . $plugin . '/css/';
    $pth['folder']['plugin_help'] = $pth['folder']['plugins'] . $plugin . '/help/';
    $pth['folder']['plugin_includes'] = $pth['folder']['plugins'] . $plugin . '/includes/';
    $pth['folder']['plugin_languages'] = $pth['folder']['plugins'] . $plugin . '/languages/';

    $pth['file']['plugin_index'] = $pth['folder']['plugin'] . 'index.php';
    $pth['file']['plugin_admin'] = $pth['folder']['plugin'] . 'admin.php';

    $pth['file']['plugin_language'] = $pth['folder']['plugin_languages'] . strtolower($sl) . '.php';

    $pth['file']['plugin_classes'] = $pth['folder']['plugin_classes'] . 'required_classes.php';
    $pth['file']['plugin_config'] = $pth['folder']['plugin_config'] . 'config.php';
    $pth['file']['plugin_stylesheet'] = $pth['folder']['plugin_css'] . 'stylesheet.css';

    $pth['file']['plugin_help'] = $pth['folder']['plugin_help'] . 'help_' . strtolower($sl) . '.htm';
    if (!file_exists($pth['file']['plugin_help'])) {
        $pth['file']['plugin_help'] = $pth['folder']['plugin_help'] . 'help_en.htm';
    }
    if (!file_exists($pth['file']['plugin_help']) AND file_exists($pth['folder']['plugin_help'] . 'help.htm')) {
        $pth['file']['plugin_help'] = $pth['folder']['plugin_help'] . 'help.htm';
    }
}

/**
 * Create menu of plugin (add row, add tab), constructed as a table.
 *
 * @param string $add Add a ROW, a TAB or DATA (Userdefineable content). SHOW will return the menu.
 * @param string $link The link, the TAB will lead to.
 * @param string $target Target of the link (with(!) 'target=').
 * @param string $text Description of the TAB.
 * @param array $style Array with style-data for the containing table-cell
 */
function PluginMenu($add = '', $link = '', $target = '', $text = '', $style = array())
{
    static $menu = '';
    
    $add = strtoupper($add);

    if (!isset($style['row'])) {
        $style['row'] = 'class="edit" style="width: 100%;"';
    }
    if (!isset($style['tab'])) {
        $style['tab'] = '';
    }
    if (!isset($style['link'])) {
        $style['link'] = '';
    }
    if (!isset($style['data'])) {
        $style['data'] = '';
    }

    $menu_row = '<table {{STYLE_ROW}} cellpadding="1" cellspacing="0">' . "\n"
	. '<tr>' . "\n" . '{{TAB}}</tr>' . "\n" . '</table>' . "\n" . "\n";
    $menu_tab = '<td {{STYLE_TAB}}><a{{STYLE_LINK}} href="{{LINK}}" {{TARGET}}>{{TEXT}}</a></td>' . "\n";
    $menu_tab_data = '<td {{STYLE_DATA}}>{{TEXT}}</td>' . "\n";

    if ($add == 'ROW') {
        $new_menu_row = $menu_row;
        $new_menu_row = str_replace('{{STYLE_ROW}}', $style['row'], $new_menu_row);
        $menu .= $new_menu_row;
    }

    if ($add == 'TAB') {
        $new_menu_tab = $menu_tab;
        $new_menu_tab = str_replace('{{STYLE_TAB}}', $style['tab'], $new_menu_tab);
        $new_menu_tab = str_replace('{{STYLE_LINK}}', $style['link'], $new_menu_tab);
        $new_menu_tab = str_replace('{{LINK}}', $link, $new_menu_tab);
        $new_menu_tab = str_replace('{{TARGET}}', $target, $new_menu_tab);
        $new_menu_tab = str_replace('{{TEXT}}', $text, $new_menu_tab);
        $menu = str_replace('{{TAB}}', $new_menu_tab . '{{TAB}}', $menu);
    }

    if ($add == 'DATA') {
        $new_menu_tab_data = $menu_tab_data;
        $new_menu_tab_data = str_replace('{{STYLE_DATA}}', $style['data'], $new_menu_tab_data);
        $new_menu_tab_data = str_replace('{{TEXT}}', $text, $new_menu_tab_data);
        $menu = str_replace('{{TAB}}', $new_menu_tab_data . '{{TAB}}', $menu);
    }

    if ($add == 'SHOW') {
        $menu = str_replace('{{TAB}}', '', $menu);
        $m = $menu;
        $menu = '';
        return $m;
    }
}


/**
 * Create plugin menu tabs.
 *
 * @param string $main Set to OFF, if menu is not needed.
 *
 * @return string Returns the created plugin-menu.
 */
function print_plugin_admin($main)
{
    global $sn, $plugin, $pth, $sl, $cf, $tx, $plugin_tx;

    initvar('action');
    initvar('admin');
    PluginFiles($plugin);
    
    $main = strtoupper($main) == 'ON';
    $css = is_readable($pth['file']['plugin_stylesheet']);
    $config = is_readable($pth['file']['plugin_config']);
    $language = is_readable($pth['file']['plugin_language']);
    $help = is_readable($pth['file']['plugin_help']);

    $tx_main = empty($plugin_tx[$plugin]['menu_main'])
	? $tx['menu']['tab_main'] : $plugin_tx[$plugin]['menu_main'];
    $tx_css = empty($plugin_tx[$plugin]['menu_css'])
	? $tx['menu']['tab_css'] : $plugin_tx[$plugin]['menu_css'];
    $tx_config = empty($plugin_tx[$plugin]['menu_config'])
	? $tx['menu']['tab_config'] : $plugin_tx[$plugin]['menu_config'];
    $tx_language = empty($plugin_tx[$plugin]['menu_language'])
	? $tx['menu']['tab_language'] : $plugin_tx[$plugin]['menu_language'];
    $tx_help = empty($plugin_tx[$plugin]['menu_help'])
	? $tx['menu']['tab_help'] : $plugin_tx[$plugin]['menu_help'];

    PluginMenu('ROW', '', '', '', array());
    if ($main) {
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_main&amp;action=plugin_text',
		   '', $tx_main, array());
    }
    if ($css) {
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_stylesheet&amp;action=plugin_text',
		   '', $tx_css, array());	
    }
    if ($config) { 
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_config&amp;action=plugin_edit',
		   '', $tx_config, ''); 
    }
    if ($language) {
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_language&amp;action=plugin_edit',
		   '', $tx_language, '');
    }
    if ($help) {
        PluginMenu('TAB', $pth['file']['plugin_help'],
		   'target="_blank"', $tx_help, '');
    }
    return PluginMenu('SHOW');
}


/**
 * Function plugin_admin_common()
 *
 * Handles reading and writing of plugin files
 * (e.g. en.php, config.php, stylesheet.css)
 *
 * @param bool $action Possible values: 'empty' = Error: empty value detected
 * @param array $admin Array, that contains debug_backtrace()-data
 * @param bool $plugin The called varname
 * @param bool $hint Array with hints for the variables (will be shown as popup)
 *
 * @global string $sn CMSimple's script-name (relative adress including $_GET)
 * @global string $action Ordered action
 * @global string $admin Ordered admin-action
 * @global string $plugin Name of the plugin, that called this function
 * @global string $pth CMSimple's configured pathes in an array
 * @global string $tx CMSimple's text-array
 * @global string $plugin_tx Plugin's text-array
 * @global string $plugin_cf Plugin's config-array
 *
 * @return string Returns the created form or the result of saving the data
 */
function plugin_admin_common($action, $admin, $plugin, $hint=ARRAY())
{
    // TODO: do something about the fake parameters
    // TODO: note that $hint is ignored now
    global $action, $admin, $plugin, $pth;
    
    require_once $pth['folder']['classes'] . 'FileEdit.php';
    switch ($admin) {
    case 'plugin_config':
	$fileEdit = new XH_PluginConfigFileEdit();
	break;
    case 'plugin_language':
	$fileEdit = new XH_PluginLanguageFileEdit();
	break;
    case 'plugin_stylesheet':
	$fileEdit = new XH_PluginTextFileEdit();
	break;
    default:
	return false;
    }
    switch ($action) {
    case 'plugin_edit':
    case 'plugin_text':
	return $fileEdit->form();
    case 'plugin_save':
    case 'plugin_textsave':
	return $fileEdit->submit();
    default:
	return false;
    }
}


/**
 * Function preCallPlugins() => Pre-Call of Plugins.
 *
 * All Plugins which are called through a function-call
 * can use this. At the moment it is'nt possible to do
 * this with class-based plugins. They need to be called
 * through standard-CMSimple-Scripting.
 *
 * Call a plugin: place this in your code (example):
 * {{{PLUGIN:pluginfunction('parameters');}}}
 *
 * Call a built-in function (at the moment only one for
 * demonstration):
 * {{{HOME}}} or: {{{HOME:name_of_Link}}}
 * This creates a link to the first page of your CMSimple-
 * Installation.
 * 
 * @param pageIndex - added for search
 * @global bool $edit TRUE if edit-mode is active
 * @global array $c Array containing all contents of all CMSimple-pages
 * @global integer $s Pagenumber of active page
 * @global array $u Array containing URLs to all CMSimple-pages
 * 
 * @author mvwd
 * @since V.2.1.02
 */
function preCallPlugins($pageIndex = -1) {
    global $edit, $c, $s, $u;

    if (!$edit) {
        if ((int) $pageIndex > - 1 && (int) $pageIndex < count($u)) {
            $as = $pageIndex;
        } else {
            $as = $s < 0 ? 0 : $s;
        }
	$c[$as] = evaluate_plugincall($c[$as]);
    }
}


/**
 * Returns a list of all installed plugins.
 *
 * @since 1.6
 *
 * @param   bool $admin  Whether to return only plugins with a admin.php
 * @return  array
 */
function XH_plugins($admin = false)
{ // TODO: might be optimized to set $admPlugins only when necessary
    global $pth;
    static $plugins = null;
    static $admPlugins = null;
    
    if (!isset($plugins)) {
	$plugins = array();
	$admPlugins = array();
	$dh = opendir($pth['folder']['plugins']); // TODO: error handling?
	while (($fn = readdir($dh)) !== false) {
	    if (strpos($fn, '.') !== 0  // ignore hidden directories
		&& is_dir($pth['folder']['plugins'] . $fn))
	    {
		$plugins[] = $fn;
		PluginFiles($fn);
		if (is_file($pth['file']['plugin_admin'])) {
		    $admPlugins[] = $fn;
		}
	    }
	}
	closedir($dh);
	natcasesort($plugins);
	natcasesort($admPlugins);
    }    
    return $admin ? $admPlugins : $plugins;
}

?>
