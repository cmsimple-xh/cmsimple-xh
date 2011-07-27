<?php

/* utf8-marker = äöüß */
/*
  CMSimple version 1.5 beta - Aug. 2011
  Small - simple - smart
  © 1999-2008 Peter Andreas Harteg - peter@harteg.dk

  This file is part of CMSimple
  For licence see notice in /cmsimple/cms.php and http://www.cmsimple.org/?Licence
 */

if (eregi('search.php', sv('PHP_SELF'))) {
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
    $search = mb_strtolower(trim($search), 'utf-8');
    $words = explode(' ', $search);

    foreach ($c as $i => $pagexyz) {
        if (!hide($i)) {
            $found  = true;
            preCallPlugins($i);
            $pagexyz = mb_strtolower(strip_tags($pagexyz), 'utf-8');
            $pagexyz = html_entity_decode($pagexyz, ENT_QUOTES, 'utf-8') ;

            preg_match("~".$cf['scripting']['regexp']."~is",  $pagexyz, $matches);
            if(count($matches) > 0) {
                $pagexyz = str_replace($matches[0], '', $pagexyz);
                if(trim($matches[1]) !== 'hide' && trim($matches[1]) !== 'remove') {
		    $output = '';
		    $o = '';
                    @eval($matches[1]);
                    $pagexyz .= $output;
                    $pagexyz .= $o;
                }
            }
            foreach($words as $word) {
                if(strpos($pagexyz, trim($word)) === false) {
                    $found = false;
                    break;
                }
            }
            if (!$found) {
                continue;
            }
            $ta[] = $i;

        }
    }
    if(count($ta) > 0){
        $cms_searchresults = "\n" .'<ul>';
	
	$words = (implode( ",", $words));
        foreach($ta as $i){
            $cms_searchresults .= "\n\t" . '<li><a href="' . $sn . '?' . $u[$i] . amp() . 'search=' . $words .'">' . $h[$i] . '</a></li>';
        }
        $cms_searchresults .= "\n" . '</ul>' . "\n";
    }
}

$o .= '<h1>' . $tx['search']['result'] . '</h1><p>"' . htmlspecialchars(stsl($search)) . '" ';

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