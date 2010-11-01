<?php
/* utf8-marker = äöüß */
/*
CMSimple_XH 1.2
2010-10-15
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

if (preg_match('/adm.php/i', sv('PHP_SELF')))die('Access Denied');

// Functions used for adm

function selectlist($fn, $regm, $regr) {
	global $k1, $k2, $v2, $o, $pth;
	$o .= '<select name="'.$k1.'_'.$k2.'">';
	if ($fd = @opendir($pth['folder'][$fn])) {
		while (($p = @readdir($fd)) == true) {
				if (preg_match($regm, $p)) {
				$v = preg_replace($regr, "\\1", $p);
				$o .= '<option value="'.$v.'"';
				if ($v == $v2) $o .= ' selected="selected"';
				$o .= '>'.$v.'</option>';
			}
		}
		closedir($fd);
	}
	$o .= '</select>';
}

function im($n, $p) {
	if (!isset($_FILES)) {
		global $_FILES;
		$_FILES = $GLOBALS['HTTP_POST_FILES'];
	}
	if (isset($_FILES[$n][$p]))return $_FILES[$n][$p];
	else return'';
}

// Adm functionality

if ($adm) {

	if ($validate)$f = 'validate';
	if ($settings)$f = 'settings';
	if ($sysinfo)$f = 'sysinfo';
	if ($phpinfo)$f = 'phpinfo';
	if ($file)$f = 'file';
	if ($images || $function == 'images')$f = 'images';
	if ($downloads || $function == 'downloads')$f = 'downloads';
	if ($function == 'save')$f = 'save';

	if ($f == 'settings' || $f == 'images' || $f == 'downloads' || $f == 'validate' || $f == 'sysinfo' || $f == 'phpinfo') {
		$title = $tx['title'][$f];
		$o .= "\n\n".'<h1>'.$title.'</h1>'."\n";
	}

// System Info and Help Links - GE 2010-10-28

if($f == 'sysinfo')
{
$o = '<h4>'.$tx['sysinfo']['headline'].'</h4>'."\n";
$o.= '<p><b>'.$tx['sysinfo']['version'].'</b></p>'."\n";
$o.= '<ul>'."\n".'<li>'.CMSIMPLE_XH_VERSION.'&nbsp;&nbsp;Build: '.CMSIMPLE_XH_BUILD.'</li>'."\n".'</ul>'."\n"."\n";

$o.='<p><b>'.$tx['sysinfo']['plugins'].'</b></p>'."\n"."\n";

$handle1 = opendir($pth['folder']['plugins']);
$o.='<ul>'."\n";
while ($plugin1 = readdir($handle1)) 
	{
	if ($plugin1 != '.' && $plugin1 != '..' && $plugin1 != $pluginloader_cfg['foldername_pluginloader'] && is_dir($pth['folder']['plugins'].$plugin1)) 
		{
		$o.= '<li>'.ucfirst($plugin1).'</li>'."\n";
		}
	}
$o.='</ul>'."\n"."\n";

$o.= '<p><b>'.$tx['sysinfo']['php_version'].'</b></p>'."\n".'<ul>'."\n".'<li>'.phpversion().'</li>'."\n".'<li><a href="./?&phpinfo" target="blank"><b>'.$tx['sysinfo']['phpinfo_link'].'</b></a> &nbsp; '.$tx['sysinfo']['phpinfo_hint'].'</li>'."\n".'</ul>'."\n"."\n";

$o.='<h4>'.$tx['sysinfo']['helplinks'].'</h4>'."\n"."\n";
$o.='<ul>
<li><a href="http://www.cmsimple-xh.com/">cmsimple-xh.com &raquo;</a></li>
<li><a href="http://www.cmsimple.org/">cmsimple.org &raquo;</a></li>
<li><a href="http://www.cmsimpleforum.com/">cmsimpleforum.com &raquo;</a></li>
<li><a href="http://www.cmsimplewiki.com/">cmsimplewiki.com &raquo;</a></li>
</ul>'."\n"."\n";
}

// PHP Info - GE 2010-10-28

if($f == 'phpinfo')
{
phpinfo();
exit;
}

// SETTINGS

	if ($f == 'settings') {
		$o .= '<p>'.$tx['settings']['warning'].'</p>'."\n".'<h4>'.$tx['settings']['systemfiles'].'</h4>'."\n".'<ul>'."\n";
		foreach(array('config', 'language') as $i)$o .= '<li><a href="'.$sn.'?file='.$i.amp().'action=array">'.ucfirst($tx['action']['edit']).' '.$tx['filetype'][$i].'</a></li>'."\n";
		foreach(array('stylesheet', 'template') as $i)$o .= '<li><a href="'.$sn.'?file='.$i.amp().'action=edit">'.ucfirst($tx['action']['edit']).' '.$tx['filetype'][$i].'</a></li>'."\n";
		foreach(array('log') as $i)$o .= '<li><a href="'.$sn.'?file='.$i.amp().'action=view">'.ucfirst($tx['action']['view']).' '.$tx['filetype'][$i].'</a></li>'."\n".'</ul>'."\n";


// changed backup-area, added pagedata.php download, removed edit-funktion, added backupexplain3 - by MD/GE 2009/08 (CMSimple_XH beta2)

$o .= '<h4>'.$tx['settings']['backup'].'</h4><p>'.$tx['settings']['backupexplain3'].'</p>'."\n".'<ul>'."\n";
		foreach(array('content', 'pagedata') as $i)$o .= '<li>'.ucfirst($tx['filetype'][$i]).' <a href="'.$sn.'?file='.$i.amp().'action=view">'.$tx['action']['view'].'</a>'.' <a href="'.$sn.'?file='.$i.amp().'action=download">'.$tx['action']['download'].'</a></li>'."\n";
$o .= '</ul>'."\n".tag('hr')."\n".'<p>'.$tx['settings']['backupexplain1'].'</p>'."\n".'<p>'.$tx['settings']['backupexplain2'].'</p>'."\n".'<ul>'."\n";
        $fs = sortdir($pth['folder']['content']);
        foreach($fs as $p)if(preg_match("/\d{3}_content\.htm|\d{3}_pagedata\.php/", $p))$o .= '<li><a href="'.$sn.'?file='.$p.amp().'action=view">'.$p.'</a> ('.(round((filesize($pth['folder']['content'].'/'.$p))/102.4)/10).' KB)</li>'."\n";
        $o .= '</ul>'."\n";
    }
// END modified backup-area (CMSimple_XH beta2)

	if ($f == 'images' || $f == 'downloads') {
		if ($f == 'images')$reg = "/\.gif$|\.jpg$|\.jpeg$|\.png$/i";
		else $reg = "/^[^\.]/i";
		if ($action == 'delete') {
			if (!(preg_match($reg, $GLOBALS[$f])))e('wrongext', 'file', $GLOBALS[$f]);
			else
				{
				if (@unlink($pth['folder'][$f].$GLOBALS[$f]))$o .= '<p>'.ucfirst($tx['filetype']['file']).' '.$GLOBALS[$f].' '.$tx['result']['deleted'].'</p>'."\n";
				else e('cntdelete', 'file', $GLOBALS[$f]);
			}
		}
		if ($action == 'upload') {
			$name = im($f, 'name');
			$size = im($f, 'size');
			if (!(preg_match($reg, $name)))e('wrongext', 'file', $name);
			else if(file_exists(rp($pth['folder'][$f].$name)))e('alreadyexists', 'file', $name);
			else if($size > $cf[$f]['maxsize'])$e .= '<li>'.ucfirst($tx['filetype']['file']).' '.$name.' '.$tx['error']['tolarge'].' '.$cf[$f]['maxsize'].' '.$tx['files']['bytes'].'</li>'."\n";
			if (!$e) {
				if (@move_uploaded_file(im($f, 'tmp_name'), $pth['folder'][$f].$name)) {
                                    chmod($pth['folder'][$f].$name, 0644);
                                    $o .= '<p>'.ucfirst($tx['filetype']['file']).' '.$name.' '.$tx['result']['uploaded'].'</p>'."\n";
                                }
				else e('cntsave', 'file', $name);
			}
		}
		if ($cf[$f]['maxsize'] > 0)$o .= '<form method="POST" action="'.$sn.'" enctype="multipart/form-data">'."\n".'<p>'.tag('input type="file" class="file" name="'.$f.'" size="30"')."\n".tag('input type="hidden" name="action" value="upload"')."\n".' '.tag('input type="hidden" name="function" value="'.$f.'"')."\n".tag('input type="submit" class="submit" value="'.ucfirst($tx['action']['upload']).'"')."\n".'</p>'."\n".'</form>'."\n";
		$o .= '<form method="post" action='.$sn.'>'."\n".'<table width="100%" cellpadding="5" cellspacing="0" border="0">'."\n";
		$totalsize = 0;
		if (@is_dir($pth['folder'][$f])) {
			$fs = sortdir($pth['folder'][$f]);
			foreach($fs as $p) {
				if (preg_match($reg, $p)) {
					$totalsize += filesize($pth['folder'][$f].$p);
					$o .= '<tr>'."\n".'<td>'."\n".tag('input type="radio" class="radio" name="'.$f.'" value="'.$p.'"')."\n".'</td>'."\n".'<td>';
					if ($f == 'images')$o .= '<img src="'.$pth['folder'][$f].$p.'">'.tag('br');
					$o .= $p.' ('.(round((filesize($pth['folder'][$f].$p))/102.4)/10).' KB)';
					if ($f == 'images') {
						for($i = 0; $i < $cl; $i++) {
							$ic = preg_match_all('/<img src=["]*([^"]*?)'.'\/'.$p.'["]*(.*?)>/i', $c[$i], $matches, PREG_PATTERN_ORDER);
							if ($ic > 0)$o .= tag('br').$tx[$f]['usedin'].' '.a($i, '').$h[$i].'</a>';
						}
					}
					$o .= '</td>'."\n".'</tr>'."\n";
				}
			}
			$o .= '</table>'."\n".tag('br').tag('input type="hidden" name="action" value="delete"')."\n".tag('input type="hidden" name="function" value="'.$f.'"')."\n";
			if ($totalsize > 0)$o .= tag('input type="submit" class="submit" value="'.ucfirst($tx['action']['delete']).'"')."\n";
			$o .= "\n".'</form>'."\n";
			$o .= '<p>'.$tx['files']['totalsize'].': '.(round($totalsize/102.4)/10).' KB</p>'."\n";
		}
		else e('cntopen', 'folder', $pth['folder'][$f]);
	}

	if ($f == 'file') {
		if (preg_match("/\d{3}_content\.htm|\d{3}_pagedata\.php/", $file))$pth['file'][$file] = $pth['folder']['content'].'/'.$file;
		if ($pth['file'][$file] != '') {
			if ($action == 'view') {
				header('Content-Type: text/plain');
				echo rmnl(rf($pth['file'][$file]));
				exit;
			}
			if ($action == 'download') {
			download($pth['file'][$file]);
			} else {
				initvar('form');
				if ($action == 'array') $form = 'array';
				if ($form == 'array') {
					if ($file == 'language')$a = 'tx';

// disables editing of site title, keywords and description in config - by MD 2009-09 (CMSimple_XH beta3.2)

					if ($file == 'config'){
						foreach($tx['meta'] as $key => $param){
						if(isset($cf['meta'][$key])){unset($cf['meta'][$key]);}
						}
						foreach($tx['site'] as $key => $param){
						if(isset($cf['site'][$key])){unset($cf['site'][$key]);}
						}
					$a = 'cf';
					}
					if ($file == 'plugin_config') { $a = 'plugin_cf'; }
					if ($file == 'plugin_language') { $a = 'plugin_tx'; }

// END of disable editing of site title, keywords and description in config - by MD 2009-09 (CMSimple_XH beta3.2)

				}
				if ($action == 'save') {
					if ($form == 'array') {
						$text = "<?php\n";
                                                $text.= "/* utf8-marker = äöüß */\n";
						foreach($GLOBALS[$a] as $k1 => $v1) {
							if (is_array($v1)) {
								foreach($v1 as $k2 => $v2) {
									if (!is_array($v2)) {
										initvar($k1.'_'.$k2);
										$GLOBALS[$a][$k1][$k2] = $GLOBALS[$k1.'_'.$k2];
										$GLOBALS[$a][$k1][$k2] = stsl($GLOBALS[$a][$k1][$k2]);
									if ($k1.$k2 == 'editorbuttons')$text .= '$'.$a.'[\''.$k1.'\'][\''.$k2.'\']=\''.$GLOBALS[$a][$k1][$k2].'\';';
									else $text .= '$'.$a.'[\''.$k1.'\'][\''.$k2.'\']="'.preg_replace("/\"/s", "", $GLOBALS[$a][$k1][$k2]).'";'."\n";
									}
								}
							}
						}
						$text .= '?>';
					}
					else $text = rmnl(stsl($text));
					if ($fh = @fopen($pth['file'][$file], "w")) {
						fwrite($fh, $text);
						fclose($fh);
						if ($file == 'config' || $file == 'language') {
							if (!@include($pth['file'][$file]))e('cntopen', $file, $pth['file'][$file]);
							if ($file == 'config') {
								$pth['folder']['template'] = $pth['folder']['templates'].$cf['site']['template'].'/';
							$pth['file']['template'] = $pth['folder']['template'].'template.htm';
								$pth['file']['stylesheet'] = $pth['folder']['template'].'stylesheet.css';
								$pth['folder']['menubuttons'] = $pth['folder']['template'].'menu/';
								$pth['folder']['templateimages'] = $pth['folder']['template'].'images/';
								if (!(preg_match('/\/[A-z]{2}\/[^\/]*/', sv('PHP_SELF')))) {
									$sl = $cf['language']['default'];
									$pth['file']['language'] = $pth['folder']['language'].$sl.'.php';
									if (!@include($pth['file']['language']))die('Language file '.$pth['file']['language'].' missing');
								}
							}
						}
					}
					else e('cntwriteto', $file, $pth['file'][$file]);
				}
				chkfile($file, true);
				$title = ucfirst($tx['action']['edit']).' '.(isset($tx['filetype'][$file])?$tx['filetype'][$file]:$file);
				$o .= '<h1>'.$title.'</h1>'."\n".'<form action="'.$sn.(isset($plugin)?'?'.amp().$plugin:'').'" method="post">';
				if ($form == 'array') {
					$o .= '<table width="100%" cellpadding="1" cellspacing="0" border="0">'."\n";
					foreach($GLOBALS[$a] as $k1 => $v1) {
					if(!@$plugin||$k1==@$plugin) {
						$o .= '<tr>'."\n".'<td colspan="2"><h4>'.ucfirst($k1).'</h4></td>'."\n".'</tr>'."\n";
						if (is_array($v1))foreach($v1 as $k2 => $v2)if(!is_array($v2)) {
							if (isset($tx['help'][$k1.'_'.$k2]) && $a == 'cf')$o .= '<tr>'."\n".'<td colspan="2"><b>'.$tx['help'][$k1.'_'.$k2].':</b></td>'."\n".'</tr>'."\n";
							$o .= '<tr>'."\n".'<td valign="top">'.$k1.'_'.$k2.':</td>'."\n".'<td>';
							if ($k1.$k2 == 'editorbuttons')$o .= '<textarea rows="25" cols="35" name="'.$k1.'_'.$k2.'">'.$v2.'</textarea>';
							else if($k1.$k2 == 'securitytype') {
								$o .= '<select name="'.$k1.'_'.$k2.'">';
								foreach(array('page', 'javascript', 'wwwaut') as $v) {
									$o .= '<option value="'.$v.'"';
									if ($v == $v2) $o .= ' selected="selected"';
									$o .= '>'.$v.'</option>';
								}
								$o .= '</select>';
							}
							else if($k1.$k2 == 'languagedefault')selectlist('language', "/^[a-z]{2}\.php$/i", "/^([a-z]{2})\.php$/i");
							else if($k1.$k2 == 'sitetemplate')selectlist('templates', "/^[^\.]*$/i", "/^([^\.]*)$/i");
							else $o .= tag('input type="text" class="text" name="'.$k1.'_'.$k2.'" value="'.$v2.'" size="30"')."\n";
							$o .= '</td>'."\n".'</tr>'."\n";
						}}
					}
					$o .= '</table>'."\n".tag('input type="hidden" name="form" value="'.$form.'"')."\n";
				}
				else $o .= '<textarea rows="25" cols="50" name="text" class="cmsimplecore_file_edit">'.rmnl(rf($pth['file'][$file])).'</textarea>';
                if($admin)$o .= tag('input type="hidden" name="admin" value="'.$admin.'"')."\n";
				$o .= tag('input type="hidden" name="file" value="'.$file.'"')."\n".tag('input type="hidden" name="action" value="save"')."\n".' '.tag('input type="submit" class="submit" value="'.ucfirst($tx['action']['save']).'"')."\n".'</form>'."\n";
			}
		}
	}

// new linkcheck - by MD 2009-09 (CMSimple_XH beta3)

	if ($f == 'validate') {$o .= check_links(); }
}


if ($s == -1 && !$f && $o == '' && $su == '') {
	$s = 0;
	$hs = 0;
}
// END new linkcheck (CMSimple_XH beta3)

// SAVE

if ($adm && $f == 'save') {
	$ss = $s;
	$c[$s] = preg_replace("/<h[1-".$cf['menu']['levels']."][^>]*>(\&nbsp;| )?<\/h[1-".$cf['menu']['levels']."]>/i", "", stsl($text));

	if ($s == 0)if(!preg_match("/^<h1[^>]*>.*<\/h1>/i", rmanl($c[0])) && !preg_match("/^(<p[^>]*>)?(\&nbsp;| |<br \/>)?(<\/p>)?$/i", rmanl($c[0])))$c[0] = '<h1>'.$tx['toc']['missing'].'</h1>'."\n".$c[0];
	$title = ucfirst($tx['filetype']['content']);
	if ($fh = @fopen($pth['file']['content'], "w")) {
		fwrite($fh, '<html><head>'.head().'</head><body>'."\n");
		foreach($c as $i) {
			fwrite($fh, rmnl($i."\n"));
		}
		fwrite($fh, '</body></html>');
		fclose($fh);
		rfc();
	}
	else e('cntwriteto', 'content', $pth['file']['content']);
	$title = '';
}

// EDITOR CALL

if ($adm && $edit && (!$f || $f == 'save') && !$download) {
	if (isset($ss))if($s < 0 && $ss < $cl)$s = $ss;
		if ($s > -1) {
		$su = $u[$s];
		$iimage = '';
		if ($cf['editor']['external'] == '')$cf['editor']['external'] = 'oedit';
		if (!@include($pth['folder']['cmsimple'].$cf['editor']['external'].'.php'))$e .= '<li>External editor '.$cf['editor']['external'].' missing</li>'."\n";
	}
	else $o = '<p>'.$tx['error']['cntlocateheading'].'</p>'."\n";
}
/**
 * collects the links
 * calls the appropriate fucntion to check each link
 * passes the results to
 *
 *
 * @global <array> $c - the cmsimple pages
 * @global <array> $u - the urls
 * @global <array> $h - the headings
 * @global <int> $cl  - the number of pages
 * @global <string> $o - the output string
 */
function check_links(){
    global $c, $u, $h, $cl, $o;
    $checkedLinks = 0;
    for($i = 0; $i < $cl; $i++) {
        preg_match_all('/<a.*?href=["]*([^"]*)["]*.*?>(.*?)<\/a>/i', $c[$i], $pageLinks);
        if(count($pageLinks[1]) > 0){


// First change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
foreach($pageLinks[1] as $link){
   if(strpos($link, '#')=== 0){
        $hrefs[$i][] = '?'.$u[$i].$link;
   }else {
        $hrefs[$i][] = $link;
   }
} 
// END first change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)


            $texts[$i] = $pageLinks[2];
            $checkedLinks += count($pageLinks[1]);
        }
    }
    $hints = array();
    $i = 0;
    foreach($hrefs as $index => $currentLinks){
       foreach($currentLinks as $counter => $link){
           $parts = parse_url($link);
           switch ($parts['scheme']) {
                case 'http':  $status = check_external_link($parts);
                    break;
                case 'mailto': $status = 'mailto';
                    break;
               default: $status = check_internal_link($parts);
                    break;
           }
           if($status == '200'){continue;}
           if($status == '400' || $status == '404'
              || $status == '500' || $status == 'internalfail'
              || $status == 'externalfail' || $status == 'content not found' || $status == 'file not found')
              {
                      $hints[$index]['errors'][] = array($status, $link, $texts[$index][$counter]);
                      continue;
           }
          $hints[$index]['caveats'][] = array($status, $link, $texts[$index][$counter]);
       }
       $i++;
    }
    return linkcheck_message($checkedLinks, $hints);
}


/**
 * checks internal link -  all languages
 * (requires the function read_content_file)
 *
 * @param <array> $test (parsed url)
 * @return <string> on success: '200' else 'internalfail'
 */


// Second change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
function check_internal_link($test){
global $c, $u, $cl, $sn, $pth, $sl, $cf, $pth;  // add $pth to globals
$template = file_get_contents($pth['file']['template']); // read it
// END second change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)


    list($query) = explode('&', $test['query']);
    $pageLinks = array();
    $pageContents = array();
    $contentLength = $cl;

    preg_match('/\/([A-z]{2})\/[^\/]*/', $test['path'], $lang);
    $lang = $lang[1];

   if(isset($test['path'])){
           $query = str_replace('/'.$lang.'/?', '', $query);
            $content = read_content_file($lang);
            if(!$content){return 'content not found';}
            $urls = $content[0];
            $pages = $content[1];
            $contentLength = count($urls);
    }else{  $urls = $u;
            $pages = $c;
    }
    for($i = 0; $i < $contentLength; $i++){
          if($urls[$i] == $query){
           if(!$test['fragment']){
              return 200;
            }
            if(preg_match('/<[^>]*[id|name]\s*=\s*"'.$test['fragment'].'"/i', $pages[$i])){
                  return 200;
            }

// Third change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
            if(preg_match('/<[^>]*[id|name]\s*=\s*"'.$test['fragment'].'"/i', $template)) // check for anchor in template
            {
    return 200;
            } 
// END third change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)

        }
    }

    $parts = explode('=', $test['query']);
    if($parts[0] == 'download'){
        if(file_exists($pth['folder']['downloads']. $parts[1])){
            return 200;
        }else {
            return 'file not found';
        }
    }
    $parts = explode('/', $test['path']);
    if($parts[1] == 'downloads'){
        if(file_exists($pth['folder']['downloads']. $parts[2])){
            return 200;
        }else {
            return 'file not found';
        }
    }
    return 'internalfail';
}

/**
 * checks web links and returns the status code
 *
 * @param <array> $parts (parsed url)
 * @return <string> status code
 */
function check_external_link($parts){
    $host = $parts['host'];
    $fh = fsockopen($parts['host'],80,$errno,$errstr,5);
     if($fh){
        $path = isset($parts['path']) ? $parts['path'] : '/';  // LM CMSimple_XH 1.1
        if(substr($path,-1) !== '/' && substr_count($path, '.') == 0){$path .= '/';}
        if(isset($parts['query'])){$path .= "?" . $parts['query'];}
        fwrite($fh, "GET ".$path." HTTP/1.1\r\nHost: ".$host."\r\nUser-Agent: CMSimple_XH Link-Checker\r\n\r\n");
        $response = fread($fh, 12);
        $status = substr($response, 9);
        fclose($fh);
        return($status);
    }
    return 'externalfail';
}

// new linkcheck - by MD 2009-08 (CMSimple_XH beta3)
/**
 * prepares the html output for the linkcheck results
 *
 * @todo internalization
 *
 * @global <array> $tx
 * @global <array> $h
 * @global <array> $u
 * @param <int> $checkedLinks - number of checked links
 * @param <array> $hints - the errors an warnings
 * @return <string>
 */
function linkcheck_message($checkedLinks, $hints){
    global $tx, $h, $u;
    $html = "\n".'<p>'.$checkedLinks.$tx['link']['checked'].'</p>'."\n";  // LM CMSimple_XH 1.1
    if(count($hints)== 0){ $html .= '<p><b>'.$tx['link']['check_ok'].'</b></p>'."\n";
                           return $html;
    }
    $html .= '<p><b>'.$tx['link']['check_errors'].'</b></p>'."\n";
    $html .= '<p>'.$tx['link']['check'].'</p>'."\n";
    foreach($hints as $page => $problems){
        $html .= tag('hr')."\n\n".'<h4>'.$tx['link']['page'].'<a href="?'.$u[$page].'">'.$h[$page].'</a></h4>'."\n";
        if(isset($problems['errors'])){
            $html .= '<h4>'.$tx['link']['errors'].'</h4>'."\n".'<ul>'."\n";
            foreach($problems['errors'] as $error){
             $html .= '<li>'."\n".'<b>'.$tx['link']['link'].'</b><a href="'.$error[1].'">'.$error[2].'</a>'.tag('br')."\n";
             $html .= '<b>'.$tx['link']['linked_page'].'</b>'.$error[1].tag('br')."\n";
             if((int)$error[0]){
                $html .= '<b>'.$tx['link']['error'].'</b>'.$tx['link']['ext_error_page'].tag('br')."\n";
                $html .= '<b>'.$tx['link']['returned_status'].'</b>'.$error[0];
              }
              if($error[0] == 'internalfail'){
                  $html .= '<b>'.$tx['link']['error'].'</b>'.$tx['link']['int_error'];
              }
              if($error[0] == 'externalfail'){
                  $html .= '<b>'.$tx['link']['error'].'</b>'.$tx['link']['ext_error_domain'];
              }
              if($error[0] == 'content not found'){
                  $html .= '<b>'.$tx['link']['error'].'</b>'.$tx['link']['int_error'];
              }
              $html .= "\n".'</li>'."\n";
            }
            $html .= '</ul>'."\n"."\n";
        }
        if(isset($problems['caveats'])){
            $html .= '<h4>'.$tx['link']['hints'].'</h4>'."\n".'<ul>'."\n";
            foreach($problems['caveats'] as $notice){
               $html .= '<li>'."\n".'<b>'.$tx['link']['link'].'</b>'.'<a href="'.$notice[1].'">'.$notice[2].'</a>'.tag('br')."\n";
               $html .= '<b>'.$tx['link']['linked_page'].'</b>'.$notice[1].tag('br')."\n";
               if((int)$notice[0]){
                  if((int)$notice[0] >= 300 && (int)$notice[0] < 400){
                    $html .= '<b>'.$tx['link']['error'].'</b>'.$tx['link']['redirect'].tag('br')."\n";
                  }
                    $html .= '<b>'.$tx['link']['returned_status'].'</b>'.$notice[0]."\n";
              }
              else{
                  if($notice[0] == 'mailto'){
                      $html .= $tx['link']['email']."\n";
                  }
                  else{
                      $html .= $tx['link']['unknown']."\n";
                  }
            $html .= '</li>'."\n";
              }
            }
            $html .= '</ul>'."\n";
        }
    }
    return $html;
}
/**
 *
 * @global <array> $cf
 * @param <string> $path
 * @return <array> - contains <array> $urls, <array> $pages, <array> $headings, <array> $levels
 */
function read_content_file($path){

    global $cf, $sl;
    $path = basename($path);
    if($sl == $cf['language']['default']) {
        $path = './'. $path;
    }else {$path = '../'. $path;}
    $sep = $cf['uri']['seperator'];
    $pattern = '/<h([1-'.$cf['menu']['levels'].'])[^>]*>(.*)<\/h/i';

    $content = file_get_contents($path. '/content/content.htm');
    if(!$content){return false;}
    preg_match_all($pattern, $content, $matches); // LM CMSimple_XH 1.1

    $headings = array();
    $levels = array();
    $urls = array();

    if(count($matches[0])== 0){return;}
    $ancestors = array();
    foreach($matches[1] as $level){
                $levels[] = (int)$level;
    }
    $i = 0;
    foreach($matches[2] as $chapter){
                $heading = trim(strip_tags($chapter));
                $url = uenc($heading); //in cms.php: handles $tx['urichar']
                $headings[] = $heading;
                $level = $levels[$i];
                $ancestors[$level] = $url;
                $myself = array_slice($ancestors,0, $level);
                $urls[] = implode($sep, $myself);
                $i++;
     }
    $pages = preg_split($pattern, $content);
    $pages = array_slice($pages, 1); // $pages[0] is the header part - drop it!
    return array($urls, $pages, $headings, $levels);
}
?>