<?php

/**
 * The main file of CMSimple_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
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
if (preg_match('/cms.php/i', $_SERVER['PHP_SELF'])) {
    die('Access Denied');
}

/**
 * The separator for urichar_org/new.
 *
 * @since   1.6
 */
define('XH_URICHAR_SEPARATOR', '|');

/**
 * The title of the current page.
 *
 * @global string $title
 */
$title = '';

/**
 * The (X)HTML for the contents area.
 *
 * @global string $o
 */
$o = '';

/**
 * The (X)HTML for the <li>s holding error messages.
 *
 * @global string $e
 */
$e = '';

/**
 * (X)HTML that will be inserted to the <head> section.
 *
 * @global  string $hjs
 */
$hjs = '';

/**
 * (X)HTML that will be inserted right before the </body> tag.
 *
 * @global string $bjs
 *
 * @since 1.5.4
 */
$bjs = '';

/**
 * JavaScript for the onload attribute of the BODY element.
 *
 * @global string $onload
 */
$onload = '';

/**
 * Used for temporary variables in the global scope.
 *
 * @global mixed $temp
 */
$temp = null;

/**
 * Used for temporary loop variables in the global scope.
 *
 * @global mixed $i
 */
$i = null;

/**
 * Used for temporary loop variables in the global scope.
 *
 * @global mixed $j
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

if (!defined('E_STRICT')) {
    /**
     * @ignore
     */
    define('E_STRICT', 2048);
}
if (!defined('E_DEPRECATED')) {
    /**
     * @ignore
     */
    define('E_DEPRECATED', 8192);
}
if (!defined('E_USER_DEPRECATED')) {
    /**
     * @ignore
     */
    define('E_USER_DEPRECATED', E_USER_NOTICE);
}

/**
 * A two dimensional array that holds the paths of important files and folders.
 *
 * @global array $pth
 */
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
require_once $pth['folder']['cmsimple'] . 'tplfuncs.php';
require_once $pth['folder']['classes'] . 'Controller.php';
require_once $pth['folder']['classes'] . 'CSRFProtection.php';
require_once $pth['folder']['classes'] . 'PasswordHash.php';
require_once $pth['folder']['classes'] . 'PageDataRouter.php';
require_once $pth['folder']['classes'] . 'PageDataModel.php';
require_once $pth['folder']['classes'] . 'PageDataView.php';
require_once $pth['folder']['classes'] . 'PluginMenu.php';
require_once $pth['folder']['plugins'] . 'utf8/utf8.php';
require_once UTF8 . '/ucfirst.php';
require_once UTF8 . '/utils/validation.php';

/**
 * The controller.
 *
 * @var XH_Controller
 */
$_XH_controller = new XH_Controller();

/**
 * The configuration of the core.
 *
 * @global array $cf
 */
$cf = XH_readConfiguration();
if (!$cf) {
    die("Config file {$pth['file']['config']} missing");
}
// removed from the core in XH 1.6, but left for compatibility with plugins.
$cf['security']['type']='page';
$cf['scripting']['regexp']='#CMSimple (.*?)#';

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

/**
 * Debug output generated by PHP according to debug mode.
 *
 * @global array $errors
 */
$errors = array();
xh_debugmode();

$pth['folder']['language'] = $pth['folder']['cmsimple'] . 'languages/';

if (!isset($cf['folders']['content'])) {
    $cf['folders']['content'] = 'content/';
}

$temp = 'date_default_timezone_set';
if ($cf['site']['timezone'] !== '' && function_exists($temp)) {
    $temp($cf['site']['timezone']);
}

/**
 * The current language.
 *
 * @global string $sl
 */
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
$pth['folder']['corestyle'] = $pth['folder']['base'] . 'core/css/';
$pth['file']['corestyle'] = $pth['folder']['corestyle'] . 'core.css';
$pth['file']['adminjs'] = $pth['folder']['base'] . 'core/js/admin.js';

XH_createLanguageFile($pth['file']['language']);

/**
 * The localization of the core.
 *
 * @global array $tx
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
if (function_exists('error_get_last')) {
    register_shutdown_function('XH_onShutdown');
}

// removed from the core in XH 1.6, but left for compatibility with plugins.
$tx['meta']['codepage']='UTF-8';

/**
 * The language configuration.
 *
 * @global array $txc
 *
 * @deprecated since 1.6 (use $cf resp. $tx instead).
 */
$txc = array('template' => $tx['template']);

$pth['folder']['templates'] = $pth['folder']['base'] . 'templates/';
$pth['folder']['template'] = $pth['folder']['templates']
    . $cf['site']['template'] . '/';

$temp = $tx['subsite']['template'] == ''
    ? $cf['site']['template']
    : $tx['subsite']['template'];
$pth['folder']['template'] = $pth['folder']['templates'] . $temp . '/';
$pth['file']['template'] = $pth['folder']['template'] . 'template.htm';
$pth['file']['stylesheet'] = $pth['folder']['template'] . 'stylesheet.css';
$pth['folder']['menubuttons'] = $pth['folder']['template'] . 'menu/';
$pth['folder']['templateimages'] = $pth['folder']['template'] . 'images/';

/*
 * Additional security measure. However, we cannot check cookies,
 * as these might be set from non UTF-8 scripts on the domain.
 */
XH_checkValidUtf8(
    array($_GET, $_POST, $_SERVER, array_keys($_POST))
);

/**
 * Whether the webserver is IIS.
 *
 * @global bool $iis
 */
$iis = strpos(sv('SERVER_SOFTWARE'), "IIS");

/**
 * Whether PHP is executed as (F)CGI.
 *
 * @global bool $cgi
 */
$cgi = (php_sapi_name() == 'cgi' || php_sapi_name() == 'cgi-fcgi');

/**
 * The relative path of the root folder, i.e. the script name.
 *
 * @global string $sn
 */
$sn = preg_replace(
    '/([^\?]*)\?.*/', '$1',
    sv(($iis ? 'SCRIPT_NAME' : 'REQUEST_URI'))
);

/**
 * The requested action.
 *
 * @global string $action
 */
$action = null;

/**
 * The requested function
 *
 * @global string $function
 */
$function = null;

/**
 * Whether login is requested.
 *
 * @global string $login
 */
$login = null;

/**
 * The admin password. This variable was renamed from <var>$passwd</var>
 * since CMSimple_XH 1.6 to avoid trouble with mod_security.
 *
 * @global string $keycut
 */
$keycut = null;

/**
 * Whether logout is requested.
 *
 * @global string $logout
 */
$logout = null;

/**
 * Whether the mailform is requested.
 *
 * @global string $mailform
 */
$mailform = null;

/**
 * The filename requested for download.
 *
 * @global string $download
 */
$download = null;

/**
 * Whether the file browser is requested to show the download folder.
 *
 * @global string $downloads
 */
$downloads = null;

/**
 * Whether the file browser is requested to show the image folder.
 *
 * @global string $images
 */
$images = null;

/**
 * Whether the file browser is requested to show the media folder.
 *
 * @global string $media
 */
$media = null;

/**
 * Whether the file browser is requested to show the userfiles folder.
 *
 * @global string $userfiles
 */
$userfiles = null;

/**
 * Whether edit mode is requested.
 *
 * @global string $edit
 */
$edit = null;

/**
 * Whether normal mode is requested.
 *
 * @global string $normal
 */
$normal = null;

/**
 * Whether print mode is requested.
 *
 * @global string $print
 */
$print = null;

/**
 * The name of a special file to be handled.
 *
 * @global string $file
 */
$file = null;

/**
 * The current search string.
 *
 * @global string $search
 */
$search = null;

/**
 * The URL of the requested page.
 *
 * @global string $selected
 */
$selected = null;

/**
 * Whether the settings page is requested.
 *
 * @global string $settings
 */
$settings = null;

/**
 * Whether the sitemap is requested.
 *
 * @global string $sitemap
 */
$sitemap = null;

/**
 * The text of the editor on save.
 *
 * @global string $text
 */
$text = null;

/**
 * Whether the link check is requested.
 *
 * @global string $validate
 */
$validate = null;

/**
 * Whether the page manager is requested.
 *
 * @global string $xhpages
 */
$xhpages = null;

$temp = array(
    'action', 'download', 'downloads', 'edit', 'file', 'function', 'images',
    'login', 'logout', 'keycut', 'mailform', 'media', 'normal', 'print', 'search',
    'selected', 'settings', 'sitemap', 'text', 'userfiles', 'validate', 'xhpages'
);
foreach ($temp as $i) {
    initvar($i);
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
 * @global string $su
 */
$su = '';
if (sv('QUERY_STRING') != '') {
    // $rq should be $temp, but its used at least in tg_popup
    $rq = explode('&', sv('QUERY_STRING'));
    if (!strpos($rq[0], '=')) {
        $su = $rq[0];
    }
    if ($su == '' && $selected != '') {
        $su = $selected;
    }
    foreach ($rq as $i) {
        if (!strpos($i, '=')) {
            $GLOBALS[$i] = 'true';
        }
    }
} else {
    $su = $selected;
}
if (!isset($cf['uri']['length'])) {
    $cf['uri']['length'] = 200;
}
$su = substr($su, 0, $cf['uri']['length']);

if ($download != '') {
    download($pth['folder']['downloads'] . basename(stsl($download)));
}

$pth['file']['search'] = $pth['folder']['cmsimple'] . 'search.php';
$pth['file']['mailform'] = $pth['folder']['cmsimple'] . 'mailform.php';

/**
 * Whether admin mode is active.
 *
 * @global bool $adm
 *
 * @see XH_ADM
 */
$adm = 0;

/**
 * The requested function.
 *
 * @global string $f
 */
$f = '';

/**
 * The password hasher.
 *
 * @global object $xh_hasher
 */
$xh_hasher = new PasswordHash(8, true);

/**
 * The plugin menu builder.
 *
 * @global XH_ClassicPluginMenu $_XH_pluginMenu
 */
$_XH_pluginMenu = new XH_ClassicPluginMenu();

/**
 * The currently loaded plugin.
 *
 * @global string $plugin
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
 * @global XH_CSRFProtection $_XH_csrfProtection
 */
$_XH_csrfProtection = new XH_CSRFProtection();

// LOGIN & BACKUP

$adm = gc('status') == 'adm' && logincheck();

$keycut = stsl($keycut);

if ($login && $keycut == '' && !$adm) {
    $login = null;
    $f = 'login';
}

if ($login && !$adm) {
    $_XH_controller->handleLogin();
} elseif ($logout && $adm) {
    $_XH_controller->handleLogout();
}

/**
 * Whether admin mode is active.
 *
 * @since 1.5.4
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#xh_adm
 * @see $adm
 */
define('XH_ADM', $adm);

if (XH_ADM) {
    include_once $pth['folder']['cmsimple'] . 'adminfuncs.php';
}

/*
 * Handle AJAX request to keep the admin session alive.
 */
if (XH_ADM && isset($_GET['xh_keep_alive'])) {
    session_start();
    header('Content-Type: text/plain');
    exit;
}

/*
 * Handle AJAX request to check the password.
 */
if (XH_ADM && isset($_GET['xh_check'])) {
    $_XH_controller->handlePasswordCheck();
}


// SETTING FUNCTIONS AS PERMITTED

if (XH_ADM) {
    $temp = 1000 * (ini_get('session.gc_maxlifetime') - 1);
    $o .= '<script type="text/javascript">/* <![CDATA[ */'
        . 'if (document.cookie.indexOf(\'status=adm\') == -1)'
        . ' document.write(\'<div class="xh_warning">'
        . $tx['error']['nocookies'] . '<\/div>\')'
        . '/* ]]> */</script>'
        . '<noscript><div class="xh_warning">'
        . $tx['error']['nojs'] . '</div></noscript>'
        . '<script type="text/javascript">/* <![CDATA[ */'
        . 'setInterval(function() {'
        . 'var request = new XMLHttpRequest();'
        . 'request.open("GET", "?xh_keep_alive"); request.send(null);'
        . '}, ' . $temp . ');'
        . '/* ]]> */</script>';
    if ($edit) {
        setcookie('mode', 'edit', 0, CMSIMPLE_ROOT);
    }
    if ($normal) {
        setcookie('mode', '', 0, CMSIMPLE_ROOT);
    }
    if (gc('mode') == 'edit' && !$normal) {
        $edit = true;
    }
} else {
    if (gc('status') != '') {
        setcookie('status', '', 0, CMSIMPLE_ROOT);
    }
    if (gc('mode') == 'edit') {
        setcookie('mode', '', 0, CMSIMPLE_ROOT);
    }
}

/**
 * The number of pages.
 *
 * @global int $cl
 */
$cl = 0;

/**
 * The page data router.
 *
 * @global XH_PageDataRouter $pd_router
 */
$pd_router = null;

/**
 * The index of the currently selected page.
 *
 * @global int $s
 */
$s = -1;

/**
 * The content of the pages.
 *
 * @global array $c
 */
$c = null;

/**
 * The headings of the pages.
 *
 * @global array $h
 *
 * @see h()
 */
$h = null;

/**
 * The URLs of the pages.
 *
 * @global array $u
 */
$u = null;

/**
 * The menu levels of the pages.
 *
 * @global array $l
 *
 * @see l()
 */
$l = null;

rfc(); // Here content is loaded

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


// If admin is logged in, generate fake output to suppress later adjustment of $s.
if (XH_ADM) {
    $o .= ' ';
}


if (XH_ADM) {
    $_XH_controller->handleMenumanager();
    $_XH_controller->handleSavePageData();
}

/**
 * The number of the currently selected page.
 *
 * @global int $pd_s
 */
$pd_s = $s == -1 && !$f && $o == '' && $su == '' ? 0 : $s;

/**
 * The infos about the current page.
 *
 * @global array $pd_current
 */
$pd_current = $pd_router->find_page($pd_s);

/**
 * The configuration of the plugins.
 *
 * @global array $plugin_cf
 */
$plugin_cf = array();
/**
 * The localization of the plugins.
 *
 * @global array $plugin_tx
 */
$plugin_tx = array();

/*
 * Include config and language files of all plugins.
 */
foreach (XH_plugins() as $plugin) {
    pluginFiles($plugin);
    $temp = XH_readConfiguration(true, false);
    $plugin_cf += $temp;
    XH_createLanguageFile($pth['file']['plugin_language']);
    $temp = XH_readConfiguration(true, true);
    $plugin_tx += $temp;
}

/*
 * Add LINK to combined plugin stylesheet.
 */
$hjs .= tag(
    'link rel="stylesheet" href="' . XH_pluginStylesheet() . '" type="text/css"'
) . PHP_EOL;

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
 * @global array $hc
 */
$hc = array();

/**
 * The length of {@link $hc}.
 *
 * @global int $hl
 */
$hl = -1;

/**
 * The index of the current page in {@link $hc}.
 *
 * @global int $si
 */
$si = -1;

XH_buildHc();

// LEGAL NOTICES - not needed under GPL3
if (empty($cf['menu']['legal'])) {
    $cf['menu']['legal'] = 'CMSimple Legal Notices';
}
if ($su == uenc($cf['menu']['legal'])) {
    $f = $title = $cf['menu']['legal'];
    $s = -1;
    $o .= '<h1>' . $title . '</h1>'
        . file_get_contents($pth['folder']['cmsimple'] . 'legal.txt');
}

if (XH_ADM) {
    $_XH_controller->setBackendF();

    if ($f == 'settings' || $f == 'xh_backups' || $f == 'images' || $f == 'downloads'
        || $f == 'validate' || $f == 'sysinfo' || $f == 'phpinfo'
    ) {
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
        include_once $pth['folder']['classes'] . 'LinkChecker.php';
        $temp = new XH_LinkChecker();
        $o .= ($f == 'validate') ? $temp->prepare() : $temp->doCheck();
        break;
    }
}


// fix $s
if ($s == -1 && !$f && $o == '' && $su == '') {
    $s = 0;
    $hs = 0;
}

if (XH_ADM && $f == 'save') {
    $_XH_csrfProtection->check();
    XH_saveEditorContents($text);
}

if (XH_ADM && $edit && (!$f || $f == 'save') && !$download) {
    if ($s > -1) {
        $o .= XH_contentEditor();
    } else {
        $o .= '<p>' . $tx['error']['cntlocateheading'] . '</p>' . "\n";
    }
}

if (XH_ADM && ($images || $downloads || $userfiles || $media || $edit
    && (!$f || $f == 'save') && !$download)
) {
    if ($cf['filebrowser']['external']
        && !file_exists($pth['folder']['plugins'] . $cf['filebrowser']['external'])
    ) {
        $temp = sprintf(
            $tx['error']['nofilebrowser'], $cf['filebrowser']['external']
        );
        $e .= '<li>' . $temp . '</li>' . "\n";
    }
}

if (XH_ADM && $f == 'xhpages') {
    if ($cf['pagemanager']['external']
        && !file_exists($pth['folder']['plugins'] . $cf['pagemanager']['external'])
    ) {
        $temp = sprintf(
            $tx['error']['nopagemanager'], $cf['pagemanager']['external']
        );
        $e .= '<li>' . $temp . '</li>' . "\n";
    }
}



// CMSimple scripting
/**
 * The output to be manipulated by CMSimple scripting.
 *
 * @global string $output
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
    shead('404');
}

loginforms();

if ($e) {
    $o = '<div class="xh_warning">' . "\n"
        . '<ul>' . "\n" . $e . '</ul>' . "\n" . '</div>' . "\n"
        . $o;
}
if ($title == '') {
    if ($s > -1) {
        $title = $h[$s];
    } elseif ($f != '') {
        // FIXME: check for duplication, i.e. isn't $title already set to $f?
        $title = ucfirst($f);
    }
}

if (!headers_sent($temp, $i)) {
    header('Content-Type: text/html; charset=UTF-8');
    header("Content-Language: $sl");
    if ($cf['security']['frame_options'] != '') {
        header('X-Frame-Options: ' . $cf['security']['frame_options']);
    }
} else {
    $temp .= ':' . $i;
    exit(str_replace('{location}', $temp, $tx['error']['headers']));
}

if ($print) {
    XH_builtinTemplate('print');
    //} elseif (strtolower($f) == 'login' || $f == 'forgotten') {
    //    XH_builtinTemplate('xh_login');
}

if (XH_ADM) {
    $bjs .= '<script type="text/javascript" src="' . $pth['file']['adminjs']
        . '"></script>' . PHP_EOL
        . XH_adminJSLocalization();
}

/*
 * Check if $adm was manipulated. If so, we present the login form.
 * Redirecting would be cleaner, but may result in a loop, so we do it this way.
 */
if (!XH_ADM && $adm) {
    $s = -1;
    $adm = $edit = false;
    $o = '';
    $f = 'login';
    $title = utf8_ucfirst($tx['menu']['login']);
    loginforms();
}

ob_start('XH_finalCleanUp');

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

$_XH_csrfProtection->store();

?>
