<?php

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
    if ($fh = @fopen(($u . '?' . preg_replace("/^" . preg_replace("/\+/s", "\\\+", preg_replace("/\//s", "\\\/", $su)) . "(\&)?/s", "", sv('QUERY_STRING'))), "r")) {
        while (!feof($fh))
            $t .= fread($fh, 1024);
        fclose($fh);
        return $t;
    }
}

function autogallery($u) {
    global $su;
    return preg_replace("/.*<!-- autogallery -->(.*)<!-- \/autogallery -->.*/is", "\\1", preg_replace("/(option value=\"\?)(p=)/is", "\\1" . $su . "&\\2", preg_replace("/(href=\"\?)/is", "\\1" . $su . amp(), preg_replace("/(src=\")(\.)/is", "\\1" . $u . "\\2", geturlwp($u)))));
}

// Other functions
/*
function newsbox($b) {
	global $c, $cl, $h, $cf;
	for($i = 0; $i < $cl; $i++)if($h[$i] == $b)return preg_replace("/".$cf['scripting']['regexp']."/is", "", preg_replace("/.*<\/h[1-".$cf['menu']['levels']."]>/i", "", $c[$i]));
}
*/

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
	    $__text = str_replace($replace,
		    ($fnct
			? eval(str_replace('{{%1}}', $fd_calls[$regex][1][$call_nr], $pl_calls[$regex]))
			: str_replace('{{%1}}', $regex . $fd_calls[$regex][1][$call_nr], $error)),
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
	    $body = preg_replace("/.*<\/h[1-".$cf['menu']['levels']."]>/isu", "", $c[$i]);
	    return $edit ? $body : preg_replace("/".$cf['scripting']['regexp']."/is", "", evaluate_scripting($body, FALSE));
	}
    }
    return FALSE;
}


// includes additional userfuncs.php - CMSimple_XH beta3
if (file_exists($pth['folder']['cmsimple'] . 'userfuncs.php')) {
    include($pth['folder']['cmsimple'] . 'userfuncs.php');
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

?>