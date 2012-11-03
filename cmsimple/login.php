<?php

/**
 * @version $Id$
 */

/* utf8-marker = äöü */
/*
  ======================================
  $CMSIMPLE_XH_VERSION$
  $CMSIMPLE_XH_DATE$
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

require $pth['folder']['classes'] . 'PasswordHash.php';
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
	shead('403');
} else if ($logout && $adm) {
    $backupDate = date("Ymd_His");
    $fn = $backupDate . '_content.htm';
    if (@copy($pth['file']['content'], $pth['folder']['content'] . $fn)) {
        $o .= '<p>' . utf8_ucfirst($tx['filetype']['backup']) . ' ' . $fn . ' ' . $tx['result']['created'] . '</p>';
        $fl = array();
        $fd = @opendir($pth['folder']['content']);
        while (($p = @readdir($fd)) == true) {
            if (preg_match('/^\d{8}_\d{6}_content.htm$/', $p))
                $fl[] = $p;
        }
        if ($fd == true)
            closedir($fd);
        @sort($fl, SORT_STRING);
        $v = count($fl) - $cf['backup']['numberoffiles'];
        for ($i = 0; $i < $v; $i++) {
            if (@unlink($pth['folder']['content'] . '/' . $fl[$i]))
                $o .= '<p>' . utf8_ucfirst($tx['filetype']['backup']) . ' ' . $fl[$i] . ' ' . $tx['result']['deleted'] . '</p>';
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
            $o .= '<p>' . utf8_ucfirst($tx['filetype']['backup']) . ' ' . $fn . ' ' . $tx['result']['created'] . '</p>';
            $fl = array();
            $fd = @opendir($pth['folder']['content']);
            while (($p = @readdir($fd)) == true) {
                if (preg_match('/^\d{8}_\d{6}_pagedata.php$/', $p))
                    $fl[] = $p;
            }
            if ($fd == true)
                closedir($fd);
            @sort($fl, SORT_STRING);
            $v = count($fl) - $cf['backup']['numberoffiles'];
            for ($i = 0; $i < $v; $i++) {
                if (@unlink($pth['folder']['content'] . $fl[$i]))
                    $o .= '<p>' . utf8_ucfirst($tx['filetype']['backup']) . ' ' . $fl[$i] . ' ' . $tx['result']['deleted'] . '</p>';
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
    $o .= '<script type="text/javascript">/* <![CDATA[ */'
	. 'if (document.cookie.indexOf(\'status=adm\') == -1)'
	. ' document.write(\'<div class="cmsimplecore_warning">' . $tx['error']['nocookies'] . '</div>\')'
	. '/* ]]> */</script>'
	. '<noscript><div class="cmsimplecore_warning">' . $tx['error']['nojs'] . '</div></noscript>';
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