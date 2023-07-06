<?php

/**
 * @file tplfuncs.php
 *
 * Template functions.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 */

/**
 * Renders the prev link.
 *
 * @return string HTML
 *
 * @since 1.6.3
 */
function XH_renderPrevLink()
{
    $index = XH_findPreviousPage();
    if ($index !== false) {
        return '<link rel="prev" href="' . XH_getPageURL($index) . '">';
    } else {
        return '';
    }
}

/**
 * Renders the next link.
 *
 * @return string HTML
 *
 * @since 1.6.3
 */
function XH_renderNextLink()
{
    $index = XH_findNextPage();
    if ($index !== false) {
        return '<link rel="next" href="' . XH_getPageURL($index) . '">';
    } else {
        return '';
    }
}

/**
 * Returns the complete HEAD element.
 *
 * @return string HTML
 */
function head()
{
    global $title, $cf, $pth, $tx, $hjs;

    $t = XH_title($cf['site']['title'], $title);
    $t = '<title>' . strip_tags($t) . '</title>' . "\n";
    foreach (array_keys(array_merge($cf['meta'], $tx['meta'])) as $i) {
        $t .= meta($i);
    }
    $t = '<meta http-equiv="content-type" content="text/html;charset=UTF-8">'
        . "\n" . $t;
    $plugins = implode(', ', XH_plugins());
    $o = $t;
    if (error_reporting() > 0) {
        $o .= '<meta name="generator" content="' . CMSIMPLE_XH_VERSION . ' '
            . CMSIMPLE_XH_BUILD . ' - www.cmsimple-xh.org">'
            . "\n"
            . '<!-- plugins: ' . $plugins . ' -->' . "\n";
    }
    $o .= XH_renderPrevLink() . XH_renderNextLink()
        . '<link rel="stylesheet" href="' . XH_pluginStylesheet()
        . '" type="text/css">' . PHP_EOL
        . $hjs
        . '<link rel="stylesheet" href="' . $pth['file']['stylesheet']
        . '" type="text/css">' . "\n";
    return $o;
}

/**
 * Returns the language dependend site title.
 *
 * @return string HTML
 */
function sitename()
{
    global $tx;

    return isset($tx['site']['title']) ? XH_hsc($tx['site']['title']) : '';
}


/**
 * Returns the global site title.
 *
 * @return string HTML
 */
function pagename()
{
    global $cf;

    return isset($cf['site']['title']) ? XH_hsc($cf['site']['title']) : '';
}


/**
 * Returns the onload attribute for the body element.
 *
 * @return string HTML
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
 * @return string HTML
 */
function toc($start = null, $end = null, $li = 'li')
{
    global $cl, $s, $l, $cf;

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
        sort($ta);
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
    return $li($ta, $start);
}


/**
 * Returns a menu structure of certain pages.
 *
 * @param array $ta The indexes of the pages.
 * @param mixed $st The menu level to start with or the type of menu.
 *
 * @return string HTML
 */
function li(array $ta, $st)
{
    $li = new XH\Li();
    return $li->render($ta, $st);
}

/**
 * Sets global variables for CSS/DHTML menus.
 *
 * The most important variable is <var>$hc</var>, which is an array of page
 * indexes of the pages of the menu. This is normally passed as first argument
 * to li(), e.g. <kbd>li($hc)</kbd>. <var>$hl</var> holds the number of these
 * pages. <var>$si</var> holds the index of the current page within
 * <var>$hc</var>; it might be useful for advanced menus.
 *
 * @return void
 *
 * @since 1.6.2
 */
function XH_buildHc()
{
    global $cl, $s, $cf, $si, $hc, $hl;

    $pages = new XH\Pages();
    $si = -1;
    $hc = array();
    for ($i = 0; $i < $cl; $i++) {
        if (!hide($i)
            || ($cf['show_hidden']['pages_toc'] == 'true'
            && ($i == $s || in_array($i, $pages->getAncestorsOf($s, false))))
        ) {
            $hc[] = $i;
        }
        if ($i == $s) {
            $si = count($hc);
        }
    }
    $hl = count($hc);
}

/**
 * Returns the search form.
 *
 * @return string HTML
 */
function searchbox()
{
    global $sn, $tx;

    return '<form id="searchbox" action="' . $sn . '" method="get">' . "\n"
        . '<input type="search" class="text" name="search" title="'
        . $tx['search']['label'] . '" placeholder="' . $tx['search']['label']
        . '" size="12">' . "\n"
        . '<input type="hidden" name="function" value="search">' . "\n" . ' '
        . '<input type="submit" class="submit" value="'
        . $tx['search']['button'] . '">' . "\n"
        . '</form>' . "\n";
}


/**
 * Returns the sitemap link.
 *
 * @return string HTML
 */
function sitemaplink()
{
    return ml('sitemap');
}


/**
 * Returns the link for the print view.
 *
 * @return string HTML
 */
function printlink()
{
    global $tx;

    return '<a href="' . XH_printUrl() . '" rel="nofollow">'
        . $tx['menu']['print'] . '</a>';
}

/**
 * Returns the URL of the print view.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_printUrl()
{
    global $f, $search, $file, $sn;

    $t = '&print';
    if ($f == 'search') {
        $t .= '&function=search&search=' . urlencode($search);
    } elseif ($f == 'file') {
        $t .= '&file=' . $file;
    } elseif ($f != '' && $f != 'save') {
        $t .= '&' . $f;
    } elseif (sv('QUERY_STRING') != '') {
        $t = sv('QUERY_STRING') . $t;
    }
    $t = XH_hsc($t);
    return $sn . '?' . $t;
}

/**
 * Returns the link to the mail form.
 *
 * @return string HTML
 */
function mailformlink()
{
    global $cf;

    if ($cf['mailform']['email'] != '') {
        return ml('mailform');
    }
    return "";
}

/**
 * Returns the link to the login form.
 *
 * @return string HTML
 */
function loginlink()
{
    global $s, $tx, $xh_publisher, $u;

    if (!XH_ADM) {
        $index = $s > -1 ? $s : 0;
        $extra = ($index === $xh_publisher->getFirstPublishedPage() ? $u[$index] : '');
        return a($index, $extra . '&amp;login" rel="nofollow')
            . $tx['menu']['login'] . '</a>';
    }
    return "";
}


/**
 * Returns the date of the last update of the site.
 *
 * @param bool $br   Whether to emit a br element between text and date.
 * @param int  $hour The time correction in hours.
 *
 * @return string HTML
 */
function lastupdate($br = null, $hour = null)
{
    global $tx, $pth;

    $t = $tx['lastupdate']['text'] . ':';
    if (!(isset($br))) {
        $t .= '<br>';
    } else {
        $t .= ' ';
    }
    $time = filemtime($pth['file']['content']) + (isset($hour) ? $hour * 3600 : 0);
    return $t . '<time datetime="' . date('c', $time) . '">'
        . XH_formatDate($time)
        . '</time>';
}

/**
 * Returns the locator (breadcrumb navigation).
 *
 * @param string $separator The separator between the breadcrumb links.
 *
 * @return string HTML
 */
function locator($separator = '&gt;')
{
    $breadcrumbs = XH_getLocatorModel();
    $last = count($breadcrumbs) - 1;
    $html = '<span itemscope itemtype="https://schema.org/BreadcrumbList">';
    foreach ($breadcrumbs as $i => $breadcrumb) {
        list($title, $url) = $breadcrumb;
        if ($i > 0) {
            $html .= ' ' . $separator . ' ';
        }
        $html .= '<span itemprop="itemListElement" '
                . 'itemscope itemtype="https://schema.org/ListItem">';
        $inner = '<span itemprop="name">' . $title . '</span>';
        if (isset($url) && $i < $last) {
            $html .= '<a itemprop="item" href="' . $url . '">'
                    . $inner . '</a>';
        } else {
            $html .= $inner;
        }
        $html .= '<meta itemprop="position" content="'. ($i + 1) . '"></span>';
    }
    $html .= '</span>';
    return $html;
}

/**
 * Returns the admin menu.
 *
 * Returns an empty string since XH 1.5,
 * as the admin menu is automatically inserted to the template.
 *
 * @return string HTML
 *
 * @see XH_adminMenu()
 *
 * @deprecated since 1.7. Just remove from the template.
 */
function editmenu()
{
    trigger_error('Function editmenu() is deprecated', E_USER_DEPRECATED);

    return '';
}

/**
 * Returns the contents area.
 *
 * @return string HTML
 */
function content()
{
    global $s, $o, $c, $edit;

    if (!($edit && XH_ADM) && $s > -1) {
        if (isset($_GET['search'])) {
            $search = XH_hsc(trim(preg_replace('/\s+/u', ' ', $_GET['search'])));
            $words = explode(' ', $search);
            $c[$s] = XH_highlightSearchWords($words, $c[$s]);
        }
        $o .= preg_replace('/#CMSimple (.*?)#/is', '', $c[$s]);
    }
    return  preg_replace('/<!--XH_ml[1-9]:.*?-->/is', '', $o);
}


/**
 * Returns the submenu of a page.
 *
 * @param string $html Optional markup to wrap the heading.
 *
 * @return string HTML
 */
function submenu($html = '')
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
            if ($html == '') {
                $level = min($cf['menu']['levels'] + 1, 6);
                return '<h' . $level . '>' . $tx['submenu']['heading']
                    . '</h' . $level . '>'
                    . li($ta, 'submenu');
            } else {
                return sprintf($html, $tx['submenu']['heading'])
                    . li($ta, 'submenu');
            }
        }
    }
    return "";
}

/**
 * Returns a link to the previous page.
 *
 * @return string|null HTML
 *
 * @see nextpage()
 */
function previouspage()
{
    global $tx;

    $index = XH_findPreviousPage();
    if ($index !== false) {
        return '<a href="' . XH_getPageURL($index) . '" rel="prev">' . $tx['navigator']['previous'] . '</a>';
    }
    return null;
}

/**
 * Returns a link to the next page
 *
 * @return string|null HTML
 *
 * @see previouspage()
 */
function nextpage()
{
    global $tx;

    $index = XH_findNextPage();
    if ($index !== false) {
        return '<a href="' . XH_getPageURL($index) . '" rel="next">' . $tx['navigator']['next'] . '</a>';
    }
    return null;
}

/**
 * Returns a link to the top of the page.
 *
 * To work, an appropriate ID has to be defined in the template.
 *
 * @param string $id An (X)HTML ID.
 * @return string
 */
function top($id = 'TOP')
{
    global $tx;

    return '<a href="#' . $id . '">' . $tx['navigator']['top'] . '</a>';
}


/**
 * Returns the language menu.
 *
 * @return string HTML
 */
function languagemenu()
{
    global $pth, $cf, $sl;

    $r = XH_secondLanguages();
    array_unshift($r, $cf['language']['default']);
    $i = array_search($sl, $r);
    unset($r[$i]);

    $langName = [];
    $langNames = explode(';', $cf['language']['2nd_lang_names']);
    foreach ($langNames as $value) {
        $langName[substr($value, 0, 2)] = substr($value, 3);
    }

    $t = '';
    foreach ($r as $lang) {
        $url = $pth['folder']['base']
            . ($lang == $cf['language']['default'] ? '' : $lang . '/');
        $img = $pth['folder']['templateflags'] . $lang . '.gif';
        if (!file_exists($img)) {
            $img = $pth['folder']['flags'] . $lang . '.gif';
        }

        $title = isset($langName[$lang])
            ? $langName[$lang]
            : $lang;

        $el = file_exists($img)
            ? '<img src="' . $img . '" alt="' . $title . '" title="'
                . $title . '" class="flag">'
            : $title;
        $t .= '<a href="' . $url . '">' . $el . '</a> ';
    }
    return $t;
}


/**
 * Provides a minimal template (in case template isn't found).
 *
 * @return void
 *
 * @since 1.6.3
 */
function XH_emergencyTemplate()
{
    header('HTTP/1.0 503 Service Unavailable');
    header('Content-Type: text/html;charset=UTF-8');
    echo '<!DOCTYPE html><head>'
    . head()
    . '</head><body '
    . onload()
    . '>'
    . sitename()
    . toc()
    . content()
    . loginlink()
    . '</body></html>';
    XH_exit();
}

/**
 * Returns a powered by CMSimple_XH link.
 *
 * @param string $linktext
 *
 * @return string
 *
 * @since 1.7
 */
function poweredByLink($linktext = '')
{
    $linktext = $linktext ? $linktext : 'Powered by CMSimple_XH';
    return '<a href="https://cmsimple-xh.org/" target="_blank">'
        . $linktext . '</a>';
}
