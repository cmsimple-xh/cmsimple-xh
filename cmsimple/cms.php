<?php

/**
 * @file cms.php
 *
 * The main file of CMSimple_XH.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
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
const XH_URICHAR_SEPARATOR = '|';

/**
 * The title of the current page.
 *
 * This *read-write* variable can be used to set the page title in the
 * plugin administration and for special extension pages.
 *
 * @var string $title
 *
 * @public
 */
$title = '';

/**
 * The HTML for the contents area.
 *
 * This *read-write* variable is used to buffer the output, which is
 * prepended to the contents of the current page (if any). Usually you will
 * only append to this variable.
 *
 * @var string $o
 *
 * @public
 */
$o = '';

/**
 * The HTML for the `<li>`s holding error messages.
 *
 * This *read-write* variable can be used to add error messages above the
 * content. Usually you will only append to this variable.
 *
 * @var string $e
 *
 * @public
 *
 * @see e()
 */
$e = '';

/**
 * HTML that will be inserted to the `<head>` section.
 *
 * This *read-write* variable can be used to add script, style, meta and link
 * elements etc. to the head element. Usually you will only append to this variable.
 *
 * @var string $hjs
 *
 * @public
 *
 * @see $bjs
 */
$hjs = '';

/**
 * HTML that will be inserted right before the `</body>` tag.
 *
 * This *read-write* variable can be used to add script elements to the end
 * of the body element. Usually you will only append to this variable.
 *
 * @var string $bjs
 *
 * @public
 *
 * @see $hjs
 * @see http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#bjs
 *
 * @since 1.5.4
 */
$bjs = '';

/**
 * JavaScript for the onload attribute of the body element.
 *
 * This *read-write* variable can be used to register window onload event
 * handlers. Usually you will only append to this variable.
 *
 * @var string $onload
 *
 * @public
 */
$onload = '';

/**
 * A temporary value.
 *
 * This *read-write* variable can be used to avoid polluting the global scope.
 *
 * @var mixed $temp
 *
 * @public
 */
$temp = null;

/**
 * A temporary (loop) value.
 *
 * This *read-write* variable can be used to avoid polluting the global scope.
 *
 * @var mixed $i
 *
 * @public
 */
$i = null;

/**
 * A temporary (loop) value.
 *
 * This *read-write* variable can be used to avoid polluting the global scope.
 *
 * @var mixed $j
 *
 * @public
 */
$j = null;

/**
 * The version in textual representation, e.g. CMSimple_XH 1.6
 */
const CMSIMPLE_XH_VERSION = '@CMSIMPLE_XH_VERSION@';
/**
 * The build number as integer: YYYYMMDDBB
 */
const CMSIMPLE_XH_BUILD = '@CMSIMPLE_XH_BUILD@';
/**
 * The release date in ISO 8601 format: YYYY-MM-DD
 */
const CMSIMPLE_XH_DATE = '@CMSIMPLE_XH_DATE@';

/**
 * A two dimensional array that holds the paths of important files and folders.
 *
 * Should be treated as *read-only*.
 *
 * @var array $pth
 *
 * @public
 *
 * @see http://www.cmsimple-xh.org/wiki/doku.php/core_variables#pth
 */
$pth = array();
$pth['file']['execute'] = './index.php';

$pth['folder']['base'] = is_dir('./cmsimple') ? './' : '../';

$pth['folder']['cmsimple'] = $pth['folder']['base'] . 'cmsimple/';
$pth['folder']['classes'] = $pth['folder']['cmsimple'] . 'classes/';
$pth['folder']['plugins'] = $pth['folder']['base'] . 'plugins/';

$pth['file']['log'] = $pth['folder']['cmsimple'] . 'log.txt';
$pth['file']['debug-log'] = $pth['folder']['cmsimple'] . 'debug-log.txt';
$pth['file']['cms'] = $pth['folder']['cmsimple'] . 'cms.php';
$pth['file']['config'] = $pth['folder']['cmsimple'] . 'config.php';

// include general utility functions and classes
require_once $pth['folder']['cmsimple'] . 'functions.php';
spl_autoload_register('XH_autoload');
require_once $pth['folder']['cmsimple'] . 'tplfuncs.php';
require_once $pth['folder']['cmsimple'] . 'utf8.php';
if (!function_exists('random_bytes')) {
    include_once $pth['folder']['cmsimple'] . 'password.php';
}
require_once $pth['folder']['cmsimple'] . 'seofuncs.php';

/**
 * The controller.
 *
 * @var XH\Controller $_XH_controller
 *
 * @private
 */
$_XH_controller = new XH\Controller();

/**
 * The configuration of the core.
 *
 * Should be treated as *read-only*.
 *
 * @var array $cf
 *
 * @public
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
 * @var array $errors
 *
 * @private
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
 * This *read-only* variable contains an ISO 639-1 language code.
 *
 * @var string $sl
 *
 * @public
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
 * Should be treated as *read-only*.
 *
 * @var array $tx
 *
 * @public
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
 * @var array $txc
 *
 * @public
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
 * Should be treated as *read-only*.
 *
 * @public
 *
 * @var bool $iis
 */
$iis = strpos(sv('SERVER_SOFTWARE'), "IIS");

/**
 * Whether PHP is executed as (F)CGI.
 *
 * Should be treated as *read-only*.
 *
 * @public
 *
 * @var bool $cgi
 */
$cgi = (php_sapi_name() == 'cgi' || php_sapi_name() == 'cgi-fcgi');

/**
 * The relative path of the root folder, i.e. the script name.
 *
 * Should be treated as *read-only*.
 *
 * @var string $sn
 *
 * @public
 *
 * @see CMSIMPLE_URL
 */
$sn = preg_replace('/([^\?]*)\?.*/', '$1', sv(($iis ? 'SCRIPT_NAME' : 'REQUEST_URI')));

/**
 * The requested plugin administration part.
 *
 * This *read-only* variable is initialized from an `admin`
 * GET/POST parameter, and is usually used in combination with
 * {@link $action} to request some functionality of a plugin back-end.
 *
 * @var string $admin
 *
 * @public
 */
$admin = null;

/**
 * The requested action.
 *
 * This *read-only* variable is initialized from an `action`
 * GET/POST parameter, and is usually used in combination with
 * {@link $admin} to request some functionality of a plugin back-end.
 *
 * @var string $action
 *
 * @public
 */
$action = null;

/**
 * The requested function.
 *
 * This variable is set from a `function` GET/POST parameter, which
 * denotes some special functionality. If set from your extension treat it as
 * *read-write*; otherwise ignore it.
 *
 * @var string $function
 *
 * @public
 */
$function = null;

/**
 * Whether login is requested.
 *
 * This variable is initialized from a `login` GET/POST parameter.
 * If the login has been successful, {@link $f} == 'login'; otherwise {@link $f}
 * == 'xh_login_failed' or == 'xh_login_pw_expired'.
 *
 * @var string $login
 *
 * @private
 */
$login = null;

/**
 * The admin password.
 *
 * This variable is initialized from a `keycut` GET/POST parameter.
 *
 * This variable has been renamed from `$passwd` since CMSimple_XH 1.6
 * to avoid trouble with mod_security.
 *
 * @var string $keycut
 *
 * @private
 */
$keycut = null;

/**
 * Whether logout is requested.
 *
 * This variable is initialized from a `logout` GET/POST parameter.
 * On logout {@link $f} == 'xh_loggedout'.
 *
 * @var string $logout
 *
 * @private
 */
$logout = null;

/**
 * Whether the mailform is requested.
 *
 * This variable is initialized from a `mailform` GET/POST parameter.
 * If the mailform has been requested {@link $f} == 'mailform'.
 *
 * @var string $mailform
 *
 * @private
 */
$mailform = null;

/**
 * The filename requested for download.
 *
 * This variable is initialized from a `download` GET/POST parameter.
 *
 * @var string $download
 *
 * @private
 */
$download = null;

/**
 * Whether the file browser is requested to show the download folder.
 *
 * This variable is initialized from a `downloads` GET/POST parameter,
 * and should only be used by file browsers and similar extensions, which may
 * treat it as *read-write*.
 *
 * @var string $downloads
 *
 * @public
 */
$downloads = null;

/**
 * Whether the file browser is requested to show the image folder.
 *
 * This variable is initialized from a `images` GET/POST parameter,
 * and should only be used by file browsers and similar extensions, which may
 * treat it as *read-write*.
 *
 * @var string $images
 *
 * @public
 */
$images = null;

/**
 * Whether the file browser is requested to show the media folder.
 *
 * This variable is initialized from a `media` GET/POST parameter,
 * and should only be used by file browsers and similar extensions, which may
 * treat it as *read-write*.
 *
 * @var string $media
 *
 * @public
 */
$media = null;

/**
 * Whether the file browser is requested to show the userfiles folder.
 *
 * This variable is initialized from a `userfiles` GET/POST parameter,
 * and should only be used by file browsers and similar extensions, which may
 * treat it as *read-write*.
 *
 * @var string $userfiles
 *
 * @public
 */
$userfiles = null;

/**
 * Whether edit mode is requested.
 *
 * This *read-only* variable is initialized from an `edit`
 * GET/POST parameter or the `mode` cookie. If you want to switch to
 * edit mode, set the `edit` GET parameter.
 *
 * @var string $edit
 *
 * @public
 *
 * @see $normal
 */
$edit = null;

/**
 * Whether normal (aka view) mode is requested.
 *
 * This *read-only* variable is initialized from a `normal`
 * GET/POST parameter, but not from the `mode` cookie. If you want to
 * detect normal mode, check for `!$edit`. If you want to switch to
 * normal mode, set the `normal` GET parameter.
 *
 * @var string $normal
 *
 * @public
 *
 * @see $edit
 */
$normal = null;

/**
 * Whether print mode is requested.
 *
 * This *read-only* variable is initialized from a `print` GET/POST
 * parameter.
 *
 * @var string $print
 *
 * @public
 */
$print = null;

/**
 * The name of a special file to be handled in the back-end.
 *
 * This variable is initialized from a `file` GET/POST parameter.
 *
 * @var string $file
 *
 * @private
 */
$file = null;

/**
 * The current search string.
 *
 * This *read-only* variable is initialized from a `search`
 * GET/POST parameter.
 *
 * @var string $search
 *
 * @public
 */
$search = null;

/**
 * The URL of the requested page.
 *
 * This variable is initialized from a `selected` GET/POST parameter.
 * If present {@link $su} is set accordingly.
 *
 * @var string $selected
 *
 * @private
 */
$selected = null;

/**
 * Whether the settings page is requested.
 *
 * This variable is initialized from a `settings` GET/POST parameter.
 *
 * @var string $settings
 *
 * @private
 */
$settings = null;

/**
 * Whether the sitemap is requested.
 *
 * This variable is initialized from a `sitemap` GET/POST parameter.
 * If the sitemap is requested {@link $f} == 'sitemap'.
 *
 * @var string $sitemap
 *
 * @private
 */
$sitemap = null;

/**
 * The text of the editor on save.
 *
 * This variable is initialized from a `text` GET/POST parameter.
 *
 * @var string $text
 *
 * @private
 */
$text = null;

/**
 * Whether the link check is requested.
 *
 * This variable is initialized from a `validate` GET/POST parameter.
 *
 * @var string $validate
 *
 * @private
 */
$validate = null;

/**
 * Whether the page manager is requested.
 *
 * This variable is initialized from a `xhpages` GET/POST parameter,
 * and should only be used by page managers, which may treat it as
 * *read-write*.
 *
 * @var string $xhpages
 *
 * @public
 *
 * @see http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#page_managers
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
 * Should be treated as *read-only*.
 *
 * @var string $su
 *
 * @public
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
    if ($su == '') {
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
    download($pth['folder']['downloads'] . basename($download));
}

$pth['file']['search'] = $pth['folder']['cmsimple'] . 'search.php';
$pth['file']['mailform'] = $pth['folder']['cmsimple'] . 'mailform.php';

/**
 * Whether admin mode is active.
 *
 * This variable is strictly *read-only*.
 *
 * @var bool $adm
 *
 * @public
 *
 * @see XH_ADM
 */
$adm = 0;

/**
 * The requested function.
 *
 * This *read-write* variable is initialized from different GET/POST
 * parameters. Usually you will want to treat it as *read-only* or even as
 * *private*.
 *
 * @var string $f
 *
 * @public
 */
$f = '';

/**
 * The plugin menu builder.
 *
 * @var XH\ClassicPluginMenu $_XH_pluginMenu
 *
 * @private
 */
$_XH_pluginMenu = new XH\ClassicPluginMenu();

/**
 * The currently loaded plugin.
 *
 * Should be treated as *read-only*.
 *
 * @var string $plugin
 *
 * @public
 */
$plugin = null;

/*
 * Include required_classes of all plugins.
 */
foreach (XH_plugins() as $plugin) {
    pluginFiles($plugin);
    // @phan-suppress-next-line PhanTypeMismatchArgumentInternalReal
    if (is_readable($pth['file']['plugin_classes'])) {
        include_once $pth['file']['plugin_classes'];
    }
}

/**
 * The CRSF protection object.
 *
 * Should be treated as *read-only*.
 *
 * @var XH\CSRFProtection $_XH_csrfProtection
 *
 * @public
 *
 * @see @ref csrf
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
 * @see $adm
 * @see http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#xh_adm
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
 * Treat as *read-only*.
 *
 * @var int $cl
 *
 * @public
 */
$cl = 0;

/**
 * The page data router.
 *
 * Treat as *read-only*.
 *
 * @var XH\PageDataRouter $pd_router
 *
 * @public
 */
$pd_router = null;

/**
 * The publisher instance.
 *
 * @var XH::Publisher $xh_publisher
 *
 * @public
 *
 * @since 1.7.0
 */
$xh_publisher = null;

/**
 * The index of the currently requested page.
 *
 * Treat as *read-only*. Note that `$s` is not properly set for the start
 * page until all plugins are loaded. If you need the know the index of the
 * currently requested page during plugin loading, consider to use {@link $pd_s}.
 *
 * @var int $s
 *
 * @public
 */
$s = -1;

/**
 * The content of the pages.
 *
 * Treat as *read-only* when in edit mode.
 *
 * @var array $c
 *
 * @public
 */
$c = null;

/**
 * The headings of the pages.
 *
 * Treat as *read-only*.
 *
 * @var array $h
 *
 * @public
 *
 * @see h()
 */
$h = null;

/**
 * The URLs of the pages.
 *
 * Treat as *read-only*.
 *
 * @var array $u
 *
 * @public
 *
 * @see $su
 */
$u = null;

/**
 * The menu levels of the pages.
 *
 * Treat as *read-only*.
 *
 * @var array $l
 *
 * @public
 *
 * @see l()
 */
$l = null;

/**
 * Optionally publish other content.
 *
 * Selection by entry in the language file.
 *
 * @since 1.8
*/
if (!XH_ADM
&& $tx['publish']['current'] != ''
&& is_readable($pth['folder']['content'] . $tx['publish']['current'])) {
    $pth['file']['content'] = $pth['folder']['content'] . $tx['publish']['current'];
}

rfc(); // Here content is loaded
assert(is_array($h));
assert(is_array($c));
assert($pd_router instanceof \XH\PageDataRouter);
assert($xh_publisher instanceof \XH\Publisher);

/*
 * Remove $su from FirstPublicPage
 * Remove empty path segments in an URL - https://github.com/cmsimple-xh/cmsimple-xh/issues/282
 * Integration of external plugin with extended functions (optional).
*/
XH_URI_Cleaning();

/*
 * Prevents a blank page in the backend when calling a URI without query string
*/
if (XH_ADM
&& $_SERVER['QUERY_STRING'] == ''
&& !$_POST) {
    header('Location:' . CMSIMPLE_URL
                       . '?'
                       . $u[$xh_publisher->getFirstPublishedPage()],
    true, 302);
    exit;
}

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
const PLUGINLOADER = true;

/**
 * For compatibility with plugins.
 */
const PLUGINLOADER_VERSION = 2.111;

/**
 * A unique prefix for autogenerated forms.
 *
 * @see http://forum.cmsimple-xh.dk/?f=12&t=4956#p25550
 */
const XH_FORM_NAMESPACE = 'PL3bbeec384_';

if (XH_ADM) {
    $o .= ' '; // generate fake output to suppress later adjustment of $s
    if ($_XH_controller->wantsSavePageData()) {
        $_XH_controller->handleSavePageData();
    }
}

/**
 * The index of the currently requested page.
 *
 * Treat as *read-only*. Note that the index of the currently requested page
 * is available in {@link $s} after the plugins have been loaded. During plugin
 * loading you may use $pd_s, but this is not guaranteed to be correct, as it
 * may be set to 0, even if `$s` might later be -1.
 *
 * @var int $pd_s
 *
 * @public
 *
 * @see $s
 */
$pd_s = ($s == -1 && !$f && $o == '' && $su == '') ? $xh_publisher->getFirstPublishedPage() : $s;

/**
 * The infos about the current page.
 *
 * Treat as *read-only*.
 *
 * @var array $pd_current
 *
 * @public
 */
$pd_current = $pd_router->find_page($pd_s);

/**
 * The configuration of the plugins.
 *
 * Treat as *read-only*.
 *
 * @var XH\PluginConfig $plugin_cf
 *
 * @public
 *
 * @see $cf
 */
$plugin_cf = new XH\PluginConfig();

/**
 * The localization of the plugins.
 *
 * Treat as *read-only*.
 *
 * @var XH\PluginConfig $plugin_tx
 *
 * @public
 *
 * @see $tx
 */
$plugin_tx = new XH\PluginConfig(true);

/*
 * @var array $CanonicalLinkInc
 *
 * Gives the possibility for plugins to publish get parameters
 * that should be included in the canonical link.
 *
 * @public
 */
$CanonicalLinkInc = array();

/*
 * Include index.php of all plugins.
 */
foreach (XH_plugins() as $plugin) {
    pluginFiles($plugin);
    // @phan-suppress-next-line PhanTypeMismatchArgumentInternalReal
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
        // @phan-suppress-next-line PhanTypeMismatchArgumentInternalReal
        if (is_readable($pth['file']['plugin_admin'])) {
                include $pth['file']['plugin_admin'];
        }
    }
    $o .= $pd_router->create_tabs($s);
}

unset($plugin);

XH_afterPluginLoading();

/**
 * Returns the canonical link element
 *
 * @return string
 *
 * @since 1.8.0
 */
if ($cf['canonical']['link']
&& !XH_ADM
&& !isset($_GET['print'])) {
    $hjs .= XH_canonicalLink();
}

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
 * This *read-only* variable can be used to build a menu with {@link li()}.
 *
 * @var array $hc
 *
 * @public
 */
$hc = array();

/**
 * The length of {@link $hc}.
 *
 * @var int $hl
 *
 * @private
 */
$hl = -1;

/**
 * The index of the current page in {@link $hc}.
 *
 * @var int $si
 *
 * @private
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
            assert(is_string($file));
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
                    case 'delete':
                        $_XH_csrfProtection->check();
                        XH_delete($pth['file'][$file]);
                        break;
                    default:
                        $_XH_controller->handleFileEdit();
                }
            }
            break;
        case 'validate':
        case 'do_validate':
            $temp = new XH\LinkChecker();
            if ($f == 'validate') {
                $o .= $temp->prepare();
            } else {
                $temp->doCheck();
            }
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
 * @var string $output
 *
 * @public
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
} elseif (in_array($f, array('login',
                             'xh_login_failed',
                             'xh_login_pw_expired',
                             'forgotten'))) {
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
        try {
            $i = include $pth['file']['template'];
        } catch (Throwable $t) {
            ob_clean();
            $tplError = 'Error-Message: ' . $t->getMessage() . '<br>'
                      . PHP_EOL
                      . 'File: ' . pathinfo($t->getFile())['basename'] . '<br>'
                      . PHP_EOL
                      . 'Line: ' . $t->getLine()
                      . PHP_EOL;
        }
        XH_lockFile($temp, LOCK_UN);
    }
    fclose($temp);
} else {
    $tplError = $pth['file']['template'] . ' not found' . PHP_EOL;
}
if (!$i) {// the template could not be included
    XH_emergencyTemplate($tplError);
}

if (isset($_XH_csrfProtection)) {
    $_XH_csrfProtection->store();
}

echo XH_finalCleanUp(ob_get_clean());
