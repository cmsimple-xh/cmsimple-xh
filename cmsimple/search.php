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

if (strpos('search.php', strtolower(sv('PHP_SELF')))) {
    die('Access Denied');
}

if(!function_exists('mb_strtolower')) {
    function mb_strtolower($string, $charset = null) {
        $string = utf8_decode($string);
        $string = strtolower($string);
        $string = utf8_encode($string);
        return $string;
    }
}



$title = $tx['title']['search'];
$ta = array();
if ($search != '') {
    $search = mb_strtolower(trim(stsl($search)), 'utf-8');
    $words = explode(' ', $search);

    foreach ($c as $i => $pagexyz) {
        if (!hide($i) || $cf['hidden']['pages_search'] == 'true') {
            $found  = true;
	    $pagexyz = evaluate_plugincall($pagexyz, TRUE);
            $pagexyz = mb_strtolower(strip_tags($pagexyz), 'utf-8');
            $pagexyz = html_entity_decode($pagexyz, ENT_QUOTES, 'utf-8');
            foreach ($words as $word) {
                if (strpos($pagexyz, trim($word)) === false) {
                    $found = false;
                    break;
                }
            }
            if (!$found) {continue;}
            $ta[] = $i;
        }
    }
    
    if(count($ta) > 0){
        $cms_searchresults = "\n" .'<ul>';
	
	$words = (implode( ",", $words));
        foreach($ta as $i){
            $cms_searchresults .= "\n\t" . '<li><a href="' . $sn . '?' . $u[$i] . amp() . 'search=' . urlencode($words) .'">' . $h[$i] . '</a></li>';
        }
        $cms_searchresults .= "\n" . '</ul>' . "\n";
    }
}

$o .= '<h1>' . $tx['search']['result'] . '</h1><p>"' . htmlspecialchars($search) . '" ';

if (count($ta) == 0) {
    $o .= $tx['search']['notfound'] . '.</p>';
}
else {
    $o .= $tx['search']['foundin'] . ' ' . count($ta) . ' ';
    if (count($ta) > 1
    )$o .= $tx['search']['pgplural'];
    else
        $o .= $tx['search']['pgsingular'];
    $o .= ':</p>' . $cms_searchresults;
}

?>