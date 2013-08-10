<?php
/*
XTOC28 version 1.0
This is a modified version of the toc()function of Peter Harteg's CMSimple version 2.8 and higher (www.cmsimple.dk). It is not thought to be used in earlier versions of CMSimple (v.2.7 and lower) It has been modified by Till from NMuD (www.nmud.de). 
It is called via the template.htm file. You call it by adding at the top of the template.htm file:
 - <?php include ($pth['folder']['template'].'xtoc.php'); ? > (remove the space between ? and >) - 
The xtoc.php file is placed in the folder where the template resides. In the template you do not use "toc()" but "xtoc()" as menu function.
While the xtoc()-function (see below) of this file always has to be active, only one of the li()-functions must be active. The other ones have to be outcommented or removed. The li()-functions offer the following:
(1) turns a clicked button to a clickable button. Originally, a clicked button cannot be clicked again.
*/

//__________________________________________________________________________

function xtoc($start, $end) {global $c, $cl, $s, $l, $cf;$ta = array();if (isset($start)) {if (!isset($end))$end = $start;}else $start = 1;if (!isset($end))$end = $cf['menu']['levels'];$ta = array();if ($s > -1) {$tl = $l[$s];for($i = $s; $i > -1; $i--) {	if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end)if(!hide($i))$ta[] = $i;if ($l[$i] < $tl)$tl = $l[$i];}@sort($ta);$tl = $l[$s];}else $tl = 0;$tl += 1+$cf['menu']['levelcatch'];for($i = $s+1; $i < $cl; $i++) {if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end)if(!hide($i))$ta[] = $i;	if ($l[$i] < $tl)$tl = $l[$i];}return xli($ta, $start);}
//
//__________________________________________________________________________
//
// This xli() function turns a clicked button to a clickable button. Originally, a clicked button cannot be clicked again.
function xli($ta, $st) {global $s, $l, $h, $cl, $cf, $u;$tl = count($ta);if ($tl < 1)return;$t = '';if ($st == 'submenu' || $st== 'search')$t .= '<ul class="'.$st.'">';$b = 0;if ($st > 0) {	$b = $st-1;	$st = 'menulevel';}$lf = array();for($i = 0; $i < $tl;$i++) {$tf = ($s != $ta[$i]);if ($st == 'menulevel' || $st == 'sitemaplevel') {for($k = (isset($ta[$i-1])?$l[$ta[$i-1]]:$b); $k < $l[$ta[$i]]; $k++)$t .= '<ul class="'.$st.($k+1).'">';}$t .= '<li class="';if (!$tf)$t .= 's';else if(@$cf['menu']['sdoc'] == "parent" && $s > -1) {if ($l[$ta[$i]] < $l[$s]) {if (substr($u[$s], 0, strlen($u[$ta[$i]])) == $u[$ta[$i]])$t .= 's';}}		$t .= 'doc';for($j = $ta[$i]+1; $j < $cl; $j++)if(!hide($j) && $l[$j]-$l[$ta[$i]] < 2+$cf['menu']['levelcatch']) {if ($l[$j] > $l[$ta[$i]])$t .= 's';break;}$t .= '">';/*if ($tf)*/$t .= a($ta[$i], '');$t .= $h[$ta[$i]];/*if ($tf)*/$t .= '</a>';if ($st == 'menulevel' || $st == 'sitemaplevel') {if ((isset($ta[$i+1])?$l[$ta[$i+1]]:$b) > $l[$ta[$i]])$lf[$l[$ta[$i]]] = true;			else{$t .= '</li>';	$lf[$l[$ta[$i]]] = false;}for($k = $l[$ta[$i]]; $k > (isset($ta[$i+1])?$l[$ta[$i+1]]:$b); $k--) {				$t .= '</ul>';if (isset($lf[$k-1]))if($lf[$k-1]) {$t .= '</li>';$lf[$k-1] = false;}};}else $t .= '</li>';}if ($st == 'submenu' || $st == 'search')$t .= '</ul>';	return $t;}

?>