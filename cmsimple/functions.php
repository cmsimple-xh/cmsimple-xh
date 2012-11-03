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


if (preg_match('/functions.php/i', sv('PHP_SELF')))
    die('Access Denied');

// Backward compatibility for DHTML menus - moved from functions.php to cms.php (CMSimple_XH 1.0)



// #CMSimple functions to use within content

function geturl($u) {
    $t = '';
    if ($fh = @fopen(preg_replace("/\&amp;/is", "&", $u), "r")) {
        while (!feof($fh))
            $t .= fread($fh, 1024);
        fclose($fh);
        return preg_replace("/.*<body[^>]*>(.*)<\/body>.*/is", "\\1", $t);
    }
}

function geturlwp($u) {
    global $su;
    $t = '';
    if ($fh = @fopen($u . '?' . preg_replace("/^" . preg_quote($su, '/') . "(\&)?/s", "", sv('QUERY_STRING')), "r")) {
        while (!feof($fh))
            $t .= fread($fh, 1024);
        fclose($fh);
        return $t;
    }
}

function autogallery($u) {
    global $su;
    
    trigger_error('Function autogallery() is deprecated', E_USER_DEPRECATED);
    
    return preg_replace("/.*<!-- autogallery -->(.*)<!-- \/autogallery -->.*/is", "\\1", preg_replace("/(option value=\"\?)(p=)/is", "\\1" . $su . "&\\2", preg_replace("/(href=\"\?)/is", "\\1" . $su . '&amp;', preg_replace("/(src=\")(\.)/is", "\\1" . $u . "\\2", geturlwp($u)))));
}

// Other functions

function h($n) {
    global $h;
    return $h[$n];
}

function l($n) {
    global $l;
    return $l[$n];
}


/**
 * Returns $__text with CMSimple scripting evaluated.
 *
 * @param string $__text
 * @param bool $__compat  Wether only last CMSimple script should be evaluated.
 * @return string
 */
function evaluate_cmsimple_scripting($__text, $__compat = TRUE) {
    global $output;
    foreach ($GLOBALS as $__name => $__dummy) {global $$__name;}

    $__scope_before = NULL; // just that it exists
    $__scripts = array();
    preg_match_all('~'.$cf['scripting']['regexp'].'~is', $__text, $__scripts);
    if (count($__scripts[1]) > 0) {
        //$output = preg_replace('~'.$cf['scripting']['regexp'].'~is', '', $__text);
	$output = preg_replace('~#CMSimple (?!hide)(.*?)#~is', '', $__text);
	if ($__compat) {$__scripts[1] = array_reverse($__scripts[1]);}
        foreach ($__scripts[1] as $__script) {
            if (strtolower($__script) !== 'hide' && strtolower($__script) !== 'remove') {
                $__script = preg_replace(
		    array("'&(quot|#34);'i", "'&(amp|#38);'i", "'&(apos|#39);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i"),
		    array("\"", "&", "'", "<", ">", " "),
		    $__script);
		$__scope_before = array_keys(get_defined_vars());
                eval($__script);
		$__scope_after = array_keys(get_defined_vars());
		$__diff = array_diff($__scope_after, $__scope_before);
		foreach ($__diff as $__var) {$GLOBALS[$__var] = $$__var;}
		if ($__compat) {break;}
            }
        }
	$eval_script_output = $output;
	$output = '';
	return $eval_script_output;
    }
    return $__text;
}


/**
 * Returns $__text with all plugin calls evaluatated.
 * see plugins/index.php preCallPlugins()
 *
 * @param string $__text
 * @return string
 */
function evaluate_plugincall($__text) {
    global $u;

    $error = ' <span style="color:#5b0000; font-size:14px;">{{CALL TO:<span style="color:#c10000;">{{%1}}</span> FAILED}}</span> '; //use this for debugging of failed plugin-calls
    $pl_regex = '"{{{RGX:CALL(.*?)}}}"is'; //general CALL-RegEx (Placeholder: "RGX:CALL")
    $pl_calls = array(
	'PLUGIN:' => 'return {{%1}}',
	'HOME:' => 'return trim(\'<a href="?' . $u[0] . '" title="' . urldecode('{{%1}}') . '">' . urldecode('{{%1}}') . '</a>\');',
	'HOME' => 'return trim(\'<a href="?' . $u[0] . '" title="' . urldecode($u[0]) . '">' . urldecode($u[0]) . '</a>\');'
    );
    $fd_calls = array();
    foreach ($pl_calls AS $regex => $call) {
	preg_match_all(str_replace("RGX:CALL", $regex, $pl_regex), $__text, $fd_calls[$regex]); //catch all PL-CALLS
	foreach ($fd_calls[$regex][0] AS $call_nr => $replace) {
	    $call = str_replace("{{%1}}", $fd_calls[$regex][1][$call_nr], $pl_calls[$regex]);
	    $fnct_call = preg_replace('"(?:(?:return)\s)*(.*?)\(.*?\);"is', '$1', $call);
	    $fnct = function_exists($fnct_call) ? TRUE : FALSE; //without object-calls; functions-only!!
	    if ($fnct) {
		preg_match_all("/\\$([a-z_0-9]*)/i", $call, $matches);
		foreach ($matches[1] as $var) {
		    global $$var;
		}
	    }
	    $__text = str_replace(
		$replace,
		$fnct
		    ? eval(str_replace('{{%1}}', $fd_calls[$regex][1][$call_nr], $pl_calls[$regex]))
		    : str_replace('{{%1}}', $regex . $fd_calls[$regex][1][$call_nr], $error),
		$__text); //replace PL-CALLS (String only!!)
	}
    }
    return $__text;
}


/**
 * Returns $text with CMSimple scripting and plugin calls evaluated.
 *
 * @param string $text
 * @param bool $compat  Wheter only last CMSimple script will be evaluated.
 * @return void
 */
function evaluate_scripting($text, $compat = TRUE) {
    return evaluate_cmsimple_scripting(evaluate_plugincall($text), $compat);
}


/**
 * Returns content of the first CMSimple page with the heading $heading
 * with the heading removed and all scripting evaluated.
 * Returns FALSE, if the page doesn't exist.
 *
 * @param string $heading
 * @return mixed
 */
function newsbox($heading) {
    global $c, $cl, $h, $cf, $edit;

    for ($i = 0; $i < $cl; $i++) {
	if ($h[$i] == $heading) {
	    $body = preg_replace("/.*<\/h[1-".$cf['menu']['levels']."]>/is", "", $c[$i]);
	    return $edit ? $body : preg_replace("/".$cf['scripting']['regexp']."/is", "", evaluate_scripting($body, FALSE));
	}
    }
    return FALSE;
}


// EDITOR CALL

function init_editor($elementClasses = array(),  $initFile = false){
    global $pth, $cf;
    if (!file_exists($pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php')) {
         return false;
    }
    include_once $pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php';
    $function = 'init_' . $cf['editor']['external'];

    if (!function_exists($function)){
        return false;
    }

    $function($elementClasses, $initFile);

    return true;
}

function include_editor(){
    global $pth, $cf;
    if (!file_exists($pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php')) {
         return false;
    }
    include_once $pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php';
    $function = 'include_' . $cf['editor']['external'];

    if (!function_exists($function)){
        return false;
    }

    $function();

    return true;
}

function editor_replace($elementID = false, $config = ''){
    global $pth, $cf;

    if(!$elementID) {
        trigger_error('No elementID given', E_USER_NOTICE);
        return false;
    }

    if (!file_exists($pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php')) {
         return false;
    }
    include_once $pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php';
    $function = $cf['editor']['external'] . '_replace';

    if (!function_exists($function)){
        return false;
    }

    return $function($elementID, $config);
}


/**
 * Returns the result view of the system check.
 *
 * @access public
 * @since 1.5.4
 * @param array $data
 * @return string  The (X)HTML.
 */
function XH_systemCheck($data)
{
    global $pth, $tx;
    
    $stx = $tx['syscheck'];
    
    foreach (array('ok', 'warning', 'failure') as $img) {
	$txt = ucfirst($img);
	$imgs[$img] = tag('img src="' . $pth['folder']['flags'] . $img . '.gif" alt="'
	    . $txt . '" title="' . $txt . '" width="16" height="16"');
    }
    
    $o = "<h4>$stx[title]</h4>\n<ul id=\"xh_system_check\">\n";
    
    if (key_exists('phpversion', $data)) {
	$ok = version_compare(PHP_VERSION, $data['phpversion']) >= 0;
	$o .= '<li>' . $imgs[$ok ? 'ok' : 'fail']
	    . sprintf($stx['phpversion'], $data['phpversion']) . "</li>\n";
    }
    
    if (key_exists('extensions', $data)) {
	$cat = ' class="xh_system_check_cat_start"';
	foreach ($data['extensions'] as $ext) {
	    if (is_array($ext)) {
		$notok = $ext[1] ? 'failure' : 'warning';
		$ext = $ext[0];
	    } else {
		$notok = 'failure';
	    }
	    $o .= '<li' . $cat . '>' . $imgs[extension_loaded($ext) ? 'ok' : $notok]
		. sprintf($stx['extension'], $ext) . "</li>\n";
	    $cat = '';
	}
    }
    
    if (key_exists('writable', $data)) {
	$cat = ' class="xh_system_check_cat_start"';
	foreach ($data['writable'] as $file) {
	    if (is_array($file)) {
		$notok = $file[1] ? 'failure' : 'warning';
		$file = $file[0];
	    } else {
		$notok = 'warning';
	    }
	    $o .= '<li' . $cat . '>' . $imgs[is_writable($file) ? 'ok' : $notok]
		. sprintf($stx['writable'], $file) . "</li>\n";
	    $cat = '';
	}
    }
    
    if (key_exists('other', $data)) {
	$cat = ' class="xh_system_check_cat_start"';
	foreach ($data['other'] as $check) {
	    $notok = $check[1] ? 'failure' : 'warning';
	    $o .= '<li' . $cat . '>' . $imgs[$check[0] ? 'ok' : $notok]
		. $check[2] . "</li>\n";
	    $cat = '';
	}
    }
    
    $o .= "</ul>\n";
    
    return $o;
}

function final_clean_up($html) {
    global $adm, $s, $o, $errors, $cf, $bjs;

    if ($adm === true) {
        $debugHint = '';
        $errorList = '';
        $margin = 34;

        if ($debugMode = error_reporting() > 0) {
            $debugHint .= '<div class="cmsimplecore_debug">' . "\n" . '<b>Notice:</b> Debug-Mode is enabled!' . "\n" . '</div>' . "\n";
            $margin += 25;
        }


        global $errors;
        if(count($errors) > 0){

            $errorList .= '
                <div class="cmsimplecore_warning" style="margin: 0; border-width: 0;">
                  <ul>
                  ';
            $errors =  array_unique($errors);
            foreach($errors as $error){
                $errorList .= '<li>' . $error . '</li>';
            }
            $errorList .= '</ul></div>';
        }
        if (isset($cf['editmenu']['scroll']) && $cf['editmenu']['scroll'] == 'true'){
            $id = ' id="editmenu_scrolling"';
            $margin = 0;
        }
        else {
             $id =' id="editmenu_fixed"';
	     $html = preg_replace('~</head>~i','<style type="text/css">html {margin-top: ' . $margin . 'px;}</style>' ."\n" . '$0', $html, 1);

        }

        $html = preg_replace('~<body[^>]*>~i',
                            '$0' . '<div' . $id . '>' . $debugHint. admin_menu(XH_plugins(true), $debugMode) . '</div>' ."\n" .  $errorList,
                         $html, 1);


    }
    
    if (!empty($bjs)) {
        $html = preg_replace('/(<\/body\s*>)/isu', $bjs . "\n" . '$1', $html);
    }

    return $html;
}

// GLOBAL INTERNAL FUNCTIONS

function initvar($name) {
    if (!isset($GLOBALS[$name])) {
        if (isset($_GET[$name]))
            $GLOBALS[$name] = $_GET[$name];
        else if (isset($_POST[$name]))
            $GLOBALS[$name] = $_POST[$name];
        else
            $GLOBALS[$name] = @preg_replace("/.*?(" . $name . "=([^\&]*))?.*?/i", "\\2", sv('QUERY_STRING'));
    }
}

function sv($s) {
    if (!isset($_SERVER)) {
        global $_SERVER;
        $_SERVER = $GLOBALS['HTTP_SERVER_VARS'];
    }
    if (isset($_SERVER[$s]))
        return $_SERVER[$s];
    else
        return'';
}

function rmnl($t) {
    return preg_replace("/(\r\n|\r|\n)+/", "\n", $t);
}

/**
 * Returns $str with all (consecutive) whitespaces replaced by a single space.
 *
 * @param   string $str
 * @return  string
 */
function xh_rmws($str)
{
    $ws = '[\x09-\x0d\x20]'
        . '|\xc2[\x85\xa0]'
        . '|\xe1(\x9a\x80|\xa0\x8e)'
        . '|\xe2\x80[\x80-\x8a\xa8\xa9\xaf]'
        . '|\xe2\x81\x9f'
        . '|\xe3\x80\x80';
    return preg_replace('/(?:' . $ws . ')+/', ' ', $str);
}


function rmanl($t) {
    return preg_replace("/(\r\n|\r|\n)+/", "", $t);
}

function stsl($t) {
    if (get_magic_quotes_gpc())
        return stripslashes($t); else
        return $t;
}

function download($fl) {
    global $sn, $download, $tx;
    if (!is_readable($fl) || ($download != '' && !chkdl($sn . '?download=' . basename($fl)))) {
        global $o, $text_title;
        shead('404');
        $o .= '<p>File ' . $fl . '</p>';
        return;
    } else {
        header('Content-Type: application/save-as');
        header('Content-Disposition: attachment; filename="' . basename($fl) . '"');
        header('Content-Length:' . filesize($fl));
        header('Content-Transfer-Encoding: binary');
        if ($fh = @fopen($fl, "rb")) {
            while (!feof($fh))
                echo fread($fh, filesize($fl));
            fclose($fh);
        }
        exit;
    }
}

function chkdl($fl) {
    global $pth, $sn;
    $m = false;
    if (@is_dir($pth['folder']['downloads'])) {
        $fd = @opendir($pth['folder']['downloads']);
        while (($p = @readdir($fd)) == true) {
            if (preg_match("/.+\..+$/", $p)) {
                if ($fl == $sn . '?download=' . $p)
                    $m = true;
            }
        }
        if ($fd == true)
            closedir($fd);
    }
    return $m;
}

function rf($fl) {
    if (!file_exists($fl))
        return;
    clearstatcache();
    if (function_exists('file_get_contents'))
        return file_get_contents($fl);
    else {
        return join("\n", file($fl));
    }
}

function chkfile($fl, $writable) {
    global $pth, $tx;
    $t = isset($pth['file'][$fl]) ? $pth['file'][$fl] : '';
    if ($t == '')
        e('undefined', 'file', $fl);
    else if (!file_exists($t))
        e('missing', $fl, $t);
    else if (!is_readable($t))
        e('notreadable', $fl, $t);
    else if (!is_writable($t) && $writable)
        e('notwritable', $fl, $t);
}

function e($et, $ft, $fn) {
    global $e, $tx;
    $e .= '<li><b>' . $tx['error'][$et] . ' ' . $tx['filetype'][$ft] . '</b>' . tag('br') . $fn . '</li>' . "\n";
}

function rfc() {
    global $c, $cl, $h, $u, $l, $su, $s, $pth, $tx, $edit, $adm, $cf, $e;

    $c = array();
    $h = array();
    $u = array();
    $l = array();
    $empty = 0;
    $duplicate = 0;

    $content = file_get_contents($pth['file']['content']);
    $stop = $cf['menu']['levels'];
    $split_token = '#@CMSIMPLE_SPLIT@#';


    $content = preg_split('~</body>~i', $content);
    $content = preg_replace('~<h[1-' . $stop . ']~i', $split_token . '$0', $content[0]);
    $content = explode($split_token, $content);
    array_shift($content);

    foreach ($content as $page) {
        $c[] = $page;
        preg_match('~<h([1-' . $stop . ']).*>(.*)</h~isU', $page, $temp);
        $l[] = $temp[1];
        $temp_h[] = trim(xh_rmws(strip_tags($temp[2])));
    }

    $cl = count($c);
    $s = -1;

    if ($cl == 0) {
        $c[] = '<h1>' . $tx['toc']['newpage'] . '</h1>';
        $h[] = trim(strip_tags($tx['toc']['newpage']));
        $u[] = uenc($h[0]);
        $l[] = 1;
        $s = 0;
        return;
    }

    $ancestors = array();  /* just a helper for the "url" construction:
     * will be filled like this [0] => "Page"
     *                          [1] => "Subpage"
     *                          [2] => "Sub_Subpage" etc.
     */

    foreach ($temp_h as $i => $heading) {
        $temp = $heading;
        if ($temp == '') {
            $empty++;
            $temp = $tx['toc']['empty'] . ' ' . $empty;
        }
        $h[] = $temp;
        $ancestors[$l[$i] - 1] = uenc($temp);
        $ancestors = array_slice($ancestors, 0, $l[$i]);
        $url = implode($cf['uri']['seperator'], $ancestors);
        $u[] = substr($url, 0, $cf['uri']['length']);
        if ($adm && strlen($url) > $cf['uri']['length']) {
            $e .= '<li><b>' . $tx['uri']['toolong'] . '</b>' . tag('br')
                . '<a href="?' . $u[count($u) - 1] . '">' . $temp . '</a>' . '</li>';
        }
    }

    foreach ($u as $i => $url) {
        if ($su == $u[$i] || $su == urlencode($u[$i])) {
            $s = $i;
        } // get index of selected page

        for ($j = $i + 1; $j < $cl; $j++) {   //check for duplicate "urls"
            if ($u[$j] == $u[$i]) {
                $duplicate++;
                $h[$j] = $tx['toc']['dupl'] . ' ' . $duplicate;
                $u[$j] = uenc($h[$j]);
            }
        }
    }
    if (!($edit && $adm)) {
        foreach ($c as $i => $j) {
            if (cmscript('remove', $j)) {
                $c[$i] = '#CMSimple hide#';
            }
        }
    }
}

function a($i, $x) {
    global $sn, $u, $cf, $adm;
    if ($i == 0 && !$adm) {
        if ($x == '' && $cf['locator']['show_homepage'] == 'true') {
            return '<a href="' . $sn . '?' . $u[0] . '">';
        }
    }
    return isset($u[$i]) ? '<a href="' . $sn . '?' . $u[$i] . $x . '">' : '<a href="' . $sn . '?' . $x . '">'; // changed by LM CMSimple_XH 1.1
}

function meta($n) {
    global $cf, $print;
    $exclude = array('robots', 'keywords', 'description');
    if ($cf['meta'][$n] != '' && !($print && in_array($n, $exclude)))
        return tag('meta name="' . $n . '" content="' . htmlspecialchars($cf['meta'][$n], ENT_COMPAT, 'UTF-8') . '"') . "\n";
}

function ml($i) {
    global $f, $sn, $tx;
    $t = '';
    if ($f != $i)
        $t .= '<a href="' . $sn . '?&amp;' . $i . '">';
    $t .= $tx['menu'][$i];
    if ($f != $i)
        $t .= '</a>';
    return $t;
}

function uenc($s) {
    global $tx;
    if (isset($tx['urichar']['org']) && isset($tx['urichar']['new']))
        $s = str_replace(explode(",", $tx['urichar']['org']), explode(",", $tx['urichar']['new']), $s);
    return str_replace('+', '_', urlencode($s));
}

function rp($p) {
    trigger_error('Function rp() is deprecated', E_USER_DEPRECATED);
    
    if (@realpath($p) == '')
        return $p;
    else
        return realpath($p);
}

function sortdir($dir) {
    $fs = array();
    $fd = @opendir($dir);
    while (false !== ($fn = @readdir($fd))) {
        $fs[] = $fn;
    }
    if ($fd == true)
        closedir($fd);
    @sort($fs, SORT_STRING);
    return $fs;
}

function cmscript($s, $i) {
    global $cf;
    return preg_match(str_replace('(.*?)', $s, '/' . $cf['scripting']['regexp'] . '/is'), $i);
}

function hide($i) {
    global $c, $edit, $adm;
    if ($i < 0) {
        return false;
    }
    return (!($edit && $adm) && cmscript('hide', $c[$i]));
}

// For valid XHTML
function tag($s) {
    global $cf;
    $t = '';
    if ($cf['xhtml']['endtags'] == 'true')
        $t = ' /';
    return '<' . $s . $t . '>';
}

function amp() {
    global $cf;
    
    trigger_error('Function amp() is deprecated', E_USER_DEPRECATED);
    
    if ($cf['xhtml']['amp'] == 'true')
        return '&amp;';
    else
        return('&');
}

function shead($s) {
    global $iis, $cgi, $tx, $txc, $title, $o;
    if ($s == '401') {
        header(($cgi || $iis) ? 'status: 401 Unauthorized' : 'HTTP/1.0 401 Unauthorized');
    } elseif ($s == '403') {
        header(($cgi || $iis) ? 'status: 403 Forbidden' : 'HTTP/1.0 403 Forbidden');
    } elseif ($s == '404') {
	if (function_exists('custom_404')) {
	    custom_404();
	} else {
	    header(($cgi || $iis) ? 'status: 404 Not Found' : 'HTTP/1.0 404 Not Found');
	}
    }
    $title = $tx['error'][$s];
    $o = '<h1>' . $title . '</h1>' . $o;
}

/**
 * Debug-Mode
 * Check if file "_XHdebug.txt" exists to turn on debug-mode
 * with default setting E_ERROR | E_USER_WARNING | E_PARSE.
 * Level of debug mode can be adjusted by placing an
 * integer-value within the file using following values:
 *
 * Possible values of $dbglevel:
 *   0 - Turn off all error reporting
 *   1 - Running errors except warnings
 *   2 - Running errors
 *   3 - Running errors + notices
 *   4 - All errors except notices and warnings
 *   5 - All errors except notices
 *   6 - All errors
 *
 * @author Holger
 * @since CMSimple_XH V.1.0rc3 / Pluginloader V.2.1 beta 9
 *
 * @global array $pth CMSimple's pathes
 * @return boolean Returns true/false if error_reporting was enabled or not
 */
function xh_debugmode() {
    global $pth;
    $dbglevel = '';

    # possible values of $dbglevel:
    # 0 - Turn off all error reporting
    # 1 - Running errors except warnings
    # 2 - Running errors
    # 3 - Running errors + notices
    # 4 - All errors except notices and warnings
    # 5 - All errors except notices
    # 6 - All errors

    if (file_exists($pth['folder']['downloads'] . '_XHdebug.txt')) {
        ini_set('display_errors', 1);
        $dbglevel = rf($pth['folder']['downloads'] . '_XHdebug.txt');
        if (strlen($dbglevel) == 1) {
            set_error_handler('xh_debug');

            switch ($dbglevel) {
                case 0: error_reporting(0);
                    break;
                case 1: error_reporting(E_ERROR | E_USER_WARNING | E_PARSE);
                    break;
                case 2: error_reporting(E_ERROR | E_WARNING | E_USER_WARNING | E_PARSE);
                    break;
                case 3: error_reporting(E_ERROR | E_WARNING | E_USER_WARNING | E_PARSE | E_NOTICE);
                    break;
                case 4: error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_USER_WARNING));
                    break;
                case 5: error_reporting(E_ALL ^ E_NOTICE);
                    break;
                case 6: error_reporting(E_ALL);
                    break;
                default:
                    error_reporting(E_ERROR | E_USER_WARNING | E_PARSE);
            }
        } else {
            error_reporting(E_ERROR | E_USER_WARNING | E_PARSE);
        }
    } else {
        ini_set('display_errors', 0);
        error_reporting(0);
    }
    if (error_reporting() > 0) {
        return true;
    } else {
        return false;
    }
}

function xh_debug($errno, $errstr, $errfile, $errline, $context)
{
    global $errors;

    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        $errtype = 'XH-ERROR';
        break;
    case E_USER_WARNING:
        $errtype = 'XH-WARNING';
        break;
    case E_USER_NOTICE:
        $errtype = 'XH-NOTICE';
        break;
    case E_USER_DEPRECATED:
        $errtype = 'XH-DEPRECATED';
        $backtrace = debug_backtrace(FALSE);
        $errfile = $backtrace[2]['file'];
        $errline = $backtrace[2]['line'];
        break;
    case E_WARNING:
        $errtype = 'WARNING';
        break;
    case E_NOTICE:
        $errtype = 'NOTICE';
        break;
    case E_DEPRECATED:
        $errtype = 'DEPRECATED';
        break;
    default:
        $errtype = "Unknow error type [$errno]";
    }
    
    $errors[] = "<b>$errtype:</b> $errstr" . tag('br') . "$errfile:$errline"
        . tag('br') . "\n";
    
    if ($errno === E_USER_ERROR) {
        die($errors[count($errors) - 1]);
    }
    
  //  error_log($error, 3, CMS_DIR .'errors.log');
    /* Don't execute PHP internal error handler */

    return true;
}







/**
 * Checks $arr recursively for valid UTF-8. Otherwise it exists the script.
 *
 * This is useful for checking user input.
 *
 * @since   1.5.5
 * 
 * @param   array $arr
 * @return  void
 */
function XH_checkValidUtf8($arr)
{
    foreach ($arr as $elt) {
        if (is_array($elt)) {
            XH_checkValidUtf8($elt);
        } elseif (!utf8_is_valid($elt)) {
            header('HTTP/1.0 400 Bad Request'); // TODO: use "Status:" for FastCGI?
            exit('Malformed UTF-8 detected!');
        }
    }
}


/**
 * Copies default file, if actual language file is missing.
 *
 * @since 1.6
 *
 * @param   string $dst
 */
function XH_createLanguageFile($dst)
{
    $config = preg_match('/config.php$/', $dst) ? 'config' : '';
    if (!file_exists($dst)) {
        if (is_readable($src = dirname($dst) . "/default$config.php")) {
            copy($src, $dst);
        } elseif ($src = is_readable(dirname($dst) . "/en$config.php")) {
            copy($src, $dst);
        }
    }
    // TODO: error reporting???
    //if (!file_exists($dst)) {
    //    e('missing', 'file', $dst);
    //}
}

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
            return '<form id="login" action="' . $sn . '" method="post"><div id="loginlink">' . tag('input type="hidden" name="login" value="true"') . tag('input type="hidden" name="selected" value="' . $u[$s] . '"') . tag('input type="hidden" name="passwd" id="passwd" value=""') . '</div></form><a href="#" onclick="login(); return false">' . $tx['menu']['login'] . '</a>';
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


function XH_backup($file)
{
    global $pth, $cf, $tx;
    static $date = null;
    
    !isset($date) and $date = date("Ymd_His");
    if ($file != 'pagedata' || is_readable($pth['file']['pagedata'])) {
        $fn = "${date}_$file.htm";
        if (@copy($pth['file'][$file], $pth['folder']['content'] . $fn)) {
            $o = '<p>' . utf8_ucfirst($tx['filetype']['backup'])
                . ' ' . $fn . ' ' . $tx['result']['created'] . '</p>';
            $fl = array();
            $fd = @opendir($pth['folder']['content']);
            while (($p = @readdir($fd)) == true) {
                if (preg_match('/^\d{8}_\d{6}_' . $file . '.htm$/', $p)) {
                    $fl[] = $p;
                }
            }
            $fd and closedir($fd);
            sort($fl);
            $v = count($fl) - $cf['backup']['numberoffiles'];
            for ($i = 0; $i < $v; $i++) {
                if (@unlink($pth['folder']['content'] . $fl[$i]))
                    $o .= '<p>' . utf8_ucfirst($tx['filetype']['backup'])
                        . ' ' . $fl[$i] . ' ' . $tx['result']['deleted'] . '</p>';
                else
                    e('cntdelete', 'backup', $fl[$i]);
            }
        } else {
            e('cntsave', 'backup', $fn);
        }
    }
    return $o;
}

?>