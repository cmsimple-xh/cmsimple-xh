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

    switch ($f) {
    case 'sysinfo':
        $o .= XH_sysinfo();
        break;
    case 'phpinfo':
        phpinfo();
        exit;
    case 'settings':
        $o .= XH_settingsView();
        break;
    case 'file':
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
                require_once $pth['folder']['classes'] . 'FileEdit.php';
                $temp = array('config' => 'XH_CoreConfigFileEdit',
                              'langconfig' => 'XH_CoreLangconfigFileEdit',
                              'language' => 'XH_CoreLangFileEdit',
                              'template' => 'XH_CoreTextFileEdit',
                              'stylesheet' => 'XH_CoreTextFileEdit');
                $temp = array_key_exists($file) ? $temp[$file] : null;
                if ($action == 'save') {
                    $o .= $temp->submit();
                } else {
                    $o .= $temp->form();
                }
            }
        }
        break;
    case 'validate':
        require_once $pth['folder']['classes'] . 'LinkCheck.php';
        $temp = new XH_LinkCheck();
        $o .= $temp->check_links();
        break;
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
