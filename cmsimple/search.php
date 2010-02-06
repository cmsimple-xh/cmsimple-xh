<?php
/*
CMSimple version 3.2 - June 20. 2008
Small - simple - smart
© 1999-2008 Peter Andreas Harteg - peter@harteg.dk

This file is part of CMSimple
For licence see notice in /cmsimple/cms.php and http://www.cmsimple.org/?Licence
*/

if (eregi('search.php', sv('PHP_SELF')))die('Access Denied');

$title = $tx['title']['search'];
$ta = array();
if ($search != '')for($i = 0; $i < $cl; $i++) {
	if (!hide($i))if (@preg_match('/'.preg_quote($search, '/').'/i', (function_exists('html_entity_decode')?html_entity_decode($c[$i]):$c[$i])))$ta[] = $i;
}
$o .= '<h1>'.$tx['search']['result'].'</h1><p>"'.htmlspecialchars(stsl($search)).'" ';
if (count($ta) == 0)$o .= $tx['search']['notfound'].'.</p>';
else
	{
	$o .= $tx['search']['foundin'].' '.count($ta). ' ';
	if (count($ta) > 1)$o .= $tx['search']['pgplural'];
	else $o .= $tx['search']['pgsingular'];
	$o .= ':</p>'.li($ta, 'search');
}

?>