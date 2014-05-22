<?php

/*
 * @version $Id: updatecheck.php 237 2014-02-05 22:31:24Z hi $
 */

/*
 * ==================================================================
 * Update-Check-Plugin for CMSimple_XH
 * ==================================================================
 * Version:    1.2.1
 * Build:      2014020601
 * Copyright:  Holger Irmler
 * Email:      CMSimple@HolgerIrmler.de
 * Website:    http://CMSimple.HolgerIrmler.de
 * License:    GPL3
 * ==================================================================
 */

if (!isset($_SESSION))
    session_start();

if (isset($_POST['do_updatecheck']) && isset($_POST['pluginname'])) {
    echo hi_updateInfo($_POST['pluginname']);
    exit;
}

if (isset($_POST['do_quickcheck'])) {
    hi_updateQuickInfo();
    exit;
}


//necessary globals, if functions used directly in a plugin
global $cf, $hjs, $pth, $sl, $sn;

function hi_updateQuickCheck($pluginname) {
    global $cf, $plugin_tx, $pth, $sl, $sn;

    if ($pluginname == '' || isset($_SESSION['upd_available']))
        return;

    if ($pluginname == 'CMSimple_XH') {
        $versionStr = 'CMSimple_XH,'
                . CMSIMPLE_XH_VERSION
                . ',,,,,'
                . CMSIMPLE_XH_VERSIONINFO;
    } else {
        $versionStr = @file_get_contents($pth['folder']['plugins'] . $pluginname . '/version.nfo');
    }

    if ($versionStr != '') {
        include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
        include_jQuery();

        $url = str_replace('index.php', '', str_replace('/' . basename($sl) . '/', '/', $sn));
        $url .= 'plugins/hi_updatecheck/updatecheck.php';
        $data = array(
            'versionstr' => $versionStr,
            'do_quickcheck' => 1
        );
        $t = '<script type = "text/javascript">
                jQuery.ajax({
                    type: "POST",
                    url: "' . $url . '",
                    data: ' . json_encode($data) . ',
                    dataType: "html",
                    processData: true,
                    success: function(msg){}
                });
            </script>';
        return $t;
    }
}

function hi_updateQuickInfo() {
    if (!isset($_SESSION['xh_session']))
        return;
    $localVersion = hi_versionInfo($_POST['versionstr']);
    if (count($localVersion) !== 7)
        return;
    $versionStr = hi_fsFileGetContents(trim($localVersion[6]), 15);
    $remoteVersion = hi_versionInfo($versionStr);
    if (count($remoteVersion) !== 7)
        return;
    //Update available?
    if (version_compare($remoteVersion[1], $localVersion[1], '>')) {
        $_SESSION['upd_available'] = TRUE;
        return;
    }
}

function hi_updateCheck($pluginname = '', $single_check = 1) {
    global $cf, $hjs, $plugin_tx, $pth, $sl, $sn;

    if ($pluginname == '')
        return '';

    $p_tx = $plugin_tx['hi_updatecheck'];
    $p_pth = $pth['folder']['plugins'] . 'hi_updatecheck';

    if ($pluginname == 'CMSimple_XH') {
        $versionStr = 'CMSimple_XH,'
                . CMSIMPLE_XH_VERSION
                . ',,,,,'
                . CMSIMPLE_XH_VERSIONINFO;
    } else {
        $versionStr = @file_get_contents($pth['folder']['plugins'] . $pluginname . '/version.nfo');
    }

    if ($versionStr == '') {
        return hi_updateNoVersioninfo($pluginname);
    }
    include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
    include_jQuery();

    $token = md5(microtime() . mt_rand());
    $_SESSION['updtoken_' . $pluginname] = $token;

    $url = str_replace('index.php', '', str_replace('/' . basename($sl) . '/', '/', $sn));
    $url .= 'plugins/hi_updatecheck/updatecheck.php';
    $data = array(
        'token_' . $pluginname => $token,
        'pluginname' => $pluginname,
        'versionstr' => $versionStr,
        'do_updatecheck' => 1,
        'single_check' => $single_check
    );
    foreach ($p_tx as $key => $val) {
        $data[$key] = $val;
    }

    $t = '<div class="upd_container">';
    $t .= '<div id="upd_' . $pluginname . '_loading"><p>' .
            $p_tx['message_searching'] . ' ' . ucfirst($pluginname) . ' ...' . tag('br') .
            tag('img src="' . $p_pth . '/images/ajax-loader.gif" style="padding: 5px 0 15px 0;"') .
            '</p></div>';
    $t .= '<div id="upd_' . $pluginname . '_Info"></div>';
    $t .= '</div>';
    $t .= '<script type = "text/javascript">
                jQuery.ajax({
                    type: "POST",
                    url: "' . $url . '",
                    data: ' . json_encode($data) . ',
                    dataType: "html",
                    processData: true,  // I know that is the default-value, anyway
                    error: function() {
                        jQuery(\'#upd_' . $pluginname . '_loading\').css(\'display\',\'none\');
                        jQuery(\'#upd_' . $pluginname . '_Info\').html(\'Sorry, a problem has occurred while checking ' . $pluginname . '.\');
                    },
                    success: function(msg){
			jQuery(\'#upd_' . $pluginname . '_Info\').html(msg);
			jQuery(\'#upd_' . $pluginname . '_loading\').css(\'display\',\'none\');
                    }
                });
            </script>';
    return $t;
}

function hi_updateInfo($pluginname = '') {

    if ($_POST['token_' . $pluginname] !== $_SESSION['updtoken_' . $pluginname]
            || !$_POST['versionstr']
            || $pluginname == '')
        die('Access denied!');

    unset($_SESSION['updtoken_' . $pluginname]);
    $p_tx = array();
    $p_tx['message_download'] = $_POST['message_download'];
    $p_tx['message_fail'] = $_POST['message_fail'];
    $p_tx['message_up-to-date'] = $_POST['message_up-to-date'];
    $p_tx['message_update-available'] = $_POST['message_update-available'];
    $p_tx['message_update-critical'] = $_POST['message_update-critical'];

    //superfluous, but anyway
    foreach ($p_tx as $key => $val) {
        $p_tx[$key] = htmlspecialchars(strip_tags($val), ENT_QUOTES, 'UTF-8');
    }
    $local = hi_versionInfo($_POST['versionstr']);
    $localVersion = array();
    //superfluous, but anyway
    foreach ($local as $val) {
        $localVersion[] = htmlspecialchars(strip_tags($val), ENT_QUOTES, 'UTF-8');
    }
    $url = trim($localVersion[6]);
    if (strtolower(pathinfo($url, PATHINFO_EXTENSION)) != 'nfo') {
        die('Access denied!');
    }
    $remote = hi_fsFileGetContents($url, 15);
    $remote = hi_versionInfo($remote);
    $remoteVersion = array();
    if ($remote) {
        foreach ($remote as $val) {
            //sanitize
            $remoteVersion[] = htmlspecialchars(strip_tags($val), ENT_QUOTES, 'UTF-8');
        }
    }
    //set defaults
    $css_suff = '';
    if ($_POST['single_check'] != '1')
        $css_suff .= '_list'; //change CSS-Classes, if not called from another plugin

    $css_class = 'upd_error' . $css_suff;
    $msg = '<b>' . sprintf($p_tx['message_fail'], ucfirst(htmlspecialchars(strip_tags($pluginname), ENT_QUOTES, 'UTF-8'))) . '</b>';
    //compare versions
    if (!$remoteVersion || !$localVersion) {
        return sprintf('<div class="%s">%s</div>', $css_class, $msg);
//    } elseif ((int) $remoteVersion[1] == (int) $localVersion[1]) {
    } elseif (version_compare($remoteVersion[1], $localVersion[1], '=')) {
        //up-to-date
        $msg = '<b>' . $localVersion[0] . ' ' . $localVersion[2] . ' ' . $p_tx['message_up-to-date'] . '</b>';
        $css_class = 'upd_success' . $css_suff;
    } elseif (version_compare($remoteVersion[1], $localVersion[1], '>')) {
        //update
        $msg = '<b>' . sprintf($p_tx['message_update-available'], $localVersion[0], $remoteVersion[2]) . '</b>';
        $css_class = 'upd_info' . $css_suff;
        $_SESSION['upd_available'] = TRUE; //show notification to the user
        //critical?
        if (version_compare($localVersion[1], $remoteVersion[3], '<=')) {
            $msg .= '<p><b>' . $p_tx['message_update-critical'] . '</b></p>';
            $css_class = 'upd_warning' . $css_suff;
        }
        //other hints?
        if (trim($remoteVersion[4]) != '') {
            $msg .= '<p>' . $remoteVersion[4] . '</p>';
        }
        $msg .= '<p><a target="_blank" href="' . $remoteVersion[5] . '">' . $p_tx['message_download'] . '</a></p>';
    }
    //output
    return sprintf('<div class="%s">%s</div>', $css_class, $msg);
}

function hi_updateNoVersioninfo($pluginname = '') {
    global $plugin_tx;
    $p_tx = $plugin_tx['hi_updatecheck'];

    $css_class = 'upd_noinfo_list';
    $msg = '<b>' . sprintf($p_tx['message_no-versioninfo1'], ucfirst($pluginname), ENT_QUOTES, 'UTF-8') . '</b>';
    $msg .= tag('br');
    $msg .= $p_tx['message_no-versioninfo2'];
    return sprintf('<div class="upd_container"><div class="%s">%s</div></div>', $css_class, $msg);
}

function hi_updateInstalledScripts() {
    global $plugin_cf, $pluginloader_cfg, $pth;
    $ignore = array();

    //ignore default-plugins, defined in config.php
    $ignore = explode(',', $plugin_cf['hi_updatecheck']['ignore']);
    $handle = opendir($pth['folder']['plugins']);
    if ($handle) {
        while ($installed_plugin = readdir($handle)) {
            if (strpos($installed_plugin, '.') === false
                    && $installed_plugin != $pluginloader_cfg['foldername_pluginloader']
                    && is_dir($pth['folder']['plugins'] . $installed_plugin)
                    && !in_array(strtolower($installed_plugin), $ignore)) {
                $installed_plugins[] = $installed_plugin;
            }
        }
        closedir($handle);
    }
    return $installed_plugins;
}

/*
 * Convert CSV to array
 */

function hi_versionInfo($versionStr = FALSE) {

    if (!$versionStr) {
        return FALSE; //Error
    }
    $versionInfo = array();
    $versionInfo = explode(',', $versionStr);
    if (count($versionInfo) !== 7) {
        return FALSE; //Error
    }
    return $versionInfo;
}

/*
 * Get contents of a file with fsockopen()
 * initial written by tleilax / klamm.de
 */

function hi_fsFileGetContents($url, $timeout = 30) {
    // split URL
    $parsedurl = @parse_url($url);
    // determine host, catch invalid calls
    if (empty($parsedurl['host']))
        return null;
    $host = $parsedurl['host'];
    // determine path
    $documentpath = empty($parsedurl['path']) ? '/' : $documentpath = $parsedurl['path'];
    // determine params
    if (!empty($parsedurl['query']))
        $documentpath .= '?' . $parsedurl['query'];
    // determine port
    $port = empty($parsedurl['port']) ? 80 : $port = $parsedurl['port'];

    // open socket
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$fp)
        return null;

    // send request
    $request = "GET {$documentpath} HTTP/1.0\r\n";
    $request .= "Host: {$host}\r\n";
    $request .= "User-Agent: hi_UpdateChecker\r\n\r\n";
    fputs($fp, $request);

    // read header
    $header = '';
    do {
        $line = rtrim(fgets($fp));
        $header .= $line . "\n";
    } while (!empty($line) and !feof($fp));
    // read data
    $result = '';
    while (!feof($fp)) {
        $result .= fgets($fp);
    }
    // close socket
    fclose($fp);

    // evaluate header
    preg_match('~^HTTP/1\.\d (?P<status>\d+)~', $header, $matches);
    $status = $matches['status'];
    if ($status == 200) { // OK
        return $result;
    } elseif ($status == 204 or $status == 304) { // No Content | Not modified
        return '';
    } elseif (in_array($status, Array(300, 301, 302, 303, 307))) {
        preg_match('~Location: (?P<location>\S+)~', $header, $match);
        $result = hi_fsFileGetContents($match['location'], $timeout);
    } elseif ($status >= 400) { // Any error
        return false;
    }

    // return reult
    return $result;
}

if (!function_exists('json_encode')) {
// make sure the class wasn't already included by another plugin
    if (!class_exists('CMB_JSON')) {
        include_once $pth['folder']['plugins'] . 'hi_updatecheck/JSON.php';
    }

    /* function json_encode($value) {
      return CMB_JSON::instance()->encode($value);
      }
     */

    function json_encode($value) {
        $json = CMB_JSON::instance();
        return $json->encode($value);
    }

}