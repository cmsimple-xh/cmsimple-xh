<?php
/* utf8-marker = äöüß */
/*
CMSimple_XH 1.2
2010-07-05
based on CMSimple version 3.3 - December 31. 2009
For changelog, downloads and information please see http://www.cmsimple-xh.com
======================================
-- COPYRIGHT INFORMATION START --
CMSimple version 3.3 - December 31. 2009
Small - simple - smart
© 1999-2009 Peter Andreas Harteg - peter@harteg.dk

This file is part of CMSimple
For licence see notice in /cmsimple/cms.php and http://www.cmsimple.org/?Licence
-- COPYRIGHT INFORMATION END --
======================================
*/

// functions used for login

if (eregi('login.php', sv('PHP_SELF')))die('Access Denied');

function gc($s) {
	if (!isset($_COOKIE)) {
		global $_COOKIE;
		 $_COOKIE = $GLOBALS['HTTP_COOKIE_VARS'];
	}
	if (isset($_COOKIE[$s]))return $_COOKIE[$s];
}

function logincheck() {
	global $cf;
	if ($cf['security']['type'] == 'wwwaut')return (sv('PHP_AUTH_USER') == $cf['security']['username'] && sv('PHP_AUTH_PW') == $cf['security']['password']);
	else return (gc('passwd') == $cf['security']['password']);
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
		if ($cf['security']['type'] == 'javascript')return '<form id="login" action="'.$sn.'" method="post"><div id="loginlink">'.tag('input type="hidden" name="login" value="true"').tag('input type="hidden" name="selected" value="'.$u[$s].'"').tag('input type="hidden" name="passwd" id="passwd" value=""').'</div></form><a href="javascript:login()">'.$tx['menu']['login'].'</a>';
		else return a($s, amp().'login').$tx['menu']['login'].'</a>';
	}
}

function loginforms() {
	global $adm, $cf, $print, $retrieve, $hjs, $tx, $onload, $f, $o, $s, $sn, $u;
	// Javascript placed in head section used for javascript login
	if (!$adm && $cf['security']['type'] == 'javascript' && !$print && !$retrieve) {
		$hjs .= '<script type="text/javascript"><!--
			function login(){var t=prompt("'.$tx['login']['warning'].'","");if(t!=null&&t!=""){document.getElementById("passwd").value=t;document.getElementById("login").submit();}}
			//-->
			</script>';
	}
	if ($f == 'login') {
		$cf['meta']['robots']="noindex";
		$onload .= "self.focus();document.login.passwd.focus();";
		$f = $tx['menu']['login'];
		$o .= '<h1>'.$tx['menu']['login'].'</h1><p><b>'.$tx['login']['warning'].'</b></p><form id="login" name="login" action="'.$sn.'" method="post"><div id="login">'.tag('input type="hidden" name="login" value="true"').tag('input type="hidden" name="selected" value="'.@$u[$s].'"').tag('input type="password" name="passwd" id="passwd" value=""').' '.tag('input type="submit" name="submit" id="submit" value="'.$tx['menu']['login'].'"').'</div></form>';
		$s = -1;
	}
}

// if(gc('status')!=''||$login){header('Cache-Control: no-cache');header('Pragma: no-cache');}

// LOGIN & BACKUP

if (!isset($cf['security']['username']) && $cf['security']['type'] == 'wwwaut')$cf['security']['username'] = "admin";

if ($cgi && $cf['security']['type'] == 'wwwaut') {
	if (!$_SERVER['REMOTE_USER'])$_SERVER['REMOTE_USER'] = $_SERVER['REDIRECT_REMOTE_USER'];
	if ((!$_SERVER['PHP_AUTH_USER'] || !$_SERVER['PHP_AUTH_USER']) && preg_match('/^Basic.*/i', $_SERVER['REMOTE_USER']))list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['REMOTE_USER'], 6)));
}

$adm = (gc('status') == 'adm' && logincheck());

if ($cf['security']['type'] == 'page' && $login && $passwd == '' && !$adm) {
	$login = null;
	$f = 'login';
}

if ($login && !$adm) {
	if ($cf['security']['type'] != 'wwwaut') {
		if ($passwd == $cf['security']['password'] && ($cf['security']['type'] == 'page' || $cf['security']['type'] == 'javascript')) {
			setcookie('status', 'adm', 0, CMSIMPLE_ROOT);
			setcookie('passwd', $passwd, 0, CMSIMPLE_ROOT);
			$adm = true;
			$edit = true;
			writelog(date("Y-m-d H:i:s")." from ".sv('REMOTE_ADDR')." logged_in\n");
		}
		else
			shead('401');
	} else {
		if (sv('PHP_AUTH_USER') == '' || sv('PHP_AUTH_PW') == '' || gc('status') == '') {

			setcookie('status', 'login', 0, CMSIMPLE_ROOT);
			header('WWW-Authenticate: Basic realm="'.$tx['login']['warning'].'"');
			shead('401');
		} else {
			if (logincheck()) {
				setcookie('status', 'adm', 0, CMSIMPLE_ROOT);
				$adm = true;
				$edit = true;
				writelog(date($tx['log']['dateformat']).' '.sv('REMOTE_ADDR').' '.$tx['log']['loggedin']."\n");
			} else {
				shead('401');
			}
		}
	}
}
else if($logout && $adm) {
	$fn = date("Ymd_His").'_content.htm';
	if (@copy($pth['file']['content'], $pth['folder']['content'].$fn)) {
		$o .= '<p>'.ucfirst($tx['filetype']['backup']).' '.$fn.' '.$tx['result']['created'].'</p>';
		$fl = array();
		$fd = @opendir($pth['folder']['content']);
		while (($p = @readdir($fd)) == true) {
			if (preg_match("/\d{3}\_content.htm/", $p))$fl[] = $p;
		}
		if ($fd == true)closedir($fd);
		@sort($fl, SORT_STRING);
		$v = count($fl)-$cf['backup']['numberoffiles'];
		for($i = 0; $i < $v; $i++) {
			if (@unlink($pth['folder']['content'].'/'.$fl[$i]))$o .= '<p>'.ucfirst($tx['filetype']['backup']).' '.$fl[$i].' '.$tx['result']['deleted'].'</p>';
			else e('cntdelete', 'backup', $fl[$i]);
		}
	}
	else e('cntsave', 'backup', $fn);

// SAVE function for pagedata.php added - by MD 2009/09 (CMSimple_XH beta3.2)

	if(file_exists($pth['folder']['content'].'pagedata.php')){
		$fn = date("Ymd_His").'_pagedata.php';
			if (@copy($pth['folder']['content'].'pagedata.php', $pth['folder']['content'].$fn)) {
			$o .= '<p>'.ucfirst($tx['filetype']['backup']).' '.$fn.' '.$tx['result']['created'].'</p>';
			$fl = array();
			$fd = @opendir($pth['folder']['content']);
				while (($p = @readdir($fd)) == true) {
				if (preg_match("/\d{3}\_pagedata.php/", $p))$fl[] = $p;
			}
			if ($fd == true)closedir($fd);
			@sort($fl, SORT_STRING);
			$v = count($fl)-$cf['backup']['numberoffiles'];
				for($i = 0; $i < $v; $i++) {
				if (@unlink($pth['folder']['content'].$fl[$i]))$o .= '<p>'.ucfirst($tx['filetype']['backup']).' '.$fl[$i].' '.$tx['result']['deleted'].'</p>';
				else e('cntdelete', 'backup', $fl[$i]);
			}
		}
	else e('cntsave', 'backup', $fn);
	}

// END save function for pagedata.php (CMSimple_XH beta3.2)


	$adm = false;
	setcookie('status', '', 0, CMSIMPLE_ROOT);
	setcookie('passwd', '', 0, CMSIMPLE_ROOT);
	$o .= '<p><font color="red">'.$tx['login']['loggedout'].'</font></p>';
}

// SETTING FUNCTIONS AS PERMITTED

if ($adm) {
	if ($edit)setcookie('mode', 'edit');
	if ($normal)setcookie('mode', '');
	if (gc('mode') == 'edit' && !$normal)$edit = true;
} else {
	if (gc('status') != '')setcookie('status', '', 0, CMSIMPLE_ROOT);
	if (gc('passwd') != '')setcookie('passwd', '', 0, CMSIMPLE_ROOT);
	if (gc('mode') == 'edit')setcookie('mode', '', 0, CMSIMPLE_ROOT);
}
?>