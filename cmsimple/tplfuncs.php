<?php

/**
 * Functions, that are called from the template.
 *
 * @version $Id$
 */

// no direct access protection necessary as only functions are defined

// PAGE FUNCTIONS
// new function head() ready for html5 - by GE 2009/06 (CMSimple_XH beta)

function head() {
    global $title, $cf, $pth, $tx, $txc, $hjs;
    if (!empty($cf['site']['title'])) {
        $t = htmlspecialchars($cf['site']['title'], ENT_COMPAT, 'UTF-8')
            . " \xe2\x80\x93 " . $title;
    } else {
        $t = $title;
    }
    $t = '<title>' . strip_tags($t) . '</title>' . "\n";
    foreach ($cf['meta'] as $i => $k)
        $t .= meta($i);
    if ($tx['meta']['codepage'] != '')
        $t = tag('meta http-equiv="content-type" content="text/html;charset=' . $tx['meta']['codepage'] . '"') . "\n" . $t;
    return $t . tag('meta name="generator" content="' . CMSIMPLE_XH_VERSION . ' ' . CMSIMPLE_XH_BUILD . ' - www.cmsimple-xh.de"') . "\n" . tag('link rel="stylesheet" href="' . $pth['file']['corestyle'] . '" type="text/css"') . "\n" . tag('link rel="stylesheet" href="' . $pth['file']['stylesheet'] . '" type="text/css"') . "\n" . $hjs;
}

// END new function head() (CMSimple_XH)


function sitename() {
    global $txc;
    return isset($txc['site']['title'])
        ? htmlspecialchars($txc['site']['title'], ENT_NOQUOTES, 'UTF-8')
        : '';
}

function pagename() {
    global $cf;
    return isset($cf['site']['title'])
        ? htmlspecialchars($cf['site']['title'], ENT_NOQUOTES, 'UTF-8')
        : '';
}

function onload() {
    global $onload;
    return ' onload="' . $onload . '"';
}

function toc($start = NULL, $end = NULL, $li = 'li') { // changed by LM CMSimple_XH 1.1
    global $c, $cl, $s, $l, $cf;
    if (isset($start)) {
        if (!isset($end))
            $end = $start;
    }
    else
        $start = 1;
    if (!isset($end))
        $end = $cf['menu']['levels'];
    $ta = array();
    if ($s > -1) {
        $tl = $l[$s];
        for ($i = $s; $i > -1; $i--) {
            if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end)
                if (!hide($i) || ($i == $s && $cf['show_hidden']['pages_toc'] == 'true'))
                    $ta[] = $i;
            if ($l[$i] < $tl)
                $tl = $l[$i];
        }
        @sort($ta);
        $tl = $l[$s];
    }
    else
        $tl = 0;
    $tl += 1 + $cf['menu']['levelcatch'];
    for ($i = $s + 1; $i < $cl; $i++) {
        if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end)
            if (!hide($i))
                $ta[] = $i;
        if ($l[$i] < $tl)
            $tl = $l[$i];
    }
    return call_user_func($li, $ta, $start);
}

// inserted many "\n" for better structured Sourcecode - by GE 2009/06 (CMSimple_XH beta)

function li($ta, $st) {
    global $s, $l, $h, $cl, $cf, $u;
    $tl = count($ta);
    if ($tl < 1)
        return;
    $t = '';
    if ($st == 'submenu' || $st == 'search')
        $t .= '<ul class="' . $st . '">' . "\n";
    $b = 0;
    if ($st > 0) {
        $b = $st - 1;
        $st = 'menulevel';
    }
    $lf = array();
    for ($i = 0; $i < $tl; $i++) {
        $tf = ($s != $ta[$i]);
        if ($st == 'menulevel' || $st == 'sitemaplevel') {
            for ($k = (isset($ta[$i - 1]) ? $l[$ta[$i - 1]] : $b); $k < $l[$ta[$i]]; $k++)
                $t .= "\n" . '<ul class="' . $st . ($k + 1) . '">' . "\n";
        }
        $t .= '<li class="';
        if (!$tf)
            $t .= 's';
        else if (@$cf['menu']['sdoc'] == "parent" && $s > -1) {
            if ($l[$ta[$i]] < $l[$s]) {
                if (@substr($u[$s], 0, 1 + strlen($u[$ta[$i]])) == $u[$ta[$i]] . $cf['uri']['seperator'])
                    $t .= 's';
            }
        }
        $t .= 'doc';
        for ($j = $ta[$i] + 1; $j < $cl; $j++)
            if (!hide($j) && $l[$j] - $l[$ta[$i]] < 2 + $cf['menu']['levelcatch']) {
                if ($l[$j] > $l[$ta[$i]])
                    $t .= 's';
                break;
            }
        $t .= '">';
        if ($tf)
            $t .= a($ta[$i], '');
        $t .= $h[$ta[$i]];
        if ($tf)
            $t .= '</a>';
        if ($st == 'menulevel' || $st == 'sitemaplevel') {
            if ((isset($ta[$i + 1]) ? $l[$ta[$i + 1]] : $b) > $l[$ta[$i]])
                $lf[$l[$ta[$i]]] = true;
            else {
                $t .= '</li>' . "\n";
                $lf[$l[$ta[$i]]] = false;
            }
            for ($k = $l[$ta[$i]]; $k > (isset($ta[$i + 1]) ? $l[$ta[$i + 1]] : $b); $k--) {
                $t .= '</ul>' . "\n";
                if (isset($lf[$k - 1]))
                    if ($lf[$k - 1]) {
                        $t .= '</li>' . "\n";
                        $lf[$k - 1] = false;
                    }
            };
        }
        else
            $t .= '</li>' . "\n";
    }
    if ($st == 'submenu' || $st == 'search')
        $t .= '</ul>' . "\n";
    return $t;
}

// END modified function li (CMSimple_XH)


function searchbox() {
    global $sn, $tx;
    return '<form action="' . $sn . '" method="GET">' . "\n"
        . '<div id="searchbox">' . "\n"
        . tag('input type="text" class="text" name="search" size="12"') . "\n"
        . tag('input type="hidden" name="function" value="search"') . "\n" . ' '
        . tag('input type="submit" class="submit" value="' . $tx['search']['button'] . '"') . "\n"
        . '</div>' . "\n" . '</form>' . "\n";
}

function sitemaplink() {
    return ml('sitemap');
}

function printlink() {
    global $f, $search, $file, $sn, $tx;
    $t = '&amp;print';
    if ($f == 'search')
        $t .= '&amp;function=search&amp;search=' . htmlspecialchars(stsl($search), ENT_COMPAT, 'UTF-8');
    else if ($f == 'file')
        $t .= '&amp;file=' . $file;
    else if ($f != '' && $f != 'save')
        $t .= '&amp;' . $f;
    else if (sv('QUERY_STRING') != '')
        $t = htmlspecialchars(sv('QUERY_STRING'), ENT_COMPAT, "UTF-8") . $t;
    return '<a href="' . $sn . '?' . $t . '">' . $tx['menu']['print'] . '</a>';
}

// END modified printlink (CMSimple_XH)


function mailformlink() {
    global $txc;
    if ($txc['mailform']['email'] != '')
        return ml('mailform');
}

function guestbooklink() {
    trigger_error('Function guestbooklink() is deprecated', E_USER_DEPRECATED);
    
    if (function_exists('gblink'))
        return gblink();
}

function loginlink() {
    if (function_exists('lilink'))
        return lilink();
}

function lastupdate($br = NULL, $hour = NULL) { // changed by LM CMSimple_XH 1.1
    global $tx, $pth;
    $t = $tx['lastupdate']['text'] . ':';
    if (!(isset($br)))
        $t .= tag('br');
    else
        $t .= ' ';
    return $t . date($tx['lastupdate']['dateformat'], filemtime($pth['file']['content']) + (isset($hour) ? $hour * 3600 : 0));
}

function legallink() {
    global $cf, $sn; // changed by LM CMSimple_XH 1.1
    return '<a href="' . $sn . '?' . uenc($cf['menu']['legal']) . '">' . $cf['menu']['legal'] . '</a>';
}

function locator() {
    global $title, $h, $s, $f, $c, $l, $tx, $txc, $cf;
    if (hide($s) && $cf['show_hidden']['path_locator'] != 'true')
        return $h[$s];
    if ($title != '' && (!isset($h[$s]) || $h[$s] != $title))
        return $title;
    $t = '';
    if ($s == 0)
        return $h[$s];
    elseif ($f != '')
        return ucfirst($f);
    elseif ($s > 0) {
        $tl = $l[$s];
        if ($tl > 1) {
            for ($i = $s - 1; $i >= 0; $i--) {
                if ($l[$i] < $tl) {
                    $t = a($i, '') . $h[$i] . '</a> &gt; ' . $t;
                    $tl--;
                }
                if ($tl < 2)
                    break;
            }
        }
        if ($cf['locator']['show_homepage'] == 'true') {
            return a(0, '') . $tx['locator']['home'] . '</a> &gt; ' . $t . $h[$s];
        } else {
            return $t . $h[$s];
        }
    }
    else
        return '&nbsp;';
}

function editmenu() {
    return '';
}

function admin_menu($plugins = array(), $debug = false)
{
    global $adm, $edit, $s, $u, $sn, $tx, $sl, $cf, $su;

    if ($adm)
    {
        $pluginMenu = '';
        if ((bool) $plugins)
        {
            $pluginMenu .= '<li><a href="#" onclick="return false">' . utf8_ucfirst($tx['editmenu']['plugins']) . "</a>\n    <ul>";
            foreach ($plugins as $plugin)
            {
                $pluginMenu .= "\n" .
                    '     <li><a href="?' . $plugin . '&amp;normal">' . ucfirst($plugin) . '</a></li>';
            }

            $pluginMenu .= "\n    </ul>";
        }


        $t .= "\n" . '<div id="editmenu">';

        $t .= "\n" . '<ul id="edit_menu">' . "\n";

        if ($s < 0)
        {
            $su = $u[0];
        }
        $changeMode = $edit ? 'normal' : 'edit';
        $changeText = $edit ? $tx['editmenu']['normal'] : $tx['editmenu']['edit'];
        $t .= '<li><a href="' . $sn . '?' . $su . '&' . $changeMode . '">' . $changeText . '</a></li>' . "\n";
        $t .= '<li><a href="' . $sn . '?&amp;normal&amp;xhpages" class="">' . utf8_ucfirst($tx['editmenu']['pagemanager']) . '</a></li>' . "\n";
        $t .= '<li><a href="#" onclick="return false" class="">' . utf8_ucfirst($tx['editmenu']['files']) . '</a>' ."\n";
        $t .= '    <ul>' . "\n";
        $t .= '    <li><a href="' . $sn . '?&amp;normal&amp;images">' . utf8_ucfirst($tx['editmenu']['images']) . '</a></li>' . "\n";
        $t .= '    <li><a href="' . $sn . '?&amp;normal&amp;downloads">' . utf8_ucfirst($tx['editmenu']['downloads']) . '</a></li>' . "\n";
        $t .= '    <li><a href="' . $sn . '?&amp;normal&amp;media">' . utf8_ucfirst($tx['editmenu']['media']) . '</a></li>' . "\n";
        $t .= '    <li><a href="' . $sn . '?&amp;normal&amp;userfiles">' . utf8_ucfirst($tx['editmenu']['userfiles']) . '</a></li>' . "\n";
        $t .= '    </ul>' . "\n";
        $t .= '</li>' ."\n";
        $t .= '<li><a href="' . $sn . '?&amp;settings">' . utf8_ucfirst($tx['editmenu']['settings']) . '</a>' ."\n"
                    . '    <ul>' ."\n";

        if($sl == $cf['language']['default'])
        {
            $t .='    <li><a href="?file=config&amp;action=array">' . utf8_ucfirst($tx['editmenu']['configuration']) . '</a></li>' . "\n";
        }

        $t .='    <li><a href="?file=langconfig&amp;action=array">' . utf8_ucfirst($tx['editmenu']['langconfig']) . '</a></li>' . "\n"
        . '    <li><a href="?file=language&amp;action=array">' . utf8_ucfirst($tx['editmenu']['language']) . '</a></li>' . "\n"
        . '    <li><a href="?file=template&amp;action=edit">' . utf8_ucfirst($tx['editmenu']['template']) . '</a></li>' . "\n"
        . '    <li><a href="?file=stylesheet&amp;action=edit">' . utf8_ucfirst($tx['editmenu']['stylesheet']) . '</a></li>' . "\n"
        . '    <li><a href="?file=log&amp;action=view" target="_blank">' . utf8_ucfirst($tx['editmenu']['log']) . '</a></li>' . "\n"
        . '    <li><a href="' . $sn . '?&amp;validate">' . utf8_ucfirst($tx['editmenu']['validate']) . '</a></li>' . "\n"
        . '    <li><a href="' . $sn . '?&amp;sysinfo">' . utf8_ucfirst($tx['editmenu']['sysinfo']) . '</a></li>' . "\n"
        . '    </ul>' . "\n"
        . '</li>' . "\n"
        . $pluginMenu . "\n"
        . '</li>' . "\n";
        $t .= '</ul>' . "\n" . '<ul id="editmenu_logout">' . "\n";
        $t .= '<li id="edit_menu_logout"><a href="?&logout">' . utf8_ucfirst($tx['editmenu']['logout']) . '</a></li>' . "\n";
        $t .= '</ul>' . "\n";

        return $t . '<div style="float:none;clear:both;padding:0;margin:0;width:100%;height:0px;"></div>' . "\n" . '</div>' . "\n";
    }
}

function content() {
    global $s, $o, $c, $edit, $adm, $cf;
    if (!($edit && $adm) && $s > -1) {
        if (isset($_GET['search'])) {
            $words = explode(',', htmlspecialchars(stsl($_GET['search']), ENT_COMPAT, 'UTF-8'));
            $code = 'return "&" . preg_quote($w, "&") . "(?!([^<]+)?>)&isU";';
            $words = array_map(create_function('$w', $code), $words);
            $c[$s] = preg_replace($words, '<span class="highlight_search">$0</span>', $c[$s]);
        }
        return $o . preg_replace("/" . $cf['scripting']['regexp'] . "/is", "", $c[$s]);
    } else {
        return $o;
    }
}

function submenu() {
    global $s, $cl, $l, $tx, $cf;
    $ta = array();
    if ($s > -1) {
        $tl = $l[$s] + 1 + $cf['menu']['levelcatch'];
        for ($i = $s + 1; $i < $cl; $i++) {
            if ($l[$i] <= $l[$s])
                break;
            if ($l[$i] <= $tl)
                if (!hide($i))
                    $ta[] = $i;
            if ($l[$i] < $tl)
                $tl = $l[$i];
        }
        if (count($ta) != 0)
            return '<h4>' . $tx['submenu']['heading'] . '</h4>' . li($ta, 'submenu');
    }
}

function previouspage() {
    global $s, $cl, $tx;
    for ($i = $s - 1; $i > -1; $i--)
        if (!hide($i))
            return a($i, '') . $tx['navigator']['previous'] . '</a>';
}

function nextpage() {
    global $s, $cl, $tx;
    for ($i = $s + 1; $i < $cl; $i++)
        if (!hide($i))
            return a($i, '') . $tx['navigator']['next'] . '</a>';
}

function top() {
    global $tx;
    return '<a href="#TOP">' . $tx['navigator']['top'] . '</a>';
}

// tagged img-tags in function languagemenu() - by GE 09-06-26 (CMSimple_XH beta3)
// title-tags for flag-gifs - by GE 09-10-07 (CMSimple_XH 1.0rc2)

function languagemenu() {
    global $pth, $cf, $sl;
    if(!file_exists('./cmsimplesubsite.htm')){  // for subsites
        $t = '';
        $r = array();
        $fd = @opendir($pth['folder']['base']);
        while (($p = @readdir($fd)) == true ) {
            if (@is_dir($pth['folder']['base'].$p)) {
                if (preg_match('/^[A-z]{2}$/', $p)
                    && !file_exists($pth['folder']['base'] . $p . '/cmsimplesubsite.htm'))
                {
                    $r[] = $p;
                }
            }
        }
        if ($fd == true)closedir($fd); if(count($r) == 0)return ''; if($cf['language']['default'] != $sl)$t .= '<a href="'.$pth['folder']['base'].'">'.tag('img src="'.$pth['folder']['flags'].$cf['language']['default'].'.gif" alt="'.$cf['language']['default'].'" title="&nbsp;'.$cf['language']['default'].'&nbsp;" class="flag"').'</a> '; $v = count($r); for($i = 0;
        $i < $v;
        $i++) {
            if ($sl != $r[$i]) {
                if (is_file($pth['folder']['flags'].'/'.$r[$i].'.gif')) {
                    $t .= '<a href="'.$pth['folder']['base'].$r[$i].'/">'.tag('img src="'.$pth['folder']['flags'].$r[$i].'.gif" alt="'.$r[$i].'" title="&nbsp;'.$r[$i].'&nbsp;" class="flag"').'</a> ';
                } else {
                    $t .= '<a href="'.$pth['folder']['base'].$r[$i].'/">['.$r[$i].']</a> ';
                }
            }
        }
        return ''.$t.'';
    } // for subsites
}
// END modified function languagemenu() - by GE 09-06-26 (CMSimple_XH beta3)




?>
