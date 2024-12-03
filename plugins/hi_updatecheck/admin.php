<?php

/*
 * @version $Id: admin.php 257 2015-03-14 17:01:30Z hi $
 */

/*
 * ==================================================================
 * Update-Check-Plugin for CMSimple_XH
 * ==================================================================
 * Version:    1.6.1
 * Build:      2024120301
 * Copyright:  Holger Irmler
 * Email:      CMSimple@HolgerIrmler.de
 * Website:    http://CMSimple.HolgerIrmler.de
 * Copyright:  CMSimple_XH developers
 * Website:    https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team
 * License:    GPL3
 * ==================================================================
 */

if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

if (defined('CMSIMPLE_RELEASE')) {
    $o .= '<div class="upd_error">'
            . 'Sorry, Update-Check-Plugin can only work with the original CMSimple_XH from '
            . '<a target="_blank" href="https://www.cmsimple-xh.org">www.cmsimple-xh.org.</a><br>'
            . 'Please delete the folder /plugins/hi_updatecheck/ from your installation.'
            . '</div>';
    return;
}

define('UPD_VERSION', '1.6.1');
define('UPD_DATE', '2024-12-03');

//Path to core-Versioninfo
define('CMSIMPLE_XH_VERSIONINFO', 'https://www.cmsimple-xh.org/userfiles/downloads/versioninfo/cmsimple_xh-version.nfo');

include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
include_jQuery();

include_once($pth['folder']['plugins'] . 'hi_updatecheck/updatecheck.php');

//Add hidden info-icon to editmenu
$o .= upd_addMenuEntry();

if (isset($_POST['do_updatecheck']) && isset($_POST['pluginname'])) {
    header('Content-Type:text/html; charset=UTF-8');
    echo hi_updateInfo($_POST['pluginname']);
    exit;
}

if (isset($_POST['do_quickcheck'])) {
    hi_updateQuickInfo();
    exit;
}

//Quick-Check, only once per session
if (!isset($_SESSION['upd_checked']) && $plugin_cf['hi_updatecheck']['autocheck'] == 'true') {
    hi_updateSetStatus();
}

if (isset($_SESSION['upd_available'])) {
    hi_updateNotify();
}

/*
 * Register the plugin menu items.
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(true);
}

/*
 * Handle the plugin administration.
 */
if (function_exists('XH_wantsPluginAdministration')
    && XH_wantsPluginAdministration('hi_updatecheck')
    || isset($hi_updatecheck) && $hi_updatecheck == 'true'
) {
    $o .= print_plugin_admin('on');
    switch ($admin) {
        case '':
            $o .= hi_updateVersion();
            break;
        case 'plugin_main':
            $o .= hi_updateCheckAll();
            break;
        default:
            $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

/**
 * Returns the plugin version information view.
 *
 * @return string  The HTML.
 */
function hi_updateVersion() {
    global $pth;

    return '<h1>CMSimple_XH - Update-Check</h1>' . "\n"
            . '<img src="' . $pth['folder']['plugins'] . 'hi_updatecheck/images/software-update-icon.png" class="upd_plugin_icon">'
            . '<p>Version: ' . UPD_VERSION . ' - ' . UPD_DATE . '</p>' . "\n"
            . '<p>Copyright &copy;2013-2023 <a href="http://cmsimple.holgerirmler.de/">Holger Irmler</a> - all rights reserved<br>'
            . '<p>Copyright &copy;2014 <a href="https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team">CMSimple_XH developers</a><br>'
            . '<p class="upd_license">License: GPL3</p>' . "\n"
            . '<p class="upd_license">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR'
            . ' IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,'
            . ' FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE'
            . ' AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER'
            . ' LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,'
            . ' OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE'
            . ' SOFTWARE.</p>' . "\n";
}
