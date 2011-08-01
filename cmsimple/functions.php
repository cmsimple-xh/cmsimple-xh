<?php

/* utf8-marker = äöüß */
/*
  CMSimple_XH 1.4.1
  2011-01-18
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

function newsbox($b) {
	global $c, $cl, $h, $cf;
	for($i = 0; $i < $cl; $i++)if($h[$i] == $b)return preg_replace("/".$cf['scripting']['regexp']."/is", "", preg_replace("/.*<\/h[1-".$cf['menu']['levels']."]>/i", "", $c[$i]));
}

function h($n) {
    global $h;
    return $h[$n];
}

function l($n) {
    global $l;
    return $l[$n];
}


function evaluating_newsbox($heading){
    global $c, $h, $cf;
 
    foreach($c as $i => $page) {
        if ($h[$i] == $heading) {
            preCallPlugins($i);
            $scripts = array();
            preg_match_all("~" . $cf['scripting']['regexp'] . "~is", $c[$i], $scripts);
            if (count($scripts) > 0) {
                $output = preg_replace("/".$cf['scripting']['regexp']."/is", "", $c[$i]);
                foreach($scripts[1] as $script){
                    if($script !== 'hide' && $script !== 'remove'){
                        $script = preg_replace( array("'&(quot|#34);'i", "'&(amp|#38);'i", "'&(apos|#39);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i"), 
                                                array("\"", "&", "'", "<", ">", " "), 
                                                $script);
                       eval($script);
                    }
                }
                return $output;
            }
            return $page;
        }
    }
}


// includes additional userfuncs.php - CMSimple_XH beta3
if (file_exists($pth['folder']['cmsimple'] . 'userfuncs.php')) {
    include($pth['folder']['cmsimple'] . 'userfuncs.php');
}
?>