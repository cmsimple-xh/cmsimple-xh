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


if (preg_match('/adm.php/i', sv('PHP_SELF'))) {
    die('Access Denied');
}


/**
 * Returns the (X)HTML for a config option selectbox.
 *
 * @param   string $fn  The folder.
 * @param   string $regm  The regex to match.
 * @param   string $regr  The regex for replacement.
 * @result  string
 */ 
function selectlist($fn, $regm, $regr)
{
    global $k1, $k2, $v2, $o, $pth, $cf, $tx;
    
    $o .= '<select name="' . $k1 . '_' . $k2 . '">';
    if ($fd = @opendir($pth['folder'][$fn])) {
        while (($p = @readdir($fd)) !== false) {
            if (preg_match($regm, $p)) {
                $v = preg_replace($regr, '$1', $p);
                $options[$v] = ($v == $v2);
            }
        }
        closedir($fd);
    }
    ksort($options, SORT_STRING);
    if($k1 . $k2 == 'subsitetemplate')  // FIXME: might result in 2 options selected!
    {
        $o .= '<option value="" selected="selected">' . $tx['template']['default'] . '</option>';
    }
    foreach ($options as $option => $selected) {
        $o .= '<option value="' . $option . '"';
        if ($selected) {
            $o .= ' selected="selected"';
        }
        $o .= '>' . $option . '</option>';
    }
    $o .= '</select>';
}


if ($adm) {
    
    if ($validate) {
        $f = 'validate';
    }
    if ($settings) {
        $f = 'settings';
    }
    if (isset($sysinfo)) { // FIXME: why isset() here and not in the other ifs?
        $f = 'sysinfo';
    }
    if (isset($phpinfo)) { // FIXME: why isset() here and not in the other ifs?
        $f = 'phpinfo';
    }
    if ($file) {
        $f = 'file';
    }
    if ($userfiles) {
        $f = 'userfiles';
    }
    if ($images || $function == 'images') {
        $f = 'images';
    }
    if ($downloads || $function == 'downloads') {
        $f = 'downloads';
    }
    if ($function == 'save') {
        $f = 'save';
    }

    if ($f == 'settings' || $f == 'images' || $f == 'downloads'
        || $f == 'validate' || $f == 'sysinfo' || $f == 'phpinfo')
    {
        $title = $tx['title'][$f];
        $o .= "\n\n" . '<h1>' . $title . '</h1>' . "\n";
    }

// FIXME: change ifs to switch
    
// System Info and Help Links - GE 2010-10-28

    if ($f == 'sysinfo') {
        $o .= '<p><b>' . $tx['sysinfo']['version'] . '</b></p>' . "\n";
        $o .= '<ul>' . "\n" . '<li>' . CMSIMPLE_XH_VERSION . '&nbsp;&nbsp;Released: '
            . CMSIMPLE_XH_DATE . '</li>' . "\n" . '</ul>' . "\n" . "\n";

        $o .= '<p><b>' . $tx['sysinfo']['plugins'] . '</b></p>' . "\n" . "\n";

        $handle1 = opendir($pth['folder']['plugins']);
        if ($handle1) {
            $o .= '<ul>' . "\n";
            while (($plugin1 = readdir($handle1)) !== false) {
                if (strpos($plugin1, '.') === false
                    && $plugin1 != $pluginloader_cfg['foldername_pluginloader']
                    && is_dir($pth['folder']['plugins'] . $plugin1))
                {
                    $o .= '<li>' . ucfirst($plugin1) . '</li>' . "\n";
                }
            }
            $o .= '</ul>' . "\n" . "\n";
            closedir($handle1);
        }

        $o .= '<p><b>' . $tx['sysinfo']['php_version'] . '</b></p>' . "\n"
            . '<ul>' . "\n" . '<li>' . phpversion() . '</li>' . "\n"
            . '<li><a href="./?&phpinfo" target="blank"><b>'
            . $tx['sysinfo']['phpinfo_link'] . '</b></a> &nbsp; '
            . $tx['sysinfo']['phpinfo_hint'] . '</li>' . "\n" . '</ul>' . "\n" . "\n";

        $o .= '<h4>' . $tx['sysinfo']['helplinks'] . '</h4>' . "\n" . "\n";
        $o .= '<ul>'
            . '<li><a href="http://www.cmsimple-xh.com/">cmsimple-xh.com &raquo;</a></li>'
            . '<li><a href="http://www.cmsimple.org/">cmsimple.org &raquo;</a></li>'
            . '<li><a href="http://www.cmsimpleforum.com/">cmsimpleforum.com &raquo;</a></li>'
            . '<li><a href="http://www.cmsimplewiki.com/">cmsimplewiki.com &raquo;</a></li>'
            . '</ul>' . "\n" . "\n";

        $temp = array('phpversion' => '4.3',
            'extensions' => array(
                array('date', false),
                'pcre',
                array('session', false),
                array('xml', false)),
            'writable' => array(),
            'other' => array());
        foreach (array('content', 'images', 'downloads', 'userfiles', 'media') as $i) {
            $temp['writable'][] = $pth['folder'][$i];
        }
        foreach (
            array('config', 'log', 'language', 'langconfig', 'content',
                'pagedata', 'template', 'stylesheet') as $i)
        {
            $temp['writable'][] = $pth['file'][$i];
        }
        $temp['writable'] = array_unique($temp['writable']);
        sort($temp['writable']);
        $temp['other'][] = array(strtoupper($tx['meta']['codepage']) == 'UTF-8',
                                 true, $tx['syscheck']['encoding']);
        $temp['other'][] = array(!get_magic_quotes_runtime(),
                                 false, $tx['syscheck']['magic_quotes']);
        $o .= XH_systemCheck($temp);
    }

// PHP Info - GE 2010-10-28

    if ($f == 'phpinfo') {
        phpinfo();
        exit;
    }

// SETTINGS

    if ($f == 'settings') {
        $o .= '<p>' . $tx['settings']['warning'] . '</p>' . "\n"
            . '<h4>' . $tx['settings']['systemfiles'] . '</h4>' . "\n" . '<ul>' . "\n";


        // for subsites - edit config.php only from the main site / default language

        if ($sl == $cf['language']['default']) { // FIXME: simplify
            foreach (array('config', 'langconfig', 'language') as $i) {
                $o .= '<li><a href="' . $sn . '?file=' . $i . '&amp;action=array">'
                    . utf8_ucfirst($tx['action']['edit']) . ' '
                    . $tx['filetype'][$i] . '</a></li>' . "\n";
            }
        } else {
            foreach (array('langconfig', 'language') as $i) {
                $o .= '<li><a href="' . $sn . '?file=' . $i . '&amp;action=array">'
                    . utf8_ucfirst($tx['action']['edit']) . ' '
                    . $tx['filetype'][$i] . '</a></li>' . "\n";
            }
        }

        // END for subsites


        foreach (array('stylesheet', 'template') as $i) {
            $o .= '<li><a href="' . $sn . '?file=' . $i . '&amp;action=edit">'
                . utf8_ucfirst($tx['action']['edit']) . ' '
                . $tx['filetype'][$i] . '</a></li>' . "\n";
        }
        foreach (array('log') as $i) {
            $o .= '<li><a href="' . $sn . '?file=' . $i . '&amp;action=view">'
                . utf8_ucfirst($tx['action']['view']) . ' '
                . $tx['filetype'][$i] . '</a></li>' . "\n";
        }
        $o .= '</ul>' . "\n";


        $o .= '<h4>' . $tx['settings']['backup'] . '</h4><p>'
            . $tx['settings']['backupexplain3'] . '</p>' . "\n" . '<ul>' . "\n";
        foreach (array('content', 'pagedata') as $i) {
            $o .= '<li>' . utf8_ucfirst($tx['filetype'][$i]) . ' <a href="'
                . $sn . '?file=' . $i . '&amp;action=view">'
                . $tx['action']['view'] . '</a>' . ' <a href="' . $sn . '?file='
                . $i . '&amp;action=download">' . $tx['action']['download']
                . '</a></li>' . "\n";
        }
        $o .= '</ul>' . "\n" . tag('hr') . "\n" . '<p>'
            . $tx['settings']['backupexplain1'] . '</p>' . "\n" . '<p>'
            . $tx['settings']['backupexplain2'] . '</p>' . "\n" . '<ul>' . "\n";
        $fs = sortdir($pth['folder']['content']);
        foreach ($fs as $p) {
            if (preg_match('/^\d{8}_\d{6}_(?:content.htm|pagedata.php)$/', $p)) {
                $o .= '<li><a href="' . $sn . '?file=' . $p . '&amp;action=view">'
                    . $p . '</a> ('
                    . (round((filesize($pth['folder']['content'] . '/' . $p)) / 102.4) / 10)
                    . ' KB)</li>' . "\n";
            }
        }
        $o .= '</ul>' . "\n";
    }

    if ($f == 'file') {
        if (preg_match('/^\d{8}_\d{6}_(?:content.htm|pagedata.php)$/', $file)) {
            $pth['file'][$file] = $pth['folder']['content'] . '/' . $file;
        }
        if ($pth['file'][$file] != '') {
            if ($action == 'view') {
                header('Content-Type: text/plain; charset=utf-8');
                echo rmnl(rf($pth['file'][$file]));
                exit;
            }
            if ($action == 'download') {
                download($pth['file'][$file]);
            } else {
                initvar('form');
                if ($action == 'array') {
                    $form = 'array';
                }
                if ($form == 'array') {
                    // FIXME: use switch($file)
                    if ($file == 'language') {
                        $a = 'tx';
                    }
                    if ($file == 'langconfig') {
                        if($cf['language']['default'] == $sl) {
                            unset($txc['subsite']);
                        }
                        $a = 'txc';
                    }

                    // disables editing of site title, keywords and description in config - by MD 2009-09 (CMSimple_XH beta3.2)

                    if ($file == 'config') {
                        foreach ($txc['meta'] as $key => $param) {
                            if (isset($cf['meta'][$key])) {
                                unset($cf['meta'][$key]);
                            }
                        }
                        foreach ($txc['site'] as $key => $param) {
                            if (isset($cf['site'][$key])) {
                                unset($cf['site'][$key]);
                            }
                        }
                        foreach ($txc['mailform'] as $key => $param) {
                            if (isset($cf['mailform'][$key])) {
                                unset($cf['mailform'][$key]);
                            }
                        }
                        $a = 'cf';
                    }
                    if ($file == 'plugin_config') {
                        $a = 'plugin_cf';
                    }
                    if ($file == 'plugin_language') {
                        $a = 'plugin_tx';
                    }
                }
                
                if ($action == 'save') {
                    if ($form == 'array') {
                        $text = "<?php\n";
                        $text.= "/* utf8-marker = äöüß */\n";
                        foreach ($GLOBALS[$a] as $k1 => $v1) {
                            if (is_array($v1)) {
                                foreach ($v1 as $k2 => $v2) {
                                    if (!is_array($v2)) {
                                        initvar($k1 . '_' . $k2);
                                        $GLOBALS[$a][$k1][$k2] = stsl($GLOBALS[$k1 . '_' . $k2]);
                                        if (($k1 == 'security' || $k1 == 'subsite' && $GLOBALS[$a][$k1][$k2] != '')
                                            && $k2 == 'password')
                                        {
                                            if ($GLOBALS[$a][$k1][$k2] != stsl($_POST[$k1 . '_password_old'])) {
                                                $GLOBALS[$a][$k1][$k2] = $xh_hasher->HashPassword($GLOBALS[$a][$k1][$k2]);
                                            }
                                        }
                                        $text .= '$' . $a . '[\'' . $k1 . '\'][\'' . $k2 . '\']="'
                                            . addcslashes($GLOBALS[$a][$k1][$k2], "\0..\37\"\$\\") . '";' . "\n";
                                    }
                                }
                            }
                        }
                        $text .= '?>';
                    } else {
                        $text = stsl($text);
                    }
                    if ($fh = @fopen($pth['file'][$file], "w")) {
                        fwrite($fh, $text);
                        fclose($fh);
                        if ($file == 'config' || $file == 'language' || $file == 'langconfig') {
                            if (!@include($pth['file'][$file])) {
                                e('cntopen', $file, $pth['file'][$file]);
                            }
                            if ($file == 'config') {
                                $pth['folder']['template'] = $pth['folder']['templates'] . $cf['site']['template'] . '/';
                                $pth['file']['template'] = $pth['folder']['template'] . 'template.htm';
                                $pth['file']['stylesheet'] = $pth['folder']['template'] . 'stylesheet.css';
                                $pth['folder']['menubuttons'] = $pth['folder']['template'] . 'menu/';
                                $pth['folder']['templateimages'] = $pth['folder']['template'] . 'images/';
                                if (!(preg_match('/\/[A-z]{2}\/[^\/]*/', sv('PHP_SELF')))) {
                                    $sl = $cf['language']['default'];
                                    $pth['file']['language'] = $pth['folder']['language'] . $sl . '.php';
                                    $pth['file']['langconfig'] = $pth['folder']['language'] . $sl . 'config.php';
                                    if (!@include($pth['file']['language'])) {
                                        die('Language file ' . $pth['file']['language'] . ' missing');
                                    }
                                    if (!@include($pth['file']['langconfig'])) {
                                        die('Language config file ' . $pth['file']['langconfig'] . ' missing');
                                    }
                                }
                            }
                        }
                    } else {
                        e('cntwriteto', $file, $pth['file'][$file]);
                    }
                }
                chkfile($file, true);
                $title = utf8_ucfirst($tx['action']['edit']) . ' '
                    . (isset($tx['filetype'][$file]) ? $tx['filetype'][$file] : $file);
                $o .= '<h1>' . $title . '</h1>' . "\n";
                if (isset($a) && $a=='txc' && $cf['language']['default'] != $sl) {
                    $o .= '<p>' . "\n" . $tx['help']['subsite'] . "\n" .'</p>' . "\n";
                    $o .= '<p class="cmsimplecore_warning" style="text-align: center;">' . "\n"
                        . $tx['help']['langconfig'] . "\n" .'</p>' . "\n";
                }
                $o .= '<form action="' . $sn . (isset($plugin) ? '?&amp;' . $plugin : '') . '" method="post">';
                if ($form == 'array') {
                    $o .= tag('input type="submit" class="submit" value="'
                                . utf8_ucfirst($tx['action']['save']) . '"') . "\n";
                    $o .= '<table width="100%" cellpadding="1" cellspacing="0" border="0">' . "\n";
                    foreach ($GLOBALS[$a] as $k1 => $v1) {
                        if (!@$plugin || $k1 == @$plugin) {
                            if($file=='config') {
                                $o .= '<tr>' . "\n" . '<td colspan="2"><h4>'
                                    . str_replace('Mailform','',ucfirst($k1))
                                    . '</h4></td>' . "\n" . '</tr>' . "\n";
                            } else {
                                $o .= '<tr>' . "\n" . '<td colspan="2"><h4>'
                                    . ucfirst($k1) . '</h4></td>' . "\n" . '</tr>' . "\n";
                            }
                            if (is_array($v1)) {
                                foreach ($v1 as $k2 => $v2) {
                                    if (!is_array($v2)) {
                                        $o .= '<tr>' . "\n" . '<td valign="top">';
                                        if (isset($tx['help'][$k1 . '_' . $k2]) && ($a == 'cf' || $a == 'txc'))
                                            $o .= '<a href="#" onclick="return false" class="pl_tooltip">'
                                                . tag('img src = "' . $pth['folder']['flags'] . 'help_icon.png" alt="" class="helpicon"')
                                                . '<span>' . $tx['help'][$k1 . '_' . $k2] . '</span></a>' . "\n";
                                        $o .= "\n" . ucfirst($k2) . ':</td>' . "\n" . '<td>';
                                        if (($k1 == 'security' || $k1 == 'subsite') && $k2 == 'password') {
                                            $o .= tag('input type="hidden" name="' . $k1 . '_' . $k2 . '_old" value="' . $v2 . '"');
                                        }
                                        if ($k1 . $k2 == 'securitytype') {
                                            $o .= '<select name="' . $k1 . '_' . $k2 . '">';
                                            foreach (array('page', 'javascript') as $v) {
                                                $o .= '<option value="' . $v . '"';
                                                if ($v == $v2) {
                                                    $o .= ' selected="selected"';
                                                }
                                                $o .= '>' . $v . '</option>';
                                            }
                                            $o .= '</select>';
                                        } elseif ($k1 . $k2 == 'languagedefault') {
                                            selectlist('language', "/^[a-z]{2}\.php$/i", "/^([a-z]{2})\.php$/i");
                                        } else if ($k1 . $k2 == 'sitetemplate') {
                                            selectlist('templates', "/^[^\.]*$/i", "/^([^\.]*)$/i");
                                        } else if ($k1 . $k2 == 'subsitetemplate') {
                                            selectlist('templates', "/^[^\.]*$/i", "/^([^\.]*)$/i"); // for subsites
                                        } elseif ($a == 'cf') {
                                            // use input fields only in CMS config
                                            $o .= tag('input type="text" class="text" name="' . $k1 . '_' . $k2
                                                      . '" value="'.htmlspecialchars($v2, ENT_COMPAT, 'UTF-8')
                                                      . '" size="30"') . "\n";
                                        } elseif (utf8_strlen($v2) < 30) { // single line input field or textarea depending on text length
                                            $o .= '<textarea rows="2" cols="30" class="cmsimplecore_settings cmsimplecore_settings_short" name="'
                                                . $k1 . '_' . $k2 . '">' . htmlspecialchars($v2, ENT_COMPAT, 'UTF-8')
                                                . "</textarea>\n";
                                        } else {
                                            $o .= '<textarea rows="2" cols="30" class="cmsimplecore_settings" name="'
                                                . $k1 . '_' . $k2 . '">' . htmlspecialchars($v2, ENT_COMPAT, 'UTF-8')
                                                . "</textarea>\n";
                                        }
                                        $o .= '</td>' . "\n" . '</tr>' . "\n";
                                    }
                                }
                            }
                        }
                    }
                    $o .= '</table>' . "\n" . tag('input type="hidden" name="form" value="' . $form . '"') . "\n";
                } else {
                    $o .= '<textarea rows="25" cols="50" name="text" class="cmsimplecore_file_edit">'
                        . rf($pth['file'][$file]) . '</textarea>';
                }

                if (isset($admin) && $admin) {
                    $o .= tag('input type="hidden" name="admin" value="' . $admin . '"') . "\n";
                }
                $o .= tag('input type="hidden" name="file" value="' . $file . '"') . "\n"
                    . tag('input type="hidden" name="action" value="save"') . "\n" . ' '
                    . tag('input type="submit" class="submit" style="margin-top:1em;" value="'
                          . utf8_ucfirst($tx['action']['save']) . '"') . "\n" . '</form>' . "\n";
            }
        }
    } // end $f == 'file'

    // new linkcheck

    if ($f == 'validate') {
        $o .= check_links();
    }
}


if ($s == -1 && !$f && $o == '' && $su == '') {
    $s = 0;
    $hs = 0;
}

// SAVE

if ($adm && $f == 'save') {
    $ss = $s;
    $c[$s] = $text;

    if ($s == 0) {
        if (!preg_match("/^<h1[^>]*>.*<\/h1>/i", rmanl($c[0]))
            && !preg_match("/^(<p[^>]*>)?(\&nbsp;| |<br \/>)?(<\/p>)?$/i", rmanl($c[0])))
        {
            $c[0] = '<h1>' . $tx['toc']['missing'] . '</h1>' . "\n" . $c[0];
        }
    }
    $title = utf8_ucfirst($tx['filetype']['content']);

    if ($fh = @fopen($pth['file']['content'], "w")) {
        fwrite($fh, '<html><head><title>Content</title></head><body>' . "\n");
        foreach ($c as $i) {
            fwrite($fh, rmnl($i . "\n"));
        }
        fwrite($fh, '</body></html>');
        fclose($fh);

        preg_match('~<h[1-'.$cf['menu']['levels'].'][^>]*>(.+?)</h[1-'.$cf['menu']['levels'].']>~isu', $c[$s], $matches);
        if (count($matches) > 0) {
            $temp = explode($cf['uri']['seperator'], $selected);
            array_splice($temp, -1, 1, uenc(trim(xh_rmws(strip_tags($matches[1])))));
            $su = implode($cf['uri']['seperator'], $temp);
        } else {
            $su = $u[max($s - 1, 0)];
        }
        header("Location: " . $sn . "?" . $su);
        exit;
    } else {
        e('cntwriteto', 'content', $pth['file']['content']);
    }
    $title = '';
}

if ($adm && $edit && (!$f || $f == 'save') && !$download) {
    if (isset($ss)) {
        if ($s < 0 && $ss < $cl) {
            $s = $ss;
        }
    }
    if ($s > -1) {
        $su = $u[$s];

        $editor = $cf['editor']['external'] == '' || init_editor();
        if (!$editor) {
            $e .= '<li>'.sprintf('External editor %s missing', $cf['editor']['external']).'</li>'."\n";
        }
        $o .= '<form method="post" id="ta" action="' . $sn . '">'
                . tag('input type="hidden" name="selected" value="' . $u[$s] . '"')
                . tag('input type="hidden" name="function" value="save"')
                . '<textarea name="text" id="text" class="xh-editor" style="height: '
                . $cf['editor']['height'] . 'px; width: 100%;" rows="30" cols="80">'
                . htmlspecialchars($c[$s], ENT_COMPAT, 'UTF-8')
                . '</textarea>';
        if ($cf['editor']['external'] == '' || !$editor) {
            $o .= tag('input type="submit" value="' . utf8_ucfirst($tx['action']['save']) . '"');
        }
        $o .= '</form>';
    } else {
        $o .= '<p>' . $tx['error']['cntlocateheading'] . '</p>' . "\n";
    }
}

if ($adm && ((isset($images) && $images)
             || (isset($downloads) && $downloads)
             || (isset($userfiles) && $userfiles)
             || (isset($media) && $media)
             || $edit && (!$f || $f == 'save') && !$download))
{
    if ($cf['filebrowser']['external'] && !file_exists($pth['folder']['plugins'] . $cf['filebrowser']['external'])) {
        $e .= '<li>' . sprintf('External filebrowser %s missing', $cf['filebrowser']['external']) . '</li>' . "\n";
    }
}

if ($adm && $f == 'xhpages') {
    if ($cf['pagemanager']['external'] && !file_exists($pth['folder']['plugins'] . $cf['pagemanager']['external'])) {
        $e .= '<li>' . sprintf('External pagemanager %s missing', $cf['pagemanager']['external']) . '</li>' . "\n";
    }
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
function check_links() {
    global $c, $u, $h, $cl, $o;
    $checkedLinks = 0;
    for ($i = 0; $i < $cl; $i++) {
        preg_match_all('/<a.*?href=["]?([^"]*)["]?.*?>(.*?)<\/a>/is', $c[$i], $pageLinks);
        if (count($pageLinks[1]) > 0) {


// First change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
            foreach ($pageLinks[1] as $link) {
                if (strpos($link, '#') === 0) {
                    $hrefs[$i][] = '?' . $u[$i] . $link;
                } else {
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
    foreach ($hrefs as $index => $currentLinks) {
        foreach ($currentLinks as $counter => $link) {
            $parts = parse_url($link);
            switch ($parts['scheme']) {
                case 'http': $status = check_external_link($parts);
                    break;
                case 'mailto': $status = 'mailto';
                    break;
                case '': $status = check_internal_link($parts);
                    break;
                default: $status = 'unknown';
            }
            if ($status == '200') {
                continue;
            }
            if ($status == '400' || $status == '404'
                    || $status == '500' || $status == 'internalfail'
                    || $status == 'externalfail' || $status == 'content not found' || $status == 'file not found') {
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
function check_internal_link($test) {
    global $c, $u, $cl, $sn, $pth, $sl, $cf, $pth;  // add $pth to globals
    if (isset($test['path']) && !isset($test['query']) // link to a file
            && file_exists(dirname($_SERVER['SCRIPT_FILENAME']).'/'.$test['path'])) {
        return 200;
    }
    $template = file_get_contents($pth['file']['template']); // read it
// END second change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)

    // consider using parse_str()

    list($query) = explode('&', $test['query']);
    $pageLinks = array();
    $pageContents = array();
    $contentLength = $cl;

    preg_match('/\/([A-z]{2})\/[^\/]*/', $test['path'], $lang);
    $lang = $lang[1];

    if (isset($test['path'])) {
        $query = str_replace('/' . $lang . '/?', '', $query);
        $content = read_content_file($lang);
        if (!$content) {
            return 'content not found';
        }
        $urls = $content[0];
        $pages = $content[1];
        $contentLength = count($urls);
    } else {
        $urls = $u;
        $pages = $c;
    }
    for ($i = 0; $i < $contentLength; $i++) {
        if ($urls[$i] == $query) {
            if (!$test['fragment']) {
                return 200;
            }
            if (preg_match('/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i', $pages[$i])) {
                return 200;
            }

// Third change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
            if (preg_match('/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i', $template)) { // check for anchor in template
                return 200;
            }
// END third change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
        }
    }

    $parts = explode('=', $test['query']);

    if ($parts[0] == 'download' || $parts[0] == '&download' || $parts[0] == '&amp;download') {
        if (file_exists($pth['folder']['downloads'] . $parts[1])) {
            return 200;
        } else {
            return 'file not found';
        }
    }
    $parts = explode('/', $test['path']);
    if ($parts[1] == 'downloads' || $parts[1] == '&downloads' || $parts[1] == '&amp;downloads') {
        if (file_exists($pth['folder']['downloads'] . $parts[2])) {
            return 200;
        } else {
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
function check_external_link($parts) {
    set_time_limit(30);
    $host = $parts['host'];
    $fh = fsockopen($parts['host'], 80, $errno, $errstr, 5);
    if ($fh) {
        $path = isset($parts['path']) ? $parts['path'] : '/';  // LM CMSimple_XH 1.1
        //if (substr($path, -1) !== '/' && substr_count($path, '.') == 0) {
        //    $path .= '/';
        //}
        if (isset($parts['query'])) {
            $path .= "?" . $parts['query'];
        }
        fwrite($fh, "GET " . $path . " HTTP/1.1\r\nHost: " . $host . "\r\nUser-Agent: CMSimple_XH Link-Checker\r\n\r\n");
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
function linkcheck_message($checkedLinks, $hints) {
    global $tx, $h, $u;
    $html = "\n" . '<p>' . $checkedLinks . $tx['link']['checked'] . '</p>' . "\n";  // LM CMSimple_XH 1.1
    if (count($hints) == 0) {
        $html .= '<p><b>' . $tx['link']['check_ok'] . '</b></p>' . "\n";
        return $html;
    }
    $html .= '<p><b>' . $tx['link']['check_errors'] . '</b></p>' . "\n";
    $html .= '<p>' . $tx['link']['check'] . '</p>' . "\n";
    foreach ($hints as $page => $problems) {
        $html .= tag('hr') . "\n\n" . '<h4>' . $tx['link']['page'] . '<a href="?' . $u[$page] . '">' . $h[$page] . '</a></h4>' . "\n";
        if (isset($problems['errors'])) {
            $html .= '<h4>' . $tx['link']['errors'] . '</h4>' . "\n" . '<ul>' . "\n";
            foreach ($problems['errors'] as $error) {
                $html .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b><a href="' . $error[1] . '">' . $error[2] . '</a>' . tag('br') . "\n";
                $html .= '<b>' . $tx['link']['linked_page'] . '</b>' . $error[1] . tag('br') . "\n";
                if ((int) $error[0]) {
                    $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['ext_error_page'] . tag('br') . "\n";
                    $html .= '<b>' . $tx['link']['returned_status'] . '</b>' . $error[0];
                }
                if ($error[0] == 'internalfail') {
                    $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['int_error'];
                }
                if ($error[0] == 'externalfail') {
                    $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['ext_error_domain'];
                }
                if ($error[0] == 'content not found') {
                    $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['int_error'];
                }
                $html .= "\n" . '</li>' . "\n";
            }
            $html .= '</ul>' . "\n" . "\n";
        }
        if (isset($problems['caveats'])) {
            $html .= '<h4>' . $tx['link']['hints'] . '</h4>' . "\n" . '<ul>' . "\n";
            foreach ($problems['caveats'] as $notice) {
                $html .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>' . '<a href="' . $notice[1] . '">' . $notice[2] . '</a>' . tag('br') . "\n";
                $html .= '<b>' . $tx['link']['linked_page'] . '</b>' . $notice[1] . tag('br') . "\n";
                if ((int) $notice[0]) {
                    if ((int) $notice[0] >= 300 && (int) $notice[0] < 400) {
                        $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['redirect'] . tag('br') . "\n";
                    }
                    $html .= '<b>' . $tx['link']['returned_status'] . '</b>' . $notice[0] . "\n";
                } else {
                    if ($notice[0] == 'mailto') {
                        $html .= $tx['link']['email'] . "\n";
                    } else {
                        $html .= $tx['link']['unknown'] . "\n";
                    }
                    $html .= '</li>' . "\n";
                }
            }
            $html .= '</ul>' . "\n";
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
function read_content_file($path) {

    global $cf, $sl;
    $path = basename($path);
    if ($sl == $cf['language']['default']) {
        $path = './' . $path;
    } else {
        $path = '../' . $path;
    }
    $sep = $cf['uri']['seperator'];
    $pattern = '/<h([1-' . $cf['menu']['levels'] . '])[^>]*>(.*)<\/h/i';

    $content = file_get_contents($path . '/content/content.htm');
    if (!$content) {
        return false;
    }
    preg_match_all($pattern, $content, $matches); // LM CMSimple_XH 1.1

    $headings = array();
    $levels = array();
    $urls = array();

    if (count($matches[0]) == 0) {
        return;
    }
    $ancestors = array();
    foreach ($matches[1] as $level) {
        $levels[] = (int) $level;
    }
    $i = 0;
    foreach ($matches[2] as $chapter) {
        $heading = trim(strip_tags($chapter));
        $url = uenc($heading); //in cms.php: handles $tx['urichar']
        $headings[] = $heading;
        $level = $levels[$i];
        $ancestors[$level] = $url;
        $myself = array_slice($ancestors, 0, $level);
        $urls[] = implode($sep, $myself);
        $i++;
    }
    $pages = preg_split($pattern, $content);
    $pages = array_slice($pages, 1); // $pages[0] is the header part - drop it!
    return array($urls, $pages, $headings, $levels);
}

?>
