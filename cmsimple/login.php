<?php

/**
 * @version $Id$
 */

/* utf8-marker = äöü */
/*
  ======================================
  CMSimple_XH 1.5.3
  2012-03-19
  based on CMSimple version 3.3 - December 31. 2009
  For changelog, downloads and information please see http://www.cmsimple-xh.com
  ======================================
  -- COPYRIGHT INFORMATION START --
  Based on CMSimple version 3.3 - December 31. 2009
  Small - simple - smart
  © 1999-2009 Peter Andreas Harteg - peter@harteg.dk

  This file is part of CMSimple_XH
  For licence see notice in /cmsimple/cms.php
  -- COPYRIGHT INFORMATION END --
  ======================================
 */


if (preg_match('/login.php/i', sv('PHP_SELF')))
    die('Access Denied');

require 'PasswordHash.php';
$xh_hasher = new PasswordHash(8, true);

// for subsite solution - GE 20011-02

if ($txc['subsite']['password'] != "") {
    $cf['security']['password'] = $txc['subsite']['password'];
}

if ($sl != $cf['language']['default']) {
    $pth['folder']['content'] = $pth['folder']['base'] . $sl . '/content/';
}

// END for subsite solution - GE 20011-02
// functions used for login


function gc($s) {
    if (!isset($_COOKIE)) {
        global $_COOKIE;
        $_COOKIE = $GLOBALS['HTTP_COOKIE_VARS'];
    }
    if (isset($_COOKIE[$s]))
        return $_COOKIE[$s];
}

function logincheck() {
    global $cf;
    
    return (gc('passwd') == $cf['security']['password']);
}

function writelog($m) {
    global $pth, $e;
    if ($fh = @fopen($pth['file']['log'], "a")) {
        fwrite($fh, $m);
        fclose($fh);
    } else {
        e('cntwriteto', 'log', $pth['file']['log']);
        chkfile('log', true);
    }
}

function lilink() {
    global $cf, $adm, $sn, $u, $s, $tx;
    if (!$adm) {
        if ($cf['security']['type'] == 'javascript')
            return '<form id="login" action="' . $sn . '" method="post"><div id="loginlink">' . tag('input type="hidden" name="login" value="true"') . tag('input type="hidden" name="selected" value="' . $u[$s] . '"') . tag('input type="hidden" name="passwd" id="passwd" value=""') . '</div></form><a href="javascript:login()">' . $tx['menu']['login'] . '</a>';
        else
            return a($s > -1 ? $s : 0, '&amp;login') . $tx['menu']['login'] . '</a>';
    }
}

function loginforms() {
    global $adm, $cf, $print, $hjs, $tx, $onload, $f, $o, $s, $sn, $u;
    // Javascript placed in head section used for javascript login
    if (!$adm && $cf['security']['type'] == 'javascript' && !$print) {
        $hjs .= '<script type="text/javascript"><!--
			function login(){var t=prompt("' . $tx['login']['warning'] . '","");if(t!=null&&t!=""){document.getElementById("passwd").value=t;document.getElementById("login").submit();}}
			//-->
			</script>';
    }
    if ($f == 'login') {

        $cf['meta']['robots'] = "noindex";
        $onload .= "self.focus();document.login.passwd.focus();";
        $f = $tx['menu']['login'];
        $o .= '<h1>' . $tx['menu']['login'] . '</h1><p><b>' . $tx['login']['warning'] . '</b></p><form id="login" name="login" action="' . $sn . '?' . $u[$s] . '" method="post"><div id="login">' . tag('input type="hidden" name="login" value="true"') . tag('input type="hidden" name="selected" value="' . @$u[$s] . '"') . tag('input type="password" name="passwd" id="passwd" value=""') . ' ' . tag('input type="submit" name="submit" id="submit" value="' . $tx['menu']['login'] . '"') . '</div></form>';
        $s = -1;
    }
}

// if(gc('status')!=''||$login){header('Cache-Control: no-cache');header('Pragma: no-cache');}
// LOGIN & BACKUP

$adm = (gc('status') == 'adm' && logincheck());

if ($cf['security']['type'] == 'page' && $login && $passwd == '' && !$adm) {
    $login = null;
    $f = 'login';
}

if ($login && !$adm) {
    if ($xh_hasher->CheckPassword($passwd, $cf['security']['password'])
	&& ($cf['security']['type'] == 'page' || $cf['security']['type'] == 'javascript'))
    {
	setcookie('status', 'adm', 0, CMSIMPLE_ROOT);
	setcookie('passwd', $cf['security']['password'], 0, CMSIMPLE_ROOT);
	$adm = true;
	$edit = true;
	writelog(date("Y-m-d H:i:s") . " from " . sv('REMOTE_ADDR') . " logged_in\n");
    }
    else
	shead('401');
} else if ($logout && $adm) {
    $backupDate = date("Ymd_His");
    $fn = $backupDate . '_content.htm';
    if (@copy($pth['file']['content'], $pth['folder']['content'] . $fn)) {
        $o .= '<p>' . ucfirst($tx['filetype']['backup']) . ' ' . $fn . ' ' . $tx['result']['created'] . '</p>';
        $fl = array();
        $fd = @opendir($pth['folder']['content']);
        while (($p = @readdir($fd)) == true) {
            if (preg_match("/\d{3}\_content.htm/", $p))
                $fl[] = $p;
        }
        if ($fd == true)
            closedir($fd);
        @sort($fl, SORT_STRING);
        $v = count($fl) - $cf['backup']['numberoffiles'];
        for ($i = 0; $i < $v; $i++) {
            if (@unlink($pth['folder']['content'] . '/' . $fl[$i]))
                $o .= '<p>' . ucfirst($tx['filetype']['backup']) . ' ' . $fl[$i] . ' ' . $tx['result']['deleted'] . '</p>';
            else
                e('cntdelete', 'backup', $fl[$i]);
        }
    }
    else
        e('cntsave', 'backup', $fn);

// SAVE function for pagedata.php added - by MD 2009/09 (CMSimple_XH beta3.2)

    if (file_exists($pth['folder']['content'] . 'pagedata.php')) {
        $fn = $backupDate . '_pagedata.php';
        if (@copy($pth['file']['pagedata'], $pth['folder']['content'] . $fn)) {
            $o .= '<p>' . ucfirst($tx['filetype']['backup']) . ' ' . $fn . ' ' . $tx['result']['created'] . '</p>';
            $fl = array();
            $fd = @opendir($pth['folder']['content']);
            while (($p = @readdir($fd)) == true) {
                if (preg_match("/\d{3}\_pagedata.php/", $p))
                    $fl[] = $p;
            }
            if ($fd == true)
                closedir($fd);
            @sort($fl, SORT_STRING);
            $v = count($fl) - $cf['backup']['numberoffiles'];
            for ($i = 0; $i < $v; $i++) {
                if (@unlink($pth['folder']['content'] . $fl[$i]))
                    $o .= '<p>' . ucfirst($tx['filetype']['backup']) . ' ' . $fl[$i] . ' ' . $tx['result']['deleted'] . '</p>';
                else
                    e('cntdelete', 'backup', $fl[$i]);
            }
        }
        else
            e('cntsave', 'backup', $fn);
    }

// END save function for pagedata.php (CMSimple_XH beta3.2)


    $adm = false;
    setcookie('status', '', 0, CMSIMPLE_ROOT);
    setcookie('passwd', '', 0, CMSIMPLE_ROOT);
    $o .= '<p class="cmsimplecore_warning" style="text-align: center; font-weight: 900; padding: 8px;">' . $tx['login']['loggedout'] . '</p>';
}

define('XH_ADM', $adm);

// SETTING FUNCTIONS AS PERMITTED

if ($adm) {
    if ($edit)
        setcookie('mode', 'edit', 0, CMSIMPLE_ROOT);
    if ($normal)
        setcookie('mode', '', 0, CMSIMPLE_ROOT);
    if (gc('mode') == 'edit' && !$normal)
        $edit = true;
} else {
    if (gc('status') != '')
        setcookie('status', '', 0, CMSIMPLE_ROOT);
    if (gc('passwd') != '')
        setcookie('passwd', '', 0, CMSIMPLE_ROOT);
    if (gc('mode') == 'edit')
        setcookie('mode', '', 0, CMSIMPLE_ROOT);
}
?>