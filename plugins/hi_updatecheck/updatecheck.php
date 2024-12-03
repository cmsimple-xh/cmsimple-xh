<?php

/*
 * @version $Id: updatecheck.php 261 2016-01-10 17:58:05Z hi $
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

function hi_updateQuickCheck($pluginname) {
    global $pth;

    if ($pluginname == '' || isset($_SESSION['upd_available']))
        return;

    if ($pluginname == 'CMSimple_XH') {
        $versionStr = 'CMSimple_XH,'
                . CMSIMPLE_XH_VERSION
                . ',,,,,'
                . CMSIMPLE_XH_VERSIONINFO;
    } else {
        $versionStr = @file_get_contents($pth['folder']['plugins']
                        . $pluginname . '/version.nfo');
    }

    if ($versionStr != '') {
        include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
        include_jQuery();

        $url = CMSIMPLE_URL;
        $data = array(
            'versionstr' => $versionStr,
            'do_quickcheck' => 1
        );
        $t = '<script>
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
    global $plugin_cf;

    $remoteKey = 1;
    $compare = '<';

    try {
        $localVersion = hi_versionInfo($_POST['versionstr']);
    } catch (RuntimeException $ex) {
        $localVersion = null;
    }
    if (count($localVersion) !== 7) {
        return;
    }
    $timeout = intval($plugin_cf['hi_updatecheck']['autocheck_timeout']);
    try {
        $versionStr = hi_fsFileGetContents(trim($localVersion[6]), $timeout);
        $remoteVersion = hi_versionInfo($versionStr);
    } catch (RuntimeException $ex) {
        $versionStr = $remoteVersion = null;
    }
    if (count($remoteVersion) !== 7) {
        return;
    }
    if ($plugin_cf['hi_updatecheck']['autocheck_notify'] == 'Only critical updates') {
        $remoteKey = 3;
        $compare = '<=';
    }
    //Update available?
    if (version_compare($localVersion[1], $remoteVersion[$remoteKey], $compare)) {
        $_SESSION['upd_available'] = TRUE;
        return;
    }
}

function hi_updateCheck($pluginname = '', $single_check = 1) {
    global $plugin_tx, $pth;

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
        $versionStr = @file_get_contents($pth['folder']['plugins']
                        . $pluginname . '/version.nfo');
    }

    if ($versionStr == '') {
        return hi_updateNoVersioninfo($pluginname);
    }
    include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
    include_jQuery();

    $token = md5(microtime() . mt_rand());
    $_SESSION['updtoken_' . $pluginname] = $token;

    $url = CMSIMPLE_URL;
    $data = array(
        'token_' . $pluginname => $token,
        'pluginname' => $pluginname,
        'versionstr' => $versionStr,
        'do_updatecheck' => 1,
        'single_check' => $single_check
    );
    $t = '<div class="upd_container">';
    $t .= '<div id="upd_'
        . $pluginname
        . '_loading"><p>'
        . $p_tx['message_searching']
        . ' '
        . ucfirst($pluginname)
        . ' ...<br><img src="'
        . $p_pth
        . '/images/ajax-loader.gif" style="padding: 5px 0 15px 0;"></p></div>';
    $t .= '<div id="upd_' . $pluginname . '_Info"></div>';
    $t .= '</div>';
    $t .= '<script>
                jQuery.ajax({
                    type: "POST",
                    url: "' . $url . '",
                    data: ' . json_encode($data) . ',
                    dataType: "html",
                    processData: true,  // I know that is the default-value...
                    error: function() {
                        jQuery(\'#upd_' . $pluginname . '_loading\')
                            .css(\'display\',\'none\');
                        jQuery(\'#upd_' . $pluginname . '_Info\').html(\'Sorry, a problem has occurred while checking ' . $pluginname . '.\');
                    },
                    success: function(msg){
            jQuery(\'#upd_' . $pluginname . '_Info\').html(msg);
            jQuery(\'#upd_' . $pluginname . '_loading\')
                            .css(\'display\',\'none\');
                    }
                });
            </script>';
    return $t;
}

function hi_updateInfo($pluginname = '') {
    global $plugin_tx;

    if ($_POST['token_' . $pluginname] !== $_SESSION['updtoken_' . $pluginname] || !$_POST['versionstr'] || $pluginname == '') {
        die('Access denied!');
    }

    unset($_SESSION['updtoken_' . $pluginname]);

    $p_tx = $plugin_tx['hi_updatecheck'];

    //superfluous, but anyway
    foreach ($p_tx as $key => $val) {
        $p_tx[$key] = htmlspecialchars(strip_tags($val), ENT_QUOTES, 'UTF-8');
    }
    try {
        $local = hi_versionInfo($_POST['versionstr']);
    } catch (RuntimeException $ex) {
        $local = null;
    }
    $localVersion = array();
    //superfluous, but anyway
    foreach ($local as $val) {
        $localVersion[] = htmlspecialchars(strip_tags($val), ENT_QUOTES, 'UTF-8');
    }
    $url = trim($localVersion[6]);
    if (strtolower(pathinfo($url, PATHINFO_EXTENSION)) != 'nfo') {
        die('Access denied!');
    }
    $fail_reason = 'unknown';
    $remoteVersion = array();
    try {
        $remote = hi_fsFileGetContents($url, 15);
        $remote = hi_versionInfo($remote);
        if ($remote) {
            foreach ($remote as $val) {
                //sanitize
                $remoteVersion[] = htmlspecialchars(strip_tags($val), ENT_QUOTES, 'UTF-8');
            }
        }
    } catch (RuntimeException $ex) {
        $fail_reason = $ex->getMessage();
    }
    //set defaults
    $css_suff = '';
    if ($_POST['single_check'] != '1')
        $css_suff .= '_list'; //change CSS-Classes, if not called from another plugin

    $css_class = 'upd_error' . $css_suff;
    $msg = '<b>' . sprintf($p_tx['message_fail'], ucfirst(htmlspecialchars(strip_tags($pluginname), ENT_QUOTES, 'UTF-8'))) . '</b>'
        . '<br>' . sprintf($p_tx['message_fail_reason'], XH_hsc($fail_reason));
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
    } else {
        // pre-release
        $msg = '<b>' . sprintf($p_tx['message_version-newer'], $localVersion[0], $remoteVersion[2]) . '</b>';
        $css_class = 'upd_info' . $css_suff;
    }
    //output
    return sprintf('<div class="%s">%s</div>', $css_class, $msg);
}

function hi_updateNoVersioninfo($pluginname = '') {
    global $plugin_tx;
    $p_tx = $plugin_tx['hi_updatecheck'];

    $css_class = 'upd_noinfo_list';
    $msg = '<b>'. sprintf($p_tx['message_no-versioninfo1'], ucfirst($pluginname), ENT_QUOTES, 'UTF-8') . '</b>';
    $msg .= '<br>';
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
            if (strpos($installed_plugin, '.') === false && (!isset($pluginloader_cfg['foldername_pluginloader']) || $installed_plugin != $pluginloader_cfg['foldername_pluginloader']) && is_dir($pth['folder']['plugins'] . $installed_plugin) && !in_array(strtolower($installed_plugin), $ignore)) {
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
        throw new RuntimeException('version.nfo is empty');
    }
    $versionInfo = array();
    $versionInfo = explode(',', $versionStr);
    if (count($versionInfo) !== 7) {
        throw new RuntimeException('version.nfo is invalid');
    }
    return $versionInfo;
}

function hi_updateNotify() {
    //Display info-icon in editmenu, if updates are available
    global $sn, $o, $plugin_tx;
    $o .= "\n";
    $o .= '<script>
                    jQuery(document).ready(function($){
                        $("#editmenu_update").css("display","block"); //before xh1.6
                        $("#xh_adminmenu_update").css("display","block"); //sice xh1.6RC
                    });
            </script>' . "\n";

    //Prepend notification to "Sysinfo" - page if updates are available
    if (isset($_GET['sysinfo'])) {
        $upd_msg_sysinfo = '<div class="upd_info">'
                . '<b>' . $plugin_tx['hi_updatecheck']['message_sysinfo-update-found'] . '</b>'
                . '<br>'
                . '<a href="' . $sn . '?&amp;hi_updatecheck&amp;admin=plugin_main&amp;normal">' . $plugin_tx['hi_updatecheck']['message_sysinfo-link'] . '</a>'
                . '</div>';
        $o .= $upd_msg_sysinfo . "\n";
    }
}

function hi_updateCheckAll() {
    global $plugin_cf, $plugin_tx, $tx;

    unset($_SESSION['upd_available']); //reset notifications
    $t = '<div id="upd_list_container">';
    $t .= $plugin_tx['hi_updatecheck']['heading_updatecheck'];
    $temp = explode(',', $plugin_cf['hi_updatecheck']['ignore']);
    if (!in_array('CMSimple_XH', $temp)) {
        $t .= $plugin_tx['hi_updatecheck']['heading_updatecheck_core'];
        $t .= '<b>' . $tx['sysinfo']['version'] . ':</b><br>';
        $t .= CMSIMPLE_XH_VERSION;
        if (defined('CMSIMPLE_XH_DATE'))
            $t .= '&nbsp;&nbsp;Released: ' . CMSIMPLE_XH_DATE;
        $t .= '<ul class="upd_list">';
        $t .= '<li>';
        $t .= hi_updateCheck('CMSimple_XH', 0);
        $t .= '</li>';
        $t .= '</ul>';
    }
    $upd_plugins = hi_updateInstalledScripts();
    if (count($upd_plugins) > 0) {
        $t .= $plugin_tx['hi_updatecheck']['heading_updatecheck_plugins'];
        $t .= '<ul class="upd_list">';
        foreach ($upd_plugins as $value) {
            $t .= '<li>';
            $t .= hi_updateCheck($value, 0);
            $t .= '</li>';
        }
        $t .= '</ul>';
    }
    $t .= '</div>';
    return $t;
}

function hi_updateSetStatus() {
    global $o, $plugin_cf;

    $upd_plugins = hi_updateInstalledScripts();
    $temp = explode(',', $plugin_cf['hi_updatecheck']['ignore']);
    if (!in_array('CMSimple_XH', $temp)) {
        array_unshift($upd_plugins, 'CMSimple_XH');
    }
    foreach ($upd_plugins as $value) {
        $o .= hi_updateQuickCheck($value);
    }
    $_SESSION['upd_checked'] = TRUE;
}

//Add entry to editmenu if updates are available
function upd_addMenuEntry() {
    global $sn, $plugin_tx, $pth;

    $href = $sn . '?&amp;hi_updatecheck&amp;admin=plugin_main&amp;normal';
    $t = "\n";
    $t .= '<script>
    jQuery(document).ready(function($){
        $("#edit_menu").append("<li id=\"editmenu_update\"><a href=\"' . $href . '\"><\/a></li>");                   //before xh1.6
        $("#xh_adminmenu > ul").append("<li id=\"xh_adminmenu_update\"><a href=\"' . $href . '\"><\/a></li>");       //since xh1.6RC
    });
    </script>' . "\n";
    return $t;
}

/*
 * Get contents of a file with fsockopen()
 * initial written by tleilax / klamm.de
 */

function hi_fsFileGetContents($url, $timeout = 30) {
    global $plugin_cf;

    $connect_timeout = (int)$plugin_cf['hi_updatecheck']['curl_connect_timeout'];
    $maxredir = (int)$plugin_cf['hi_updatecheck']['curl_maxredir'];
    $agent = CMSIMPLE_URL
           . ', PHP:' . phpversion()
           . ', ' . CMSIMPLE_XH_VERSION;

    // cURL
    if (extension_loaded('curl')) {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL             => $url,
            CURLOPT_HEADER          => false,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_USERAGENT       => $agent,
            CURLOPT_TIMEOUT         => $timeout,
            CURLOPT_CONNECTTIMEOUT  => $connect_timeout,
            CURLOPT_FRESH_CONNECT   => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => $maxredir
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec ($ch);
        if ($result !== false) {
            $headers = curl_getinfo($ch);
        } else {
            $curlerror = (curl_errno($ch)
                            ?  curl_errno($ch)
                                . ' - '
                                . curl_strerror(curl_errno($ch))
                            : '');
            throw new RuntimeException("cannot connect to $url , $curlerror");
        }
        curl_close($ch);
        if (!empty($headers['http_code'])) {
            $status = (int) $headers['http_code'];
        }

        if (in_array($status, Array(300, 301, 302, 303, 307))) {
            throw new RuntimeException("server responded with $status");
        }
    } else {
        // split URL
        $parsedurl = parse_url($url);
        // determine host, catch invalid calls
        if (empty($parsedurl['host']))
            throw new RuntimeException('host missing in URL ' . XH_hsc($url));
        $host = $parsedurl['host'];
        // determine path
        $documentpath = empty($parsedurl['path']) ? '/' : $documentpath = $parsedurl['path'];
        // determine params
        if (!empty($parsedurl['query']))
            $documentpath .= '?' . $parsedurl['query'];
        // determine port
        if (!empty($parsedurl['port'])) {
            $port = $parsedurl['port'];
        }
        // determine scheme
        switch ($parsedurl['scheme']) {
            case 'https':
                $scheme = 'ssl://';
                $port = empty($port) ? '443' : $port;
                break;
            default:
                $scheme = '';
                $port = empty($port) ? '80' : $port;
        }
        // open socket
        $fp = fsockopen($scheme . $host, $port, $errno, $errstr, $timeout);
        if (!$fp)
            throw new RuntimeException("cannot connect to $scheme$host:$port");

        // send request
        $request = "GET {$documentpath} HTTP/1.0\r\n";
        $request .= "Host: {$host}\r\n";
        $request .= "User-Agent: " . $agent . "\r\n\r\n";
        fputs($fp, $request);

        // read header
        $header = '';
        do {
            $line = rtrim(fgets($fp));
            $header .= $line . "\n";
        } while (!empty($line) and ! feof($fp));
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

        if (in_array($status, Array(300, 301, 302, 303, 307))) {
            preg_match('~Location: (?P<location>\S+)~i', $header, $match);
            $result = hi_fsFileGetContents($match['location'], $timeout);
        }
    }

    if ($status == 200) { // OK
        return $result;
    } elseif ($status == 204 or $status == 304) { // No Content | Not modified
        return '';
    } elseif ($status >= 400) { // Any error
        throw new RuntimeException("server responded with $status");
    }

    return $result;
}
