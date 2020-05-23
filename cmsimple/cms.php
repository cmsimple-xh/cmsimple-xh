<?php

/**
 * @file cms.php
 *
 * The main file of CMSimple_XH.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/*
  ======================================
  @CMSIMPLE_XH_VERSION@, @CMSIMPLE_XH_BUILD@
  @CMSIMPLE_XH_DATE@
  based on CMSimple version 3.3 - December 31. 2009
  For changelog, downloads and information please see http://www.cmsimple-xh.org/
  ======================================
  [Please note: URLs in the following Copyright Notice are either void or
  lead to different information as Mr. Harteg sold the code and website
  in Nov. 2012.  Of the four mentioned licenses only the first (GPL 3)
  applies to CMSimple_XH.]

  -- COPYRIGHT INFORMATION START --

  CMSimple version 3.3 - December 31. 2009
  Small - simple - smart
  (c) 1999-2009 Peter Andreas Harteg - peter@harteg.dk

  -- COPYRIGHT INFORMATION END --

  -- LICENCE TYPES SECTION START --

  CMSimple is available under four different licenses:

  1) GPL 3
  From December 31. 2009 CMSimple is released under the GPL 3 licence with no
  link requirments. You may not remove copyright information from the files, and
  any modifications will fall under the copyleft conditions in the GPL 3.

  2) AGPL 3
  You must keep a convenient and prominently visible feature on every generated
  page that displays the CMSimple Legal Notices. The required link to the
  CMSimple Legal Notices must be static, visible and readable, and the text in
  the CMSimple Legal Notices may not be altered. See
  http://www.cmsimple.org/?Licence:CMSimple_Legal_Notices

  3) Linkware / CMSimple Link Requirement Licence
  Same as AGPL, but instead of keeping a link to the CMSimple Legal Notices, you
  must place a static, visible and readable link to www.cmsimple.org with the
  text or an image stating "Powered by CMSimple" on every generated page (place
  it in the template). See
  http://www.cmsimple.org/?Licence:CMSimple_Link_Requirement_Licence

  4) Commercial Licence
  This licence will allow you to remove the CMSimple Legal Notices / "Powered by
  CMSimple"-link at one specific domain. This licence will also protect your
  modifications against the copyleft requirements in AGPL 3 and give access to
  registering in user support forum.

  You may change this LICENCE TYPES SECTION to relevant information, if you have
  purchased a commercial licence, but then the files may not be distributed to
  any other domain not covered by a commercial licence.

  For further informaion about the licence types, please see
  http://www.cmsimple.org/?Licence and /cmsimple/legal.txt

  -- LICENCE TYPES SECTION END --
  ======================================
 */

// prevent direct access
if (preg_match('/cms\.php/i', $_SERVER['PHP_SELF'])) {
    die('Access Denied');
}

/**
 * The separator for urichar_org/new.
 *
 * @since 1.6
 */
define('XH_URICHAR_SEPARATOR', '|');

/**
 * The title of the current page.
 *
 * This <i>read-write</i> variable can be used to set the page title in the
 * plugin administration and for special extension pages.
 *
 * @var string $title
 *
 * @access public
 */
$title = '';

/**
 * The HTML for the contents area.
 *
 * This <i>read-write</i> variable is used to buffer the output, which is
 * prepended to the contents of the current page (if any). Usually you will
 * only append to this variable.
 *
 * @global string $o
 *
 * @access public
 */
$o = '';

/**
 * The HTML for the <li>s holding error messages.
 *
 * This <i>read-write</i> variable can be used to add error messages above the
 * content. Usually you will only append to this variable.
 *
 * @global string $e
 *
 * @access public
 *
 * @see e()
 */
$e = '';

/**
 * HTML that will be inserted to the <head> section.
 *
 * This <i>read-write</i> variable can be used to add script, style, meta and link
 * elements etc. to the head element. Usually you will only append to this variable.
 *
 * @global string $hjs
 *
 * @access public
 *
 * @see $bjs
 */
$hjs = '';

/**
 * HTML that will be inserted right before the </body> tag.
 *
 * This <i>read-write</i> variable can be used to add script elements to the end
 * of the body element. Usually you will only append to this variable.
 *
 * @global string $bjs
 *
 * @access public
 *
 * @see $hjs
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#bjs
 *
 * @since 1.5.4
 */
$bjs = '';

/**
 * JavaScript for the onload attribute of the body element.
 *
 * This <i>read-write</i> variable can be used to register window onload event
 * handlers. Usually you will only append to this variable.
 *
 * @global string $onload
 *
 * @access public
 */
$onload = '';

/**
 * A temporary value.
 *
 * This <i>read-write</i> variable can be used to avoid polluting the global scope.
 *
 * @global mixed $temp
 *
 * @access public
 */
$temp = null;

/**
 * A temporary (loop) value.
 *
 * This <i>read-write</i> variable can be used to avoid polluting the global scope.
 *
 * @global mixed $i
 *
 * @access public
 */
$i = null;

/**
 * A temporary (loop) value.
 *
 * This <i>read-write</i> variable can be used to avoid polluting the global scope.
 *
 * @global mixed $j
 *
 * @access public
 */
$j = null;

/**
 * The version in textual representation, e.g. CMSimple_XH 1.6
 */
define('CMSIMPLE_XH_VERSION', '@CMSIMPLE_XH_VERSION@');
/**
 * The build number as integer: YYYYMMDDBB
 */
define('CMSIMPLE_XH_BUILD', '@CMSIMPLE_XH_BUILD@');
/**
 * The release date in ISO 8601 format: YYYY-MM-DD
 */
define('CMSIMPLE_XH_DATE', '@CMSIMPLE_XH_DATE@');

/**
 * A two dimensional array that holds the paths of important files and folders.
 *
 * Should be treated as <i>read-only</i>.
 *
 * @global array $pth
 *
 * @access public
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/core_variables#pth
 */
$pth = array();
$pth['file']['execute'] = './index.php';

$pth['folder']['base'] = is_dir('./cmsimple') ? './' : '../';

$pth['folder']['cmsimple'] = $pth['folder']['base'] . 'cmsimple/';
$pth['folder']['classes'] = $pth['folder']['cmsimple'] . 'classes/';
$pth['folder']['plugins'] = $pth['folder']['base'] . 'plugins/';

$pth['file']['log'] = $pth['folder']['cmsimple'] . 'log.txt';
$pth['file']['cms'] = $pth['folder']['cmsimple'] . 'cms.php';
$pth['file']['config'] = $pth['folder']['cmsimple'] . 'config.php';

// include general utility functions and classes
require_once $pth['folder']['cmsimple'] . 'functions.php';
spl_autoload_register('XH_autoload');
require_once $pth['folder']['cmsimple'] . 'tplfuncs.php';
require_once $pth['folder']['cmsimple'] . 'utf8.php';
if (!function_exists('password_hash') || !function_exists('random_bytes')) {
    include_once $pth['folder']['cmsimple'] . 'password.php';
}
require_once $pth['folder']['cmsimple'] . 'seofuncs.php';

/**
 * The controller.
 *
 * @var XH\Controller
 *
 * @access private
 */
$_XH_controller = new XH\Controller();

/**
 * The configuration of the core.
 *
 * Should be treated as <i>read-only</i>.
 *
 * @global array $cf
 *
 * @access public
 *
 * @see $plugin_cf
 */
$cf = XH_readConfiguration();
if (!$cf) {
    die("Config file {$pth['file']['config']} missing");
}
// removed from the core in XH 1.6, but left for compatibility with plugins.
$cf['security']['type'] = 'page';
$cf['scripting']['regexp'] = '#CMSimple (.*?)#';

// removed from the core in XH 1.7, but left for compatibility with extensions
$cf['xhtml']['endtags'] = '';
$cf['xhtml']['amp'] = 'true';

foreach (array('userfiles', 'downloads', 'images', 'media') as $temp) {
    // for compatibility with older version's config files
    if (!isset($cf['folders'][$temp])) {
        $cf['folders'][$temp] = $temp != 'media' ? "$temp/" : 'downloads/';
    }
    if ($temp == 'userfiles') {
        $pth['folder'][$temp] = $pth['folder']['base'] . $cf['folders'][$temp];
    } else {
        $pth['folder'][$temp] = $pth['folder']['userfiles'] . $cf['folders'][$temp];
    }
}

$pth['folder']['flags'] = $pth['folder']['images'] . 'flags/';

if ($cf['site']['compat']) {
    include_once $pth['folder']['cmsimple'] . 'compat.php';
}

/**
 * Debug output generated by PHP according to debug mode.
 *
 * @global array $errors
 *
 * @access private
 */
$errors = array();
xh_debugmode();

$pth['folder']['language'] = $pth['folder']['cmsimple'] . 'languages/';

if (!isset($cf['folders']['content'])) {
    $cf['folders']['content'] = 'content/';
}

if ($cf['site']['timezone'] !== '' && function_exists('date_default_timezone_set')) {
    date_default_timezone_set($cf['site']['timezone']);
}

/**
 * The current language.
 *
 * This <i>read-only</i> variable contains an ISO 639-1 language code.
 *
 * @global string $sl
 *
 * @access public
 */
$sl = '';
if (preg_match('/\/([A-z]{2})\/index.php$/', sv('SCRIPT_NAME'), $temp)
    && XH_isLanguageFolder($temp = strtolower($temp[1]))
) {
    $sl = $temp;
    $pth['folder']['content']
        = $pth['folder']['base'] . $cf['folders']['content'] . $sl . '/';
} else {
    $sl = $cf['language']['default'];
    $pth['folder']['content'] = $pth['folder']['base'] . $cf['folders']['content'];
}

$pth['file']['content'] = $pth['folder']['content'] . 'content.htm';
$pth['file']['pagedata'] = $pth['folder']['content'] . 'pagedata.php';
$pth['file']['language'] = $pth['folder']['language'] . basename($sl) . '.php';
$pth['folder']['corestyle'] = $pth['folder']['base'] . 'assets/css/';
$pth['file']['corestyle'] = $pth['folder']['corestyle'] . 'core.css';
$pth['file']['adminjs'] = $pth['folder']['base'] . 'assets/js/admin.min.js';

XH_createLanguageFile($pth['file']['language']);

/**
 * The localization of the core.
 *
 * Should be treated as <i>read-only</i>.
 *
 * @global array $tx
 *
 * @access public
 *
 * @see $plugin_tx
 */
$tx = XH_readConfiguration(false, true);
if (!$tx) {
    die("Language file {$pth['file']['language']} missing");
}
if ($tx['locale']['all'] != '') {
    setlocale(LC_ALL, $tx['locale']['all']);
}

/*
 * Register shutdown handler.
 */
register_shutdown_function('XH_onShutdown');

// removed from the core in XH 1.6, but left for compatibility with plugins.
$tx['meta']['codepage']='UTF-8';

/**
 * The language configuration.
 *
 * @global array $txc
 *
 * @access public
 *
 * @deprecated since 1.6 (use $cf resp. $tx instead).
 */
$txc = array('template' => $tx['template']);

$_XH_controller->initTemplatePaths();

/*
 * Additional security measure. However, we can neither check cookies,
 * as these might be set from non UTF-8 scripts on the domain, nor server
 * variables (<http://cmsimpleforum.com/viewtopic.php?f=10&t=8052>).
 */
XH_checkValidUtf8(
    array($_GET, $_POST, array_keys($_POST))
);

/**
 * Whether the webserver is IIS.
 *
 * Should be treated as <i>read-only</i>.
 *
 * @access public
 *
 * @global bool $iis
 */
$iis = strpos(sv('SERVER_SOFTWARE'), "IIS");

/**
 * Whether PHP is executed as (F)CGI.
 *
 * Should be treated as <i>read-only</i>.
 *
 * @access public
 *
 * @global bool $cgi
 */
$cgi = (php_sapi_name() == 'cgi' || php_sapi_name() == 'cgi-fcgi');

/**
 * The relative path of the root folder, i.e. the script name.
 *
 * Should be treated as <i>read-only</i>.
 *
 * @global string $sn
 *
 * @access public
 *
 * @see CMSIMPLE_URL
 */
$sn = preg_replace('/([^\?]*)\?.*/', '$1', sv(($iis ? 'SCRIPT_NAME' : 'REQUEST_URI')));

/**
 * The requested plugin administration part.
 *
 * This <i>read-only</i> variable is initialized from an <var>admin</var>
 * GET/POST parameter, and is usually used in combination with
 * {@link $action} to request some functionality of a plugin back-end.
 *
 * @global string $admin
 *
 * @access public
 */
$admin = null;

/**
 * The requested action.
 *
 * This <i>read-only</i> variable is initialized from an <var>action</var>
 * GET/POST parameter, and is usually used in combination with
 * {@link $admin} to request some functionality of a plugin back-end.
 *
 * @global string $action
 *
 * @access public
 */
$action = null;

/**
 * The requested function.
 *
 * This variable is set from a <var>function</var> GET/POST parameter, which
 * denotes some special functionality. If set from your extension treat it as
 * <i>read-write</i>; otherwise ignore it.
 *
 * @global string $function
 *
 * @access public
 */
$function = null;

/**
 * Whether login is requested.
 *
 * This variable is initialized from a <var>login</var> GET/POST parameter.
 * If the login has been successful, {@link $f} == 'login'; otherwise {@link $f}
 * == 'xh_login_failed'.
 *
 * @global string $login
 *
 * @access private
 */
$login = null;

/**
 * The admin password.
 *
 * This variable is initialized from a <var>keycut</var> GET/POST parameter.
 *
 * This variable has been renamed from <var>$passwd</var> since CMSimple_XH 1.6
 * to avoid trouble with mod_security.
 *
 * @global string $keycut
 *
 * @access private
 */
$keycut = null;

/**
 * Whether logout is requested.
 *
 * This variable is initialized from a <var>logout</var> GET/POST parameter.
 * On logout {@link $f} == 'xh_loggedout'.
 *
 * @global string $logout
 *
 * @access private
 */
$logout = null;

/**
 * Whether the mailform is requested.
 *
 * This variable is initialized from a <var>mailform</var> GET/POST parameter.
 * If the mailform has been requested {@link $f} == 'mailform'.
 *
 * @global string $mailform
 *
 * @access private
 */
$mailform = null;

/**
 * The filename requested for download.
 *
 * This variable is initialized from a <var>download</var> GET/POST parameter.
 *
 * @global string $download
 *
 * @access private
 */
$download = null;

/**
 * Whether the file browser is requested to show the download folder.
 *
 * This variable is initialized from a <var>downloads</var> GET/POST parameter,
 * and should only be used by file browsers and similar extensions, which may
 * treat it as <i>read-write</i>.
 *
 * @global string $downloads
 *
 * @access public
 */
$downloads = null;

/**
 * Whether the file browser is requested to show the image folder.
 *
 * This variable is initialized from a <var>images</var> GET/POST parameter,
 * and should only be used by file browsers and similar extensions, which may
 * treat it as <i>read-write</i>.
 *
 * @global string $images
 *
 * @access public
 */
$images = null;

/**
 * Whether the file browser is requested to show the media folder.
 *
 * This variable is initialized from a <var>media</var> GET/POST parameter,
 * and should only be used by file browsers and similar extensions, which may
 * treat it as <i>read-write</i>.
 *
 * @global string $media
 *
 * @access public
 */
$media = null;

/**
 * Whether the file browser is requested to show the userfiles folder.
 *
 * This variable is initialized from a <var>userfiles</var> GET/POST parameter,
 * and should only be used by file browsers and similar extensions, which may
 * treat it as <i>read-write</i>.
 *
 * @global string $userfiles
 *
 * @access public
 */
$userfiles = null;

/**
 * Whether edit mode is requested.
 *
 * This <i>read-only</i> variable is initialized from a <var>edit</var>
 * GET/POST parameter or the <var>mode</var> cookie. If you want to switch to
 * edit mode, set the <var>edit</var> GET parameter.
 *
 * @global string $edit
 *
 * @access public
 *
 * @see $normal
 */
$edit = null;

/**
 * Whether normal (aka view) mode is requested.
 *
 * This <i>read-only</i> variable is initialized from a <var>normal</var>
 * GET/POST parameter, but not from the <var>mode</var> cookie. If you want to
 * detect normal mode, check for <code>!$edit</code>. If you want to switch to
 * normal mode, set the <var>normal</var> GET parameter.
 *
 * @global string $normal
 *
 * @access public
 *
 * @see $edit
 */
$normal = null;

/**
 * Whether print mode is requested.
 *
 * This <i>read-only</i> variable is initialized from a <var>print</var> GET/POST
 * parameter.
 *
 * @global string $print
 *
 * @access public
 */
$print = null;

/**
 * The name of a special file to be handled in the back-end.
 *
 * This variable is initialized from a <var>file</var> GET/POST parameter.
 *
 * @global string $file
 *
 * @access private
 */
$file = null;

/**
 * The current search string.
 *
 * This <i>read-only</i> variable is initialized from a <var>search</var>
 * GET/POST parameter.
 *
 * @global string $search
 *
 * @access public
 */
$search = null;

/**
 * The URL of the requested page.
 *
 * This variable is initialized from a <var>selected</var> GET/POST parameter.
 * If present {@link $su} is set accordingly.
 *
 * @global string $selected
 *
 * @access private
 */
$selected = null;

/**
 * Whether the settings page is requested.
 *
 * This variable is initialized from a <var>settings</var> GET/POST parameter.
 *
 * @global string $settings
 *
 * @access private
 */
$settings = null;

/**
 * Whether the sitemap is requested.
 *
 * This variable is initialized from a <var>sitemap</var> GET/POST parameter.
 * If the sitemap is requested {@link $f} == 'sitemap'.
 *
 * @global string $sitemap
 *
 * @access private
 */
$sitemap = null;

/**
 * The text of the editor on save.
 *
 * This variable is initialized from a <var>text</var> GET/POST parameter.
 *
 * @global string $text
 *
 * @access private
 */
$text = null;

/**
 * Whether the link check is requested.
 *
 * This variable is initialized from a <var>validate</var> GET/POST parameter.
 *
 * @global string $validate
 *
 * @access private
 */
$validate = null;

/**
 * Whether the page manager is requested.
 *
 * This variable is initialized from a <var>xhpages</var> GET/POST parameter,
 * and should only be used by page managers, which may treat it as
 * <i>read-write</i>.
 *
 * @global string $xhpages
 *
 * @access public
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#page_managers
 */
$xhpages = null;

$temp = array(
    'action', 'admin', 'download', 'downloads', 'edit', 'file', 'function', 'images',
    'login', 'logout', 'keycut', 'mailform', 'media', 'normal', 'phpinfo', 'print', 'search',
    'selected', 'settings', 'sitemap', 'sysinfo', 'text', 'userfiles', 'validate', 'xhpages',
    'xh_backups', 'xh_change_password', 'xh_do_validate', 'xh_pagedata', 'xh_plugins'
);
foreach ($temp as $i) {
    if (!isset($GLOBALS[$i])) {
        if (isset($_GET[$i])) {
            $GLOBALS[$i] = $_GET[$i];
        } elseif (isset($_POST[$i])) {
            $GLOBALS[$i] = $_POST[$i];
        } else {
            $GLOBALS[$i] = '';
        }
    }
}

/**
 * The absolute path of the root folder.
 */
define('CMSIMPLE_ROOT', XH_getRootFolder());

/**
 * The relative path of the root folder.
 */
define('CMSIMPLE_BASE', $pth['folder']['base']);

/**
 * The fully qualified absolute URL of the installation (main or current language).
 *
 * @since 1.6
 *
 * @see $sn
 */
define(
    'CMSIMPLE_URL',
    'http'
    . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 's' : '')
    . '://' . $_SERVER['HTTP_HOST'] . $sn
);

/**
 * The current page's URL (selected URL).
 *
 * Should be treated as <i>read-only</i>.
 *
 * @global string $su
 *
 * @access public
 *
 * @see $selected
 * @see $u
 */
$su = '';
if (sv('QUERY_STRING') != '') {
    $j = explode('&', sv('QUERY_STRING'));
    if (!strpos($j[0], '=')) {
        $su = $j[0];
    }
    if ($su == '' && $selected != '') {
        if (isset($_GET['selected'])) {
            header('Location: ' . XH_redirectSelectedUrl(), true, 301);
            exit;
        } else {
            $su = $selected;
        }
    }
    foreach ($j as $i) {
        if (!strpos($i, '=') && in_array($i, $temp)) {
            $GLOBALS[$i] = 'true';
        }
    }
} else {
    $su = $selected;
}
if (!isset($cf['uri']['length'])) {
    $cf['uri']['length'] = 200;
}
$su = utf8_substr($su, 0, $cf['uri']['length']);

if ($download != '') {
    download($pth['folder']['downloads'] . basename(stsl($download)));
}

$pth['file']['search'] = $pth['folder']['cmsimple'] . 'search.php';
$pth['file']['mailform'] = $pth['folder']['cmsimple'] . 'mailform.php';

/**
 * Whether admin mode is active.
 *
 * This variable is strictly <i>read-only</i>.
 *
 * @global bool $adm
 *
 * @access public
 *
 * @see XH_ADM
 */
$adm = 0;

/**
 * The requested function.
 *
 * This <i>read-write</i> variable is initialized from different GET/POST
 * parameters. Usually you will want to treat it as <i>read-only</i> or even as
 * <i>private</i>.
 *
 * @global string $f
 *
 * @access public
 */
$f = '';

/**
 * The plugin menu builder.
 *
 * @global XH\ClassicPluginMenu $_XH_pluginMenu
 *
 * @access private
 */
$_XH_pluginMenu = new XH\ClassicPluginMenu();

/**
 * The currently loaded plugin.
 *
 * Should be treated as <i>read-only</i>.
 *
 * @global string $plugin
 *
 * @access public
 */
$plugin = null;

/*
 * Include required_classes of all plugins.
 */
foreach (XH_plugins() as $plugin) {
    pluginFiles($plugin);
    if (is_readable($pth['file']['plugin_classes'])) {
        include_once $pth['file']['plugin_classes'];
    }
}

/**
 * The CRSF protection object.
 *
 * Should be treated as <i>read-only</i>.
 *
 * @global XH\CSRFProtection $_XH_csrfProtection
 *
 * @access public
 *
 * @tutorial CSRFProtection.cls
 */
$_XH_csrfProtection = null;
if (isset($_COOKIE['status']) && $_COOKIE['status'] == 'adm'
    || isset($_POST['keycut'])
) {
    $_XH_csrfProtection = new XH\CSRFProtection();
}

$_XH_controller->handleLoginAndLogout();

/**
 * Whether admin mode is active.
 *
 * @since 1.5.4
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#xh_adm
 *
 * @see $adm
 */
define('XH_ADM', $adm);

if (XH_ADM) {
    include_once $pth['folder']['cmsimple'] . 'adminfuncs.php';
    if (isset($_GET['xh_keep_alive'])) {
        $_XH_controller->handleKeepAlive();
    }
    $_XH_controller->outputAdminScripts();
}

$_XH_controller->setFunctionsAsPermitted();

/**
 * The number of pages.
 *
 * Treat as <i>read-only</i>.
 *
 * @global int $cl
 *
 * @access public
 */
$cl = 0;

/**
 * The page data router.
 *
 * Treat as <i>read-only</i>.
 *
 * @global XH\PageDataRouter $pd_router
 *
 * @access public
 */
$pd_router = null;

/**
 * The publisher instance.
 *
 * @global XH::Publisher $xh_publisher
 *
 * @access public
 *
 * @since 1.7.0
 */
$xh_publisher = null;

/**
 * The index of the currently requested page.
 *
 * Treat as <i>read-only</i>. Note that $s is not properly set for the start
 * page until all plugins are loaded. If you need the know the index of the
 * currently requested page during plugin loading, consider to use {@link $pd_s}.
 *
 * @global int $s
 *
 * @access public
 */
$s = -1;

/**
 * The content of the pages.
 *
 * Treat as <i>read-only</i> when in edit mode.
 *
 * @global array $c
 *
 * @access public
 */
$c = null;

/**
 * The headings of the pages.
 *
 * Treat as <i>read-only</i>.
 *
 * @global array $h
 *
 * @access public
 *
 * @see h()
 */
$h = null;

/**
 * The URLs of the pages.
 *
 * Treat as <i>read-only</i>.
 *
 * @global array $u
 *
 * @access public
 *
 * @see $su
 */
$u = null;

/**
 * The menu levels of the pages.
 *
 * Treat as <i>read-only</i>.
 *
 * @global array $l
 *
 * @access public
 *
 * @see l()
 */
$l = null;

rfc(); // Here content is loaded

/*
 * Remove $su from FirstPublicPage
 * Remove empty path segments in an URL - https://github.com/cmsimple-xh/cmsimple-xh/issues/282
 * Integration of the ADC-Core_XH plugin with extended functions (optional)
*/
XH_URI_Cleaning();

$_XH_controller->setFrontendF();

if (is_readable($pth['folder']['cmsimple'] . 'userfuncs.php')) {
    include_once $pth['folder']['cmsimple'] . 'userfuncs.php';
}

$cf['site']['title'] = $tx['site']['title']; // for backward compatibility

// Plugin loading
if ($function == 'save') {
    $edit = true;
}

/**
 * For compatibility with plugins.
 */
define('PLUGINLOADER', true);

/**
 * For compatibility with plugins.
 */
define('PLUGINLOADER_VERSION', 2.111);

/**
 * A unique prefix for autogenerated forms.
 *
 * @link http://forum.cmsimple-xh.dk/?f=12&t=4956#p25550
 */
define('XH_FORM_NAMESPACE', 'PL3bbeec384_');

if (XH_ADM) {
    $o .= ' '; // generate fake output to suppress later adjustment of $s
    if ($_XH_controller->wantsSavePageData()) {
        $_XH_controller->handleSavePageData();
    }
}

/**
 * The index of the currently requested page.
 *
 * Treat as <i>read-only</i>. Note that the index of the currently requested page
 * is available in {@link $s} after the plugins have been loaded. During plugin
 * loading you may use $pd_s, but this is not guaranteed to be correct, as it
 * may be set to 0, even if $s might later be -1.
 *
 * @global int $pd_s
 *
 * @access public
 *
 * @see $s
 */
$pd_s = ($s == -1 && !$f && $o == '' && $su == '') ? $xh_publisher->getFirstPublishedPage() : $s;

/**
 * The infos about the current page.
 *
 * Treat as <i>read-only</i>.
 *
 * @global array $pd_current
 *
 * @access public
 */
$pd_current = $pd_router->find_page($pd_s);

/**
 * The configuration of the plugins.
 *
 * Treat as <i>read-only</i>.
 *
 * @global XH\PluginConfig $plugin_cf
 *
 * @access public
 *
 * @see $cf
 */
$plugin_cf = new XH\PluginConfig();

/**
 * The localization of the plugins.
 *
 * Treat as <i>read-only</i>.
 *
 * @global XH\PluginConfig $plugin_tx
 *
 * @access public
 *
 * @see $tx
 */
$plugin_tx = new XH\PluginConfig(true);

/*
 * Include index.php of all plugins.
 */
foreach (XH_plugins() as $plugin) {
    pluginFiles($plugin);
    if (is_readable($pth['file']['plugin_index'])) {
        include $pth['file']['plugin_index'];
    }
}

if (XH_ADM) {
    /*
     * Include admin.php of all plugins.
     */
    foreach (XH_plugins(true) as $plugin) {
        pluginFiles($plugin);
        if (is_readable($pth['file']['plugin_admin'])) {
            include $pth['file']['plugin_admin'];
        }
    }
    $o .= $pd_router->create_tabs($s);
}

unset($plugin);

XH_afterPluginLoading();


switch ($f) {
    case 'search':
        $_XH_controller->handleSearch();
        break;
    case 'mailform':
        $_XH_controller->handleMailform();
        break;
    case 'sitemap':
        $_XH_controller->handleSitemap();
        break;
    case 'forgotten':
        $_XH_controller->handlePasswordForgotten();
        break;
}

/**
 * The page indexes of the visible menu items.
 *
 * This <i>read-only</i> variable can be used to build a menu with {@link li()}.
 *
 * @global array $hc
 *
 * @access public
 */
$hc = array();

/**
 * The length of {@link $hc}.
 *
 * @global int $hl
 *
 * @access private
 */
$hl = -1;

/**
 * The index of the current page in {@link $hc}.
 *
 * @global int $si
 *
 * @access private
 */
$si = -1;

XH_buildHc();

if (XH_ADM) {
    $_XH_controller->setBackendF();

    $temp = array(
        'settings', 'xh_backups', 'images', 'downloads', 'validate', 'sysinfo',
        'phpinfo', 'xh_pagedata', 'change_password'
    );
    if (in_array($f, $temp)) {
        $title = $tx['title'][$f];
        $o .= "\n\n" . '<h1>' . $title . '</h1>' . "\n";
    }

    switch ($f) {
        case 'sysinfo':
            $o .= XH_sysinfo();
            break;
        case 'phpinfo':
            phpinfo();
            exit;
        case 'settings':
            $o .= XH_settingsView();
            break;
        case 'xh_backups':
            $o .= XH_backupsView();
            break;
        case 'xh_pagedata':
            $_XH_controller->handlePageDataEditor();
            break;
        case 'file':
            if (XH_isContentBackup($file, false)) {
                $pth['file'][$file] = $pth['folder']['content'] . $file;
            }
            if ($pth['file'][$file] != '') {
                switch ($action) {
                    case 'view':
                        $_XH_controller->handleFileView();
                        break;
                    case 'download':
                        download($pth['file'][$file]);
                        break;
                    case 'backup':
                        $_XH_controller->handleFileBackup();
                        break;
                    case 'restore':
                        $_XH_csrfProtection->check();
                        XH_restore($pth['file'][$file]);
                        break;
                    case 'empty':
                        $_XH_csrfProtection->check();
                        if ($file == 'content') {
                            XH_emptyContents();
                        }
                        break;
                    default:
                        $_XH_controller->handleFileEdit();
                }
            }
            break;
        case 'validate':
        case 'do_validate':
            $temp = new XH\LinkChecker();
            $o .= ($f == 'validate') ? $temp->prepare() : $temp->doCheck();
            break;
        case 'change_password':
            $temp = new XH\ChangePassword();
            $i = $action === 'save' ? 'save' : 'default';
            $temp->{"{$i}Action"}();
            break;
        case 'xh_plugins':
            $o .= XH_pluginsView();
            break;
    }
}


// fix $s
if ($s == -1 && !$f && $o == '' && $su == '') {
    $s = $hs = $xh_publisher->getFirstPublishedPage();
}

if (XH_ADM) {
    if ($f == 'save') {
        $_XH_controller->handleSaveRequest();
    }
    if ($_XH_controller->wantsEditContents()) {
        $_XH_controller->outputEditContents();
    }
    if ($_XH_controller->isFilebrowserMissing()) {
        $_XH_controller->reportMissingExternal('filebrowser');
    }
    if ($_XH_controller->isPagemanagerMissing()) {
        $_XH_controller->reportMissingExternal('pagemanager');
    }
}



// CMSimple scripting
/**
 * The output to be manipulated by CMSimple scripting.
 *
 * @global string $output
 *
 * @access public
 */
$output = null;
if (!($edit && XH_ADM) && $s > -1) {
    $c[$s] = evaluate_scripting($c[$s]);
    if (isset($keywords)) {
        $tx['meta']['keywords'] = $keywords;
    }
    if (isset($description)) {
        $tx['meta']['description'] = $description;
    }
}


if ($s == -1 && !$f && $o == '') {
    shead(404);
}

loginforms();

$o = $_XH_controller->renderErrorMessages() . $o;
if ($title == '') {
    if ($s > -1) {
        $title = $h[$s];
    } elseif ($f != '') {
        // FIXME: check for duplication, i.e. isn't $title already set to $f?
        $title = ucfirst($f);
    }
}

$_XH_controller->sendStandardHeaders();

if ($print) {
    XH_builtinTemplate('print');
} elseif (in_array($f, array('login', 'xh_login_failed', 'forgotten'))) {
    XH_builtinTemplate('xh_login');
}

if (XH_ADM) {
    $bjs .= '<script src="' . $pth['file']['adminjs']
        . '"></script>' . PHP_EOL
        . XH_adminJSLocalization();
}

$_XH_controller->verifyAdm();

ob_start();

$i = false;
$temp = fopen($pth['file']['template'], 'r');
if ($temp) {
    if (XH_lockFile($temp, LOCK_SH)) {
        $i = include $pth['file']['template'];
        XH_lockFile($temp, LOCK_UN);
    }
    fclose($temp);
}
if (!$i) {// the template could not be included
    XH_emergencyTemplate();
}

if (isset($_XH_csrfProtection)) {
    $_XH_csrfProtection->store();
}

echo XH_finalCleanUp(ob_get_clean());
