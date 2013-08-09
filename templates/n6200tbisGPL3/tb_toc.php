<?php
/*
This file is part of a template, which was created by Torsten Behrens.
Take a modern CMSimple XH version. www.cmsimple-xh.org www.cmsimple.name www.cmsimple.me www.cmsimple.eu

Version 08.08.2013. Update for jQuery4CMSimple. 
##################################################################################
# Dies ist ein GPL3 Template von Torsten Behrens.                                #
# Torsten Behrens                                                                #
# Dorfstraße 2                                                                   #
# D-24619 Tarbek                                                                 #
# USt.ID-Nr. DE214080613                                                         #
# http://torsten-behrens.de                                                      #
# http://tbis.info                                                               #
# http://tbis.net                                                                #
# http://cmsimple-templates.de                                                   #
# http://cmsimple-templates.com                                                  #
##################################################################################
*/



/* tb_toc 0.5
This is a modified version of the toc()function of Peter Harteg's CMSimple (www.cmsimple.dk). It has been modified by Nikolai Bock
for Torsten Behrens (www.torsten-behrens.de).
It is called via the template.htm file. You call it by adding at the top of the template.htm file:
 - <?php include ($pth['folder']['template'].'tb_toc.php'); ? > (remove the space between ? and >) - 
The tb_toc.php file is placed in the folder where the template resides. In the template you do not use "toc()" but "tb_toc()" as menu function.
The only different of the tb_toc to toc is that the function call the tb_li from this file and not the li-function from cms.php
If you want to change the layout change this li-function and/or change the CSS-File.
*/


function tb_toc($start = NULL, $end = NULL, $li = 'tb_li') { // changed by LM CMSimple_XH 1.1
	global $c, $cl, $s, $l, $cf;
	$ta = array();
	if (isset($start)) {
		if (!isset($end))$end = $start;
	}
	else $start = 1;
	if (!isset($end))$end = $cf['menu']['levels'];
	$ta = array();
	if ($s > -1) {
		$tl = $l[$s];
		for($i = $s; $i > -1; $i--) {
			if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end)if(!hide($i))$ta[] = $i;
			if ($l[$i] < $tl)$tl = $l[$i];
		}
		@sort($ta);
		$tl = $l[$s];
	}
	else $tl = 0;
	$tl += 1+$cf['menu']['levelcatch'];
	for($i = $s+1; $i < $cl; $i++) {
		if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end)if(!hide($i))$ta[] = $i;
		if ($l[$i] < $tl)$tl = $l[$i];
	}
	return call_user_func($li, $ta, $start);
}

// inserted many "\n" for better structured Sourcecode - by GE 2009/06 (CMSimple_XH beta)

function tb_li($ta, $st) {
	global $s, $l, $h, $cl, $cf, $u;
	$tl = count($ta);
	if ($tl < 1)return;
	$t = '';
	if ($st == 'submenu' || $st == 'search')$t .= '<ul class="'.$st."\n".'">';
	$b = 0;
	if ($st > 0) {
		$b = $st-1;
		$st = 'menulevel';
	}
	$lf = array();
	for($i = 0; $i < $tl; $i++) {
		$tf = ($s != $ta[$i]);
		if ($st == 'menulevel' || $st == 'sitemaplevel') {
			for($k = (isset($ta[$i-1])?$l[$ta[$i-1]]:$b); $k < $l[$ta[$i]]; $k++)
				if($k==0)
					$t .= "\n".'<ul class="tbisgpl3-vmenu">'."\n";
				else
					$t .= "\n".'<ul class="active">'."\n";
		}
		$t .= '<li class="';
		if (!$tf)$t .= 'active';
		else if(@$cf['menu']['sdoc'] == "parent" && $s > -1) {
			if ($l[$ta[$i]] < $l[$s]) {
				if (@substr($u[$s], 0, 1+strlen($u[$ta[$i]])) == $u[$ta[$i]].$cf['uri']['seperator']){
					$t .= 'active';
				}
			}
		}
		//$t .= 'doc';
		/*for($j = $ta[$i]+1; $j < $cl; $j++)if(!hide($j) && $l[$j]-$l[$ta[$i]] < 2+$cf['menu']['levelcatch']) {
			if ($l[$j] > $l[$ta[$i]]){
				$t .= 's';
				break;
			}
		}*/
		$t .= '">';
		//if ($tf)
		$prueflink=a($ta[$i], '');
		$t .= $prueflink;
		if(!(($k>1 && strpos($prueflink,":")!==false)))
			$t .= '<span class="l"></span><span class="r"></span><span class="t">';
		$t .= $h[$ta[$i]];
		if(!(($k>1 && strpos($prueflink,":")!==false)))
			$t .= '</span>';
		//if ($tf)
		$t .= '</a>';
		if ($st == 'menulevel' || $st == 'sitemaplevel') {
			if ((isset($ta[$i+1])?$l[$ta[$i+1]]:$b) > $l[$ta[$i]])$lf[$l[$ta[$i]]] = true;
			else
				{
				$t .= '</li>'."\n";
				$lf[$l[$ta[$i]]] = false;
			}
			for($k = $l[$ta[$i]]; $k > (isset($ta[$i+1])?$l[$ta[$i+1]]:$b); $k--) {
				$t .= '</ul>'."\n";
				if (isset($lf[$k-1]))if($lf[$k-1]) {
					$t .= '</li>'."\n";
					$lf[$k-1] = false;
				}
			};
		}
		else $t .= '</li>'."\n";
	}
	if ($st == 'submenu' || $st == 'search')$t .= '</ul>'."\n";
	return $t;
}
?>