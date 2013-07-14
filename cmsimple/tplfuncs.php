<?php

/**
 * Template functions.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */


/**
 * Returns the complete HEAD element.
 *
 * @return string The (X)HTML.
 *
 * @global string The page title.
 * @global array  The configuration of the core.
 * @global array  The paths of system files and folders.
 * @global array  The localization of the core.
 * @global string (X)HTML to be inserted to the HEAD Element.
 */
function head()
{
    global $title, $cf, $pth, $tx, $hjs;

    $t = XH_title($cf['site']['title'], $title);
    $t = '<title>' . strip_tags($t) . '</title>' . "\n";
    foreach (array_merge($cf['meta'], $tx['meta']) as $i => $k) {
        $t .= meta($i);
    }
    $t = tag('meta http-equiv="content-type" content="text/html;charset=UTF-8"')
        . "\n" . $t;
    $plugins = implode(', ', XH_plugins());
    return $t
        . tag(
            'meta name="generator" content="' . CMSIMPLE_XH_VERSION . ' '
            . CMSIMPLE_XH_BUILD . ' - www.cmsimple-xh.org"'
        ) . "\n"
        . '<!-- plugins: ' . $plugins . ' -->' . "\n"
        . tag(
            'link rel="stylesheet" href="' . $pth['file']['corestyle']
            . '" type="text/css"'
        ) . "\n"
        . tag(
            'link rel="stylesheet" href="' . $pth['file']['stylesheet']
            . '" type="text/css"'
        ) . "\n"
        . $hjs;
}


/**
 * Returns the language dependend site title.
 *
 * @return string The (X)HTML.
 *
 * @global array The localization of the core.
 */
function sitename()
{
    global $tx;

    return isset($tx['site']['title'])
        ? htmlspecialchars($tx['site']['title'], ENT_QUOTES, 'UTF-8')
        : '';
}


/**
 * Returns the global site title.
 *
 * @return string The (X)HTML.
 *
 * @global array The configuration of the core.
 */
function pagename()
{
    global $cf;

    return isset($cf['site']['title'])
        ? htmlspecialchars($cf['site']['title'], ENT_QUOTES, 'UTF-8')
        : '';
}


/**
 * Returns the onload attribute for the body element.
 *
 * @return string The (X)HTML.
 *
 * @global string JavaScript for the onload attribute of the BODY element.
 */
function onload()
{
    global $onload;

    return ' onload="' . $onload . '"';
}


/**
 * Returns the table of contents.
 *
 * @param int      $start The menu level to start with.
 * @param int      $end   The menu level to end with.
 * @param callable $li    A callback that actually creates the view.
 *
 * @return string The (X)HTML.
 *
 * @global array The content of the pages.
 * @global int   The number of pages.
 * @global int   The index of the current page.
 * @global array The menu levels of the pages.
 * @global array The configuration of the core.
 */
function toc($start = null, $end = null, $li = 'li')
{
    global $c, $cl, $s, $l, $cf;

    if (isset($start)) {
        if (!isset($end)) {
            $end = $start;
        }
    } else {
        $start = 1;
    }
    if (!isset($end)) {
        $end = $cf['menu']['levels'];
    }
    $ta = array();
    if ($s > -1) {
        $tl = $l[$s];
        for ($i = $s; $i > -1; $i--) {
            if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end) {
                if (!hide($i)
                    || ($i == $s && $cf['show_hidden']['pages_toc'] == 'true')
                ) {
                    $ta[] = $i;
                }
            }
            if ($l[$i] < $tl) {
                $tl = $l[$i];
            }
        }
        @sort($ta);
        $tl = $l[$s];
    } else {
        $tl = 0;
    }
    $tl += 1 + $cf['menu']['levelcatch'];
    for ($i = $s + 1; $i < $cl; $i++) {
        if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end) {
            if (!hide($i)) {
                $ta[] = $i;
            }
        }
        if ($l[$i] < $tl) {
            $tl = $l[$i];
        }
    }
    return call_user_func($li, $ta, $start);
}


/**
 * Returns a menu structure of the pages.
 *
 * @param array $ta The indexes of the pages.
 * @param mixed $st The menu level to start with or the type of menu.
 *
 * @return string The (X)HTML.
 *
 * @global int   The index of the current page.
 * @global array The menu levels of the pages.
 * @global array The headings of the pages.
 * @global int   The number of pages.
 * @global array The configuration of the core.
 * @global array The URLs of the pages.
 */
function li($ta, $st)
{
    global $s, $l, $h, $cl, $cf, $u;

    $tl = count($ta);
    if ($tl < 1) {
        return;
    }
    $t = '';
    if ($st == 'submenu' || $st == 'search') {
        $t .= '<ul class="' . $st . '">' . "\n";
    }
    $b = 0;
    if ($st > 0) {
        $b = $st - 1;
        $st = 'menulevel';
    }
    $lf = array();
    for ($i = 0; $i < $tl; $i++) {
        $tf = ($s != $ta[$i]);
        if ($st == 'menulevel' || $st == 'sitemaplevel') {
            for ($k = (isset($ta[$i - 1]) ? $l[$ta[$i - 1]] : $b);
                 $k < $l[$ta[$i]];
                 $k++
            ) {
                $t .= "\n" . '<ul class="' . $st . ($k + 1) . '">' . "\n";
            }
        }
        $t .= '<li class="';
        if (!$tf) {
            $t .= 's';
        } elseif (@$cf['menu']['sdoc'] == "parent" && $s > -1) {
            if ($l[$ta[$i]] < $l[$s]) {
                $hasChildren = substr($u[$s], 0, 1 + strlen($u[$ta[$i]]))
                    == $u[$ta[$i]] . $cf['uri']['seperator'];
                if ($hasChildren) {
                    $t .= 's';
                }
            }
        }
        $t .= 'doc';
        for ($j = $ta[$i] + 1; $j < $cl; $j++) {
            if (!hide($j)
                && $l[$j] - $l[$ta[$i]] < 2 + $cf['menu']['levelcatch']
            ) {
                if ($l[$j] > $l[$ta[$i]]) {
                    $t .= 's';
                }
                break;
            }
        }
        $t .= '">';
        if ($tf) {
            $t .= a($ta[$i], '');
        } else {
            $t .='<span>';
        }
        $t .= $h[$ta[$i]];
        if ($tf) {
            $t .= '</a>';
        } else {
            $t .='</span>';
        }
        if ($st == 'menulevel' || $st == 'sitemaplevel') {
            if ((isset($ta[$i + 1]) ? $l[$ta[$i + 1]] : $b) > $l[$ta[$i]]) {
                $lf[$l[$ta[$i]]] = true;
            } else {
                $t .= '</li>' . "\n";
                $lf[$l[$ta[$i]]] = false;
            }
            for ($k = $l[$ta[$i]];
                $k > (isset($ta[$i + 1]) ? $l[$ta[$i + 1]] : $b);
                $k--
            ) {
                $t .= '</ul>' . "\n";
                if (isset($lf[$k - 1])) {
                    if ($lf[$k - 1]) {
                        $t .= '</li>' . "\n";
                        $lf[$k - 1] = false;
                    }
                }
            }
        } else {
            $t .= '</li>' . "\n";
        }
    }
    if ($st == 'submenu' || $st == 'search') {
        $t .= '</ul>' . "\n";
    }
    return $t;
}

/**
 * Returns the search form.
 *
 * @return string The (X)HTML.
 *
 * @global string The script name.
 * @global array  The localization of the core.
 */
function searchbox()
{
    global $sn, $tx;

    return '<form action="' . $sn . '" method="get">' . "\n"
        . '<div id="searchbox">' . "\n"
        . tag('input type="text" class="text" name="search" size="12"') . "\n"
        . tag('input type="hidden" name="function" value="search"') . "\n" . ' '
        . tag(
            'input type="submit" class="submit" value="'
            . $tx['search']['button'] . '"'
        ) . "\n"
        . '</div>' . "\n" . '</form>' . "\n";
}


/**
 * Returns the sitemap link.
 *
 * @return string The (X)HTML.
 */
function sitemaplink()
{
    return ml('sitemap');
}


/**
 * Returns the link for the print view.
 *
 * @return string The (X)HTML.
 *
 * @global string The requested special function.
 * @global string The current search string.
 * @global string The requested special file.
 * @global string The script name.
 * @global array  The localization of the core.
 */
function printlink()
{
    global $f, $search, $file, $sn, $tx;

    $t = '&amp;print';
    if ($f == 'search') {
        $t .= '&amp;function=search&amp;search='
            . htmlspecialchars(stsl($search), ENT_QUOTES, 'UTF-8');
    } elseif ($f == 'file') {
        $t .= '&amp;file=' . $file;
    } elseif ($f != '' && $f != 'save') {
        $t .= '&amp;' . $f;
    } elseif (sv('QUERY_STRING') != '') {
        $t = htmlspecialchars(sv('QUERY_STRING'), ENT_QUOTES, 'UTF-8') . $t;
    }
    return '<a href="' . $sn . '?' . $t . '">' . $tx['menu']['print'] . '</a>';
}


/**
 * Returns the link to the mail form.
 *
 * @return string The (X)HTML.
 *
 * @global array The configuration of the core.
 */
function mailformlink()
{
    global $cf;

    if ($cf['mailform']['email'] != '') {
        return ml('mailform');
    }
}


/**
 * Returns the link to the guestbook.
 *
 * @return string The (X)HTML.
 *
 * @deprecated since 1.5.4
 */
function guestbooklink()
{
    trigger_error('Function guestbooklink() is deprecated', E_USER_DEPRECATED);

    if (function_exists('gblink')) {
        return gblink();
    }
}


/**
 * Returns the link to the login form.
 *
 * @return string The (X)HTML.
 */
function loginlink()
{
    if (function_exists('lilink')) {
        return lilink();
    }
}


/**
 * Returns the date of the last update of the site.
 *
 * @param bool $br   Whether to emit a br element between text and date.
 * @param int  $hour The time correction in hours.
 *
 * @return string The (X)HTML.
 *
 * @global array The localization of the core.
 * @global array The paths of system files and folders.
 */
function lastupdate($br = null, $hour = null)
{
    global $tx, $pth;

    $t = $tx['lastupdate']['text'] . ':';
    if (!(isset($br))) {
        $t .= tag('br');
    } else {
        $t .= ' ';
    }
    return $t
        . date(
            $tx['lastupdate']['dateformat'],
            filemtime($pth['file']['content']) + (isset($hour) ? $hour * 3600 : 0)
        );
}


/**
 * Returns the link to the copyright and license informations.
 *
 * @return string The (X)HTML.
 *
 * @global array  The configuration of the core.
 * @global string The script name.
 */
function legallink()
{
    global $cf, $sn;

    return '<a href="' . $sn . '?' . uenc($cf['menu']['legal']) . '">'
        . $cf['menu']['legal'] . '</a>';
}


/**
 * Returns the locator (breadcrumb navigation).
 *
 * @return string The (X)HTML.
 *
 * @global string The title of the page.
 * @global array  The headings of the pages.
 * @global int    The index of the current page.
 * @global string The requested special function.
 * @global array  The content of the pages.
 * @global array  The menu levels of the pages.
 * @global array  The localization of the core.
 * @global array  The configuration of the core.
 */
function locator()
{
    global $title, $h, $s, $f, $c, $l, $tx, $cf;

    if (hide($s) && $cf['show_hidden']['path_locator'] != 'true') {
        return $h[$s];
    }
    if ($title != '' && (!isset($h[$s]) || $h[$s] != $title)) {
        return $title;
    }
    $t = '';
    if ($s == 0) {
        return $h[$s];
    } elseif ($f != '') {
        return ucfirst($f);
    } elseif ($s > 0) {
        $tl = $l[$s];
        if ($tl > 1) {
            for ($i = $s - 1; $i >= 0; $i--) {
                if ($l[$i] < $tl) {
                    $t = a($i, '') . $h[$i] . '</a> &gt; ' . $t;
                    $tl--;
                }
                if ($tl < 2) {
                    break;
                }
            }
        }
        if ($cf['locator']['show_homepage'] == 'true') {
            return a(0, '') . $tx['locator']['home'] . '</a> &gt; ' . $t . $h[$s];
        } else {
            return $t . $h[$s];
        }
    } else {
        return '&nbsp;';
    }
}


/**
 * Returns the admin menu.
 *
 * Returns an empty string since XH 1.5,
 * as the admin menu is automatically inserted to the template.
 *
 * @return string The (X)HTML.
 *
 * @see XH_adminMenu()
 */
function editmenu()
{
    return '';
}

/**
 * Returns the contents area.
 *
 * @return string (X)HTML.
 *
 * @global int    The index of the current page.
 * @global string The output of the contents area.
 * @global array  The content of the pages.
 * @global bool   Whether edit mode is active.
 * @global bool   Whether admin mode is active.
 * @global array  The configuration of the core.
 */
function content()
{
    global $s, $o, $c, $edit, $adm, $cf;

    if (!($edit && $adm) && $s > -1) {
        if (isset($_GET['search'])) {
            $search = htmlspecialchars(stsl($_GET['search']), ENT_QUOTES, 'UTF-8');
            $words = explode(' ', $search);
            $code = 'return "&" . preg_quote($w, "&") . "(?!([^<]+)?>)&isU";';
            $words = array_map(create_function('$w', $code), $words);
            $replacement = '<span class="highlight_search">$0</span>';
            $c[$s] = preg_replace($words, $replacement, $c[$s]);
        }
        return $o . preg_replace('/#CMSimple (.*?)#/is', '', $c[$s]);
    } else {
        return $o;
    }
}


/**
 * Returns the submenu of a page.
 *
 * @return string (X)HTML.
 *
 * @global int   The index of the current page.
 * @global int   The number of pages.
 * @global array The menu levels of the pages.
 * @global array The localization of the core.
 * @global array The configuration of the core.
 */
function submenu()
{
    global $s, $cl, $l, $tx, $cf;

    $ta = array();
    if ($s > -1) {
        $tl = $l[$s] + 1 + $cf['menu']['levelcatch'];
        for ($i = $s + 1; $i < $cl; $i++) {
            if ($l[$i] <= $l[$s]) {
                break;
            }
            if ($l[$i] <= $tl) {
                if (!hide($i)) {
                    $ta[] = $i;
                }
            }
            if ($l[$i] < $tl) {
                $tl = $l[$i];
            }
        }
        if (count($ta) != 0) {
            return '<h4>' . $tx['submenu']['heading'] . '</h4>'
                . li($ta, 'submenu');
        }
    }
}


/**
 * Returns the link to the previous page.
 *
 * @return string (X)HTML.
 *
 * @global int   The index of the current page.
 * @global int   The number of pages.
 * @global array The localization of the core.
 *
 * @see nextpage()
 */
function previouspage()
{
    global $s, $cl, $tx;

    for ($i = $s - 1; $i > -1; $i--) {
        if (!hide($i)) {
            return a($i, '') . $tx['navigator']['previous'] . '</a>';
        }
    }
}


/**
 * Returns the link to the next page
 *
 * @return string (X)HTML.
 *
 * @global int   The index of the current page.
 * @global int   The number of pages.
 * @global array The localization of the core.
 *
 * @see previouspage()
 */
function nextpage()
{
    global $s, $cl, $tx;

    for ($i = $s + 1; $i < $cl; $i++) {
        if (!hide($i)) {
            return a($i, '') . $tx['navigator']['next'] . '</a>';
        }
    }
}


/**
 * Returns a link to the top of the page.
 *
 * To work, an anchor TOP has to be defined in the template.
 *
 * @return string (X)HTML.
 *
 * @global array The localization of the core.
 */
function top()
{
    global $tx;

    return '<a href="#TOP">' . $tx['navigator']['top'] . '</a>';
}


/**
 * Returns the language menu.
 *
 * @return string (X)HTML.
 *
 * @global array  The paths of system files and folders.
 * @global array  The configuration of the core.
 * @global string The current language.
 */
function languagemenu()
{
    global $pth, $cf, $sl;

    $r = array();
    if (($fd = opendir($pth['folder']['base'])) !== false) {
        while (($p = readdir($fd)) !== false) {
            if (XH_isLanguageFolder($p)) {
                $r[] = $p;
            }
        }
        closedir($fd);
    }
    array_unshift($r, $cf['language']['default']);
    $i = array_search($sl, $r);
    unset($r[$i]);

    $t = '';
    foreach ($r as $lang) {
        $url = $pth['folder']['base']
            . ($lang == $cf['language']['default'] ? '' : $lang . '/');
        $img = $pth['folder']['flags'] . '/' . $lang . '.gif';
        $el = file_exists($img)
            ? tag(
                'img src="' . $img . '" alt="' . $lang . '" title="&nbsp;'
                . $lang . '&nbsp;" class="flag"'
            )
            : '[' . $lang . ']';
        $t .= '<a href="' . $url . '">' . $el . '</a> ';
    }
    return $t;
}

?>
