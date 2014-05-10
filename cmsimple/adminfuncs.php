<?php

/**
 * Admin only functions.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * Returns the readable version of a plugin.
 *
 * @param string $plugin Name of a plugin.
 *
 * @return string
 *
 * @global array The paths of system files and folders.
 *
 * @since 1.6
 */
function XH_pluginVersion($plugin)
{
    global $pth;

    $internalPlugins = array(
        'filebrowser', 'meta_tags', 'page_params', 'tinymce', 'tinymce4'
    );
    if (in_array($plugin, $internalPlugins)) {
        $version = 'for ' . CMSIMPLE_XH_VERSION;
    } else {
        $filename = $pth['folder']['plugins'] . $plugin . '/version.nfo';
        if (is_readable($filename)) {
            $contents = file_get_contents($filename);
            $contents = explode(',', $contents);
            $version = $contents[2];
        } else {
            $version = '';
        }
    }
    return $version;
}

/**
 * Returns the result view of the system check.
 *
 * @param array $data The data ;)
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 *
 * @return string The (X)HTML.
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#system_check
 *
 * @since 1.5.4
 */
function XH_systemCheck($data)
{
    global $pth, $tx;

    $stx = $tx['syscheck'];

    foreach (array('success', 'warning', 'fail') as $img) {
        $txt = $stx[$img];
        $imgs[$img] = tag(
            'img src="' . $pth['folder']['corestyle'] . $img . '.png" alt="'
            . $txt . '" title="' . $txt . '" width="16" height="16"'
        );
    }

    $o = "<h4>$stx[title]</h4>\n<ul id=\"xh_system_check\">\n";

    if (key_exists('phpversion', $data)) {
        $ok = version_compare(PHP_VERSION, $data['phpversion']) >= 0;
        $o .= '<li>' . $imgs[$ok ? 'success' : 'fail']
            . sprintf($stx['phpversion'], $data['phpversion']) . "</li>\n";
    }

    if (key_exists('extensions', $data)) {
        $cat = ' class="xh_system_check_cat_start"';
        foreach ($data['extensions'] as $ext) {
            if (is_array($ext)) {
                $notok = $ext[1] ? 'fail' : 'warning';
                $ext = $ext[0];
            } else {
                $notok = 'fail';
            }
            $o .= '<li' . $cat . '>'
                . $imgs[extension_loaded($ext) ? 'success' : $notok]
                . sprintf($stx['extension'], $ext) . "</li>\n";
            $cat = '';
        }
    }

    if (key_exists('writable', $data)) {
        $cat = ' class="xh_system_check_cat_start"';
        foreach ($data['writable'] as $file) {
            if (is_array($file)) {
                $notok = $file[1] ? 'fail' : 'warning';
                $file = $file[0];
            } else {
                $notok = 'warning';
            }
            $o .= '<li' . $cat . '>' . $imgs[is_writable($file) ? 'success' : $notok]
                . sprintf($stx['writable'], $file) . "</li>\n";
            $cat = '';
        }
    }

    if (key_exists('other', $data)) {
        $cat = ' class="xh_system_check_cat_start"';
        foreach ($data['other'] as $check) {
            $notok = $check[1] ? 'fail' : 'warning';
            $o .= '<li' . $cat . '>' . $imgs[$check[0] ? 'success' : $notok]
                . $check[2] . "</li>\n";
            $cat = '';
        }
    }

    $o .= "</ul>\n";

    return $o;
}

/**
 * Returns the normalized absolute URL path.
 *
 * @param string $path A relative path.
 *
 * @return string
 *
 * @global string The script name.
 *
 * @since 1.6.1
 */
function XH_absoluteUrlPath($path)
{
    global $sn;

    $base = preg_replace('/index\.php$/', '', $sn);
    $parts = explode('/', $base . $path);
    $i = 0;
    while ($i < count($parts)) {
        switch ($parts[$i]) {
        case '.':
            array_splice($parts, $i, 1);
            break;
        case '..':
            array_splice($parts, $i - 1, 2);
            $i--;
            break;
        default:
            $i++;
        }
    }
    $path = implode('/', $parts);
    return $path;
}

/**
 * Returns whether a resource is access protected.
 *
 * @param string $path A normalized absolute URL path.
 *
 * @return bool.
 *
 * @since 1.6.1
 */
function XH_isAccessProtected($path)
{
    $host = $_SERVER['HTTP_HOST'];
    $stream = fsockopen($host, $_SERVER['SERVER_PORT'], $errno, $errstr, 5);
    if ($stream) {
        stream_set_timeout($stream, 5);
        $request = "HEAD $path HTTP/1.1\r\nHost: $host\r\n"
            . "User-Agent: CMSimple_XH\r\n\r\n";
        fwrite($stream, $request);
        $response = fread($stream, 12);
        fclose($stream);
        $status = substr($response, 9);
        return $status[0] == '4' || $status[0] == '5';
    } else {
        return false;
    }
}

/**
 * Returns the system information view.
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 *
 * @return string The (X)HTML.
 *
 * @since 1.6
 */
function XH_sysinfo()
{
    global $pth, $tx;

    $o = '<p><b>' . $tx['sysinfo']['version'] . '</b></p>' . "\n";
    $o .= '<ul>' . "\n" . '<li>' . CMSIMPLE_XH_VERSION . '&nbsp;&nbsp;Released: '
        . CMSIMPLE_XH_DATE . '</li>' . "\n" . '</ul>' . "\n" . "\n";

    $o .= '<p><b>' . $tx['sysinfo']['plugins'] . '</b></p>' . "\n" . "\n";

    $o .= '<ul>' . "\n";
    foreach (XH_plugins() as $temp) {
        $o .= '<li>' . ucfirst($temp) . ' ' . XH_pluginVersion($temp) . '</li>'
            . "\n";
    }
    $o .= '</ul>' . "\n" . "\n";

    $serverSoftware = !empty($_SERVER['SERVER_SOFTWARE'])
        ? $_SERVER['SERVER_SOFTWARE']
        : $tx['sysinfo']['unknown'];
    $o .= '<p><b>' . $tx['sysinfo']['webserver'] . '</b></p>' . "\n"
        . '<ul>' . "\n" . '<li>' . $serverSoftware . '</li>' . "\n"
        . '</ul>' . "\n\n";
    $o .= '<p><b>' . $tx['sysinfo']['php_version'] . '</b></p>' . "\n"
        . '<ul>' . "\n" . '<li>' . phpversion() . '</li>' . "\n"
        . '<li><a href="./?&phpinfo" target="blank"><b>'
        . $tx['sysinfo']['phpinfo_link'] . '</b></a> &nbsp; '
        . $tx['sysinfo']['phpinfo_hint'] . '</li>' . "\n" . '</ul>' . "\n" . "\n";

    $o .= '<h4>' . $tx['sysinfo']['helplinks'] . '</h4>' . "\n" . "\n";
    $o .= <<<HTML
<ul>
<li><a href="http://www.cmsimple-xh.org/">cmsimple-xh.org &raquo;</a></li>
<li><a href="http://www.cmsimple-xh.org/wiki/">cmsimple-xh.org/wiki/ &raquo;</a></li>
<li><a href="http://www.cmsimpleforum.com/">cmsimpleforum.com &raquo;</a></li>
<li><a href="http://www.cmsimplewiki.com/">cmsimplewiki.com &raquo;</a></li>
</ul>

HTML;

    $checks = array(
        'phpversion' => '4.3.10',
        'extensions' => array(
            'pcre',
            array('session', false),
            array('xml', false)
        ),
        'writable' => array(),
        'other' => array()
    );
    $temp = array(
        'content', 'corestyle', 'images', 'downloads', 'userfiles', 'media'
    );
    foreach ($temp as $i) {
        $checks['writable'][] = $pth['folder'][$i];
    }
    $temp = array('config', 'log', 'language', 'content', 'template', 'stylesheet');
    foreach ($temp as $i) {
        $checks['writable'][] = $pth['file'][$i];
    }
    $checks['writable'] = array_unique($checks['writable']);
    sort($checks['writable']);
    foreach (array($pth['file']['config'], $pth['file']['content']) as $file) {
        $checks['other'][] = array(
            XH_isAccessProtected($file), false,
            sprintf($tx['syscheck']['access_protected'], $file)
        );
    }
    if ($tx['locale']['all'] == '') {
        $checks['other'][] = array(true, false, $tx['syscheck']['locale_default']);
    } else {
        $checks['other'][] = array(
            setlocale(LC_ALL, $tx['locale']['all']), false,
            sprintf($tx['syscheck']['locale_available'], $tx['locale']['all'])
        );
    }
    $checks['other'][] = array(
        !function_exists('date_default_timezone_get')
        || date_default_timezone_get() !== 'UTC',
        false, $tx['syscheck']['timezone']
    );
    $checks['other'][] = array(
        !get_magic_quotes_runtime(), false, $tx['syscheck']['magic_quotes']
    );
    $checks['other'][] = array(
        !ini_get('session.use_trans_sid'), false, 'session.use_trans_sid off'
    );
    $checks['other'][] = array(
        ini_get('session.use_only_cookies'), false, 'session.use_only_cookies on'
    );
    $o .= XH_systemCheck($checks);
    return $o;
}


/**
 * Returns the general settings view.
 *
 * @return string The (X)HTML.
 *
 * @global string The script name.
 * @global array  The localization of the core.
 *
 * @since 1.6
 */
function XH_settingsView()
{
    global $sn, $tx;

    $o = '<p>' . $tx['settings']['warning'] . '</p>' . "\n"
        . '<h4>' . $tx['settings']['systemfiles'] . '</h4>' . "\n" . '<ul>' . "\n";

    foreach (array('config', 'language') as $i) {
        $o .= '<li><a href="' . $sn . '?file=' . $i . '&amp;action=array">'
            . utf8_ucfirst($tx['action']['edit']) . ' '
            . $tx['filetype'][$i] . '</a></li>' . "\n";
    }

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

    $o .= '<h4>' . $tx['settings']['backup'] . '</h4>' . "\n";
    $o .= XH_backupsView();
    return $o;
}

/**
 * Returns the log file view.
 *
 * @return string (X)HTML.
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 *
 * @since 1.6
 */
function XH_logFileView()
{
    global $pth, $tx;

    return '<h1>' . $tx['title']['log'] . '</h1>'
        . '<pre id="xh_logfile">' . XH_hsc(XH_readFile($pth['file']['log']))
        . '</pre>'
        . '<script type="text/javascript">/* <![CDATA[ */'
        . '(function () {'
        . 'var elt = document.getElementById("xh_logfile");'
        . 'elt.scrollTop = elt.scrollHeight;'
        . '}())'
        . '/* ]]> */</script>';
}

/**
 * Returns the backup view.
 *
 * @return string The (X)HTML.
 *
 * @global array  The paths of system files and folders.
 * @global array  The script name.
 * @global array  The localization of the core.
 * @global object The CSRF protection object.
 *
 * @since 1.6
 */
function XH_backupsView()
{
    global $pth, $sn, $tx, $_XH_csrfProtection;

    $o = '<ul>' . "\n";
    if (isset($_GET['xh_success'])) {
        $o .= XH_message('success', $tx['message'][stsl($_GET['xh_success'])]);
    }
    $o .= '<li>' . utf8_ucfirst($tx['filetype']['content']) . ' <a href="'
        . $sn . '?file=content&amp;action=view" target="_blank">'
        . $tx['action']['view'] . '</a>' . ' <a href="' . $sn . '?file=content">'
        . $tx['action']['edit'] . '</a>' . ' <a href="'
        . $sn . '?file=content&amp;action=download">' . $tx['action']['download']
        . '</a>'
        . ' <form action="' . $sn . '?&xh_backups" method="post"'
        . ' class="xh_inline_form" onsubmit="return XH.promptBackupName(this)">'
        . tag('input type="hidden" name="file" value="content"')
        . tag('input type="hidden" name="action" value="backup"')
        . tag('input type="hidden" name="xh_suffix" value="extra"')
        . tag(
            'input type="submit" class="submit" value="'
            . $tx['action']['backup'] . '"'
        )
        . $_XH_csrfProtection->tokenInput()
        . '</form>'
        . ' <form action="' . $sn . '?&xh_backups" method="post"'
        . ' class="xh_inline_form">'
        . tag('input type="hidden" name="file" value="content"')
        . tag('input type="hidden" name="action" value="empty"')
        . tag(
            'input type="submit" class="submit" value="'
            . $tx['action']['empty'] . '"'
        )
        . $_XH_csrfProtection->tokenInput()
        . '</form>'
        . '</li>' . "\n";
    $o .= '</ul>' . "\n" . tag('hr') . "\n" . '<p>'
        . $tx['settings']['backupexplain1'] . '</p>' . "\n" . '<p>'
        . $tx['settings']['backupexplain2'] . '</p>' . "\n" . '<ul>' . "\n";
    $fs = sortdir($pth['folder']['content']);
    foreach ($fs as $p) {
        if (XH_isContentBackup($p, false)) {
            $size = filesize($pth['folder']['content'] . '/' . $p);
            $size = round(($size) / 102.4) / 10;
            $o .= '<li><a href="' . $sn . '?file=' . $p
                . '&amp;action=view" target="_blank">'
                . $p . '</a> (' . $size . ' KB)'
                . ' <form action="' . $sn . '?&xh_backups" method="post"'
                . ' class="xh_inline_form">'
                . tag('input type="hidden" name="file" value="' . $p . '"')
                . tag('input type="hidden" name="action" value="restore"')
                . tag(
                    'input type="submit" class="submit" value="'
                    . $tx['action']['restore'] . '"'
                )
                . $_XH_csrfProtection->tokenInput()
                . '</form>'
                . '</li>' . "\n";
        }
    }
    $o .= '</ul>' . "\n";
    return $o;
}

/**
 * Creates the menu of a plugin (add row, add tab), constructed as a table.
 * This is an object implemented with a procedural interface.
 *
 * @param string $add    Add a ROW, a TAB or DATA (Userdefineable content).
 *                       SHOW will return the menu.
 * @param string $link   The link, the TAB will lead to.
 * @param string $target Target of the link (with(!) 'target=').
 * @param string $text   Description of the TAB.
 * @param array  $style  Array with style-data for the containing table-cell
 *
 * @return mixed
 *
 * @staticvar string The (X)HTML of the menu build so far.
 */
function pluginMenu($add = '', $link = '', $target = '', $text = '',
    $style = array()
) {
    static $menu = '';

    $add = strtoupper($add);

    if (!isset($style['row'])) {
        $style['row'] = 'class="edit" style="width: 100%;"';
    }
    if (!isset($style['tab'])) {
        $style['tab'] = '';
    }
    if (!isset($style['link'])) {
        $style['link'] = '';
    }
    if (!isset($style['data'])) {
        $style['data'] = '';
    }

    $menu_row = '<table {{STYLE_ROW}}>' . "\n"
        . '<tr>' . "\n" . '{{TAB}}</tr>' . "\n" . '</table>' . "\n" . "\n";
    $menu_tab = '<td {{STYLE_TAB}}><a{{STYLE_LINK}} href="{{LINK}}"'
        . ' {{TARGET}}>{{TEXT}}</a></td>' . "\n";
    $menu_tab_data = '<td {{STYLE_DATA}}>{{TEXT}}</td>' . "\n";

    if ($add == 'ROW') {
        $new_menu_row = $menu_row;
        $new_menu_row = str_replace('{{STYLE_ROW}}', $style['row'], $new_menu_row);
        $menu .= $new_menu_row;
    }

    if ($add == 'TAB') {
        $new_menu_tab = $menu_tab;
        $new_menu_tab = str_replace('{{STYLE_TAB}}', $style['tab'], $new_menu_tab);
        $new_menu_tab = str_replace('{{STYLE_LINK}}', $style['link'], $new_menu_tab);
        $new_menu_tab = str_replace('{{LINK}}', $link, $new_menu_tab);
        $new_menu_tab = str_replace('{{TARGET}}', $target, $new_menu_tab);
        $new_menu_tab = str_replace('{{TEXT}}', $text, $new_menu_tab);
        $menu = str_replace('{{TAB}}', $new_menu_tab . '{{TAB}}', $menu);
    }

    if ($add == 'DATA') {
        $new_menu_tab_data = $menu_tab_data;
        $new_menu_tab_data = str_replace(
            '{{STYLE_DATA}}', $style['data'], $new_menu_tab_data
        );
        $new_menu_tab_data = str_replace('{{TEXT}}', $text, $new_menu_tab_data);
        $menu = str_replace('{{TAB}}', $new_menu_tab_data . '{{TAB}}', $menu);
    }

    if ($add == 'SHOW') {
        $menu = str_replace('{{TAB}}', '', $menu);
        $m = $menu;
        $menu = '';
        return $m;
    }
}

/**
 * Returns the admin menu.
 *
 * @param array $plugins A list of plugins.
 *
 * @return string (X)HTML.
 *
 * @global bool   Whether edit mode is active.
 * @global int    The index of the current page.
 * @global array  The URLs of the pages.
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global string The URL of the current page.
 * @global array  The localization of the plugins.
 *
 * @since 1.6
 */
function XH_adminMenu($plugins = array())
{
    global $edit, $s, $u, $cf, $tx, $su, $plugin_tx;

    if ($s < 0) {
        $su = $u[0];
    }
    $changeMode = $edit ? 'normal' : 'edit';
    $changeText = $edit ? $tx['editmenu']['normal'] : $tx['editmenu']['edit'];

    $filesMenu = array();
    foreach (array('images', 'downloads', 'media') as $item) {
        $filesMenu[] =  array(
            'label' => utf8_ucfirst($tx['editmenu'][$item]),
            'url' => '?&amp;normal&amp;' . $item
        );
    }
    $settingsMenu = array(
        array(
            'label' => utf8_ucfirst($tx['editmenu']['configuration']),
            'url' => '?file=config&amp;action=array'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['language']),
            'url' => '?file=language&amp;action=array'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['template']),
            'url' => '?file=template&amp;action=edit'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['stylesheet']),
            'url' => '?file=stylesheet&amp;action=edit'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['log']),
            'url' => '?file=log&amp;action=view'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['validate']),
            'url' => '?&amp;validate'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['backups']),
            'url' => '?&amp;xh_backups'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['pagedata']),
            'url' => '?&amp;xh_pagedata'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['sysinfo']),
            'url' => '?&amp;sysinfo'
        )
    );
    $hiddenPlugins = explode(',', $cf['plugins']['hidden']);
    $hiddenPlugins = array_map('trim', $hiddenPlugins);
    $plugins = array_diff($plugins, $hiddenPlugins);
    $total = count($plugins);
    $rows = 12;
    $columns = ceil($total / $rows);
    $rows = ceil($total / $columns);
    $width = 125 * $columns;
    $marginLeft = min($width, 250) - $width;
    natcasesort($plugins);
    $plugins = array_values($plugins);
    $orderedPlugins = array();
    for ($j = 0; $j < $rows; ++$j) {
        for ($i = 0; $i < $total; $i += $rows) {
            $orderedPlugins[] = isset($plugins[$i + $j]) ? $plugins[$i + $j] : '';
        }
    }
    $plugins = $orderedPlugins;
    $pluginMenu = array();
    foreach ($plugins as $plugin) {
        $label = isset($plugin_tx[$plugin]['menu_plugin'])
            ? $plugin_tx[$plugin]['menu_plugin']
            : ucfirst($plugin);
        $pluginMenuItem = array('label' => $label);
        if ($plugin != '') {
            $pluginMenuItem['url'] = '?' . $plugin . '&amp;normal';
        }
        $pluginMenu[] = $pluginMenuItem;
    }
    $menu = array(
        array(
            'label' => $changeText,
            'url' => '?' . $su . '&amp;' . $changeMode,
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['pagemanager']),
            'url' => '?&amp;normal&amp;xhpages'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['files']),
            'url' => '?&amp;normal&amp;userfiles',
            'children' => $filesMenu
            ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['settings']),
            'url' => '?&amp;settings',
            'children' => $settingsMenu
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['plugins']),
            'children' => $pluginMenu,
            'style' => 'width:' . $width . 'px; margin-left: ' . $marginLeft . 'px'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['logout']),
            'url' => '?&amp;logout'
        )
    );

    $t = "\n" . '<div id="xh_adminmenu">';
    $t .= "\n" . '<ul>' . "\n";
    foreach ($menu as $item) {
        $t .= XH_adminMenuItem($item);
    }
    $t .= '</ul>' . "\n"
        . '<div class="xh_break"></div>' . "\n" . '</div>' . "\n";
    return $t;
}

/**
 * Returns the LI element of an admin menu item.
 *
 * @param array $item  The menu item.
 * @param int   $level The level of the menu item.
 *
 * @return string
 *
 * @global string The scipt name.
 *
 * @since 1.6
 */
function XH_adminMenuItem($item, $level = 0)
{
    global $sn;

    $indent = str_repeat('    ', $level);
    $t = $indent . '<li>';
    if (isset($item['url'])) {
        $t .= '<a href="' . $sn . $item['url'] . '">';
    } else {
        $t .= '<span>';
    }
    $t .= $item['label'];
    if (isset($item['url'])) {
        $t .= '</a>';
    } else {
        $t .= '</span>';
    }
    if (isset($item['children'])) {
        $t .= "\n" . $indent . '    <ul';
        if (isset($item['style'])) {
            $t .= ' style="' . $item['style'] . '"';
        }
        $t .= '>' . "\n";
        foreach ($item['children'] as $child) {
            $t .= XH_adminMenuItem($child, $level + 1);
        }
        $t .= $indent . '    </ul>' . "\n" . $indent;
    }
    $t .= '</li>' . "\n";
    return $t;
}

/**
 * Returns the plugin menu.
 *
 * @param string $main Whether the main setting menu item should be shown
 *                     ('ON'/'OFF').
 *
 * @global string The sitename.
 * @global string The name of the currently loading plugin.
 * @global array  The paths of system files and folders.
 * @global string The currently active language.
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global array  The localization of the plugins.
 *
 * @return string (X)HTML.
 */
function print_plugin_admin($main)
{
    global $sn, $plugin, $pth, $sl, $cf, $tx, $plugin_tx;

    initvar('action');
    initvar('admin');
    pluginFiles($plugin);

    $main = strtoupper($main) == 'ON';
    $css = is_readable($pth['file']['plugin_stylesheet']);
    $config = is_readable($pth['file']['plugin_config']);
    $language = is_readable($pth['file']['plugin_language']);
    $help = is_readable($pth['file']['plugin_help']);

    $tx_main = empty($plugin_tx[$plugin]['menu_main'])
        ? $tx['menu']['tab_main'] : $plugin_tx[$plugin]['menu_main'];
    $tx_css = empty($plugin_tx[$plugin]['menu_css'])
        ? $tx['menu']['tab_css'] : $plugin_tx[$plugin]['menu_css'];
    $tx_config = empty($plugin_tx[$plugin]['menu_config'])
        ? $tx['menu']['tab_config'] : $plugin_tx[$plugin]['menu_config'];
    $tx_language = empty($plugin_tx[$plugin]['menu_language'])
        ? $tx['menu']['tab_language'] : $plugin_tx[$plugin]['menu_language'];
    $tx_help = empty($plugin_tx[$plugin]['menu_help'])
        ? $tx['menu']['tab_help'] : $plugin_tx[$plugin]['menu_help'];

    pluginMenu('ROW', '', '', '', array());
    if ($main) {
        $link = $sn . '?&amp;' . $plugin
            . '&amp;admin=plugin_main&amp;action=plugin_text';
        pluginMenu('TAB', $link, '', $tx_main, array());
    }
    if ($css) {
        $link = $sn . '?&amp;' . $plugin
            . '&amp;admin=plugin_stylesheet&amp;action=plugin_text';
        pluginMenu('TAB', $link, '', $tx_css, array());
    }
    if ($config) {
        $link = $sn . '?&amp;' . $plugin
            . '&amp;admin=plugin_config&amp;action=plugin_edit';
        pluginMenu('TAB', $link, '', $tx_config, '');
    }
    if ($language) {
        $link = $sn . '?&amp;' . $plugin
            . '&amp;admin=plugin_language&amp;action=plugin_edit';
        pluginMenu('TAB', $link, '', $tx_language, '');
    }
    if ($help) {
        $link = $pth['file']['plugin_help'];
        pluginMenu('TAB', $link, 'target="_blank"', $tx_help, '');
    }
    return pluginMenu('SHOW');
}


/**
 * Handles reading and writing of plugin files
 * (e.g. en.php, config.php, stylesheet.css).
 *
 * @param bool  $action Unused.
 * @param array $admin  Unused.
 * @param bool  $plugin Unused.
 * @param bool  $hint   Unused.
 *
 * @global string The requested action.
 * @global string The requested admin-action.
 * @global string The name of the currently loading plugin.
 * @global array  The paths of system files and folders.
 *
 * @return string Returns the created form or the result of saving the data.
 *
 * @todo Deprecated unused parameters.
 */
function plugin_admin_common($action, $admin, $plugin, $hint=array())
{
    global $action, $admin, $plugin, $pth;

    include_once $pth['folder']['classes'] . 'FileEdit.php';
    switch ($admin) {
    case 'plugin_config':
        $fileEdit = new XH_PluginConfigFileEdit();
        break;
    case 'plugin_language':
        $fileEdit = new XH_PluginLanguageFileEdit();
        break;
    case 'plugin_stylesheet':
        $fileEdit = new XH_PluginTextFileEdit();
        break;
    default:
        return false;
    }
    switch ($action) {
    case 'plugin_edit':
    case 'plugin_text':
        return $fileEdit->form();
    case 'plugin_save':
    case 'plugin_textsave':
        return $fileEdit->submit();
    default:
        return false;
    }
}


/**
 * Returns the content editor and activates it.
 *
 * @global string The script name.
 * @global string The currently active page URL.
 * @global int    The index of the currently active page.
 * @global array  The URLs of the pages.
 * @global array  The content of the pages.
 * @global string Error messages as (X)HTML fragment consisting of LI Elements.
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global object The CSRF protection object.
 *
 * @return string  The (X)HTML.
 *
 * @since 1.6
 */
function XH_contentEditor()
{
    global $sn, $su, $s, $u, $c, $e, $cf, $tx, $_XH_csrfProtection;

    $su = $u[$s]; // TODO: is changing of $su correct here???

    $editor = $cf['editor']['external'] == '' || init_editor();
    if (!$editor) {
        $msg = sprintf($tx['error']['noeditor'], $cf['editor']['external']);
        $e .= '<li>' . $msg . '</li>' . "\n";
    }
    $o = '<form method="POST" id="ta" action="' . $sn . '">'
        . tag('input type="hidden" name="selected" value="' . $u[$s] . '"')
        . tag('input type="hidden" name="function" value="save"')
        . '<textarea name="text" id="text" class="xh-editor" style="height: '
        . $cf['editor']['height'] . 'px; width: 100%;" rows="30" cols="80">'
        . XH_hsc($c[$s])
        . '</textarea>'
        . '<script type="text/javascript">/* <![CDATA[ */'
        . 'document.getElementById("text").style.height=(' . $cf['editor']['height']
        . ') + "px";/* ]]> */</script>'
        . $_XH_csrfProtection->tokenInput();
    if ($cf['editor']['external'] == '' || !$editor) {
        $value = utf8_ucfirst($tx['action']['save']);
        $o .= tag('input type="submit" value="' . $value . '"');
    }
    $o .= '</form>';
    return $o;
}

/**
 * Saves the current contents (including the page data), if edit mode is active.
 *
 * @return bool Whether that succeeded
 *
 * @global array  The content of the pages.
 * @global array  The paths of system files and folders.
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global array  Whether edit mode is active.
 * @global object The page data router.
 *
 * @since 1.6
 */
function XH_saveContents()
{
    global $c, $pth, $cf, $tx, $edit, $pd_router;

    if (!(XH_ADM && $edit)) {
        trigger_error(
            'Function ' . __FUNCTION__ . '() must not be called in view mode',
            E_USER_WARNING
        );
        return false;
    }
    $hot = '<h[1-' . $cf['menu']['levels'] . '][^>]*>';
    $hct = '<\/h[1-' . $cf['menu']['levels'] . ']>';
    $title = utf8_ucfirst($tx['filetype']['content']);
    $cnts = "<html><head><title>$title</title>\n"
        . $pd_router->headAsPHP()
        . '</head><body>' . "\n";
    foreach ($c as $j => $i) {
        preg_match("/(.*?)($hot(.+?)$hct)(.*)/isu", $i, $matches);
        $page = $matches[1] . $matches[2] . PHP_EOL . $pd_router->pageAsPHP($j)
            . $matches[4];
        $cnts .= rmnl($page . "\n");
    }
    $cnts .= '</body></html>';
    if (!file_exists($pth['folder']['content'])) {
        mkdir($pth['folder']['content'], true);
    }
    return XH_writeFile($pth['file']['content'], $cnts) !== false;
}

/**
 * Saves content.htm after submitting changes from the content editor.
 *
 * @param string $text The text to save.
 *
 * @global array  The paths of system files and folders.
 * @global array  The configuation of the core.
 * @global array  The localization of the core.
 * @global object The page data router.
 * @global array  The content of the pages.
 * @global int    The index of the active page.
 * @global array  The URLs of the pages.
 * @global string The URL of the active page.
 *
 * @return void
 *
 * @since 1.6
 */
function XH_saveEditorContents($text)
{
    global $pth, $cf, $tx, $pd_router, $c, $s, $u, $selected;

    $hot = '<h[1-' . $cf['menu']['levels'] . '][^>]*>';
    $hct = '<\/h[1-' . $cf['menu']['levels'] . ']>'; // TODO: use $1 ?
    // TODO: this might be done before the plugins are loaded
    //       for backward compatibility
    $text = stsl($text);
    // remove empty headings
    $text = preg_replace("/$hot(&nbsp;|&#160;|\xC2\xA0| )?$hct/isu", '', $text);
    // replace P elements around plugin calls and scripting with DIVs
    $text = preg_replace(
        '/<p>({{{.*?}}}|#CMSimple .*?#)<\/p>/isu', '<div>$1</div>', $text
    );

    // handle missing heading on the first page
    if ($s == 0) {
        if (!preg_match('/^<h1[^>]*>.*<\/h1>/isu', $text)
            && !preg_match('/^(<p[^>]*>)?(\&nbsp;| |<br \/>)?(<\/p>)?$/isu', $text)
        ) {
            $text = '<h1>' . $tx['toc']['missing'] . '</h1>' . "\n" . $text;
        }
    }
    $c[$s] = $text; // keep editor contents, if saving fails

    // insert $text to $c
    $text = preg_replace(
        '/<h[1-' . $cf['menu']['levels'] . ']/i', "\x00" . '$0', $text
    );
    $pages = explode("\x00", $text);
    // append everything before the first page heading to the previous page:
    if ($s > 0) {
        $c[$s - 1] .= $pages[0];
    }
    array_shift($pages);
    array_splice($c, $s, 1, $pages);

    // delegate changes to $pd_router
    preg_match_all("/$hot(.+?)$hct/isu", $text, $matches);
    if ($pd_router->refresh_from_texteditor($matches[1], $s)) {
        // redirect to get back in sync
        if (count($matches[1]) > 0) {
            // page heading might have changed
            $urlParts = explode($cf['uri']['seperator'], $selected);
            array_splice(
                $urlParts, -1, 1, uenc(trim(xh_rmws(strip_tags($matches[1][0]))))
            );
            $su = implode($cf['uri']['seperator'], $urlParts);
        } else {
            // page was deleted; go to previous page
            $su = $u[max($s - 1, 0)];
        }
        header("Location: " . CMSIMPLE_URL . "?" . $su, true, 303);
        exit;
    } else {
        e('notwritable', 'content', $pth['file']['content']);
    }
}

/**
 * Empties the contents.
 *
 * @return void
 *
 * @global array  The content of the pages.
 * @global int    The number of pages.
 * @global array  The paths of system files and folders.
 * @global array  An (X)HTML fragment with error messages.
 * @global object The pagedata router.
 */
function XH_emptyContents()
{
    global $c, $cl, $pth, $e, $pd_router;

    XH_backup();
    if ($e) {
        return;
    }
    $c = array();
    for ($i = 0; $i < $cl; ++$i) {
        $pd_router->destroy($i);
    }
    if (XH_saveContents()) {
        // the following relocation is necessary to cater for the changed content
        $url = CMSIMPLE_URL . '?&xh_backups&xh_success=emptied';
        header('Location: ' . $url, true, 303);
        exit;
    } else {
        e('cntsave', 'content', $pth['file']['content']);
    }
}

/**
 * Restores a content backup. The current content.htm is backed up before.
 *
 * @param string $filename The filename.
 *
 * @return void
 *
 * @global array  The paths of system files and folders.
 * @global array  An (X)HTML fragment with error messages.
 *
 * @since  1.6
 */
function XH_restore($filename)
{
    global $pth, $e;

    $tempFilename = $pth['folder']['content'] . 'restore.htm';
    if (!XH_renameFile($filename, $tempFilename)) {
        e('cntsave', 'backup', $tempFilename);
        return;
    }
    XH_backup();
    if ($e) {
        if (!unlink($tempFilename)) {
            e('cntdelete', 'content', $tempFilename);
        }
        return;
    }
    if (!XH_renameFile($tempFilename, $pth['file']['content'])) {
        e('cntsave', 'content', $pth['file']['content']);
        return;
    }
    // the following relocation is necessary to cater for the changed content
    $url = CMSIMPLE_URL . '?&xh_backups&xh_success=restored';
    header('Location: ' . $url, true, 303);
    exit;
}

/**
 * Creates an extra backup of the contents file.
 *
 * @param string $suffix A suffix for the filename.
 *
 * @return void
 *
 * @since 1.6
 */
function XH_extraBackup($suffix)
{
    global $pth;

    $date = date("Ymd_His");
    $dest = $pth['folder']['content'] . $date . '_' . $suffix . '.htm';
    if (!copy($pth['file']['content'], $dest)) {
        e('cntsave', 'backup', $dest);
    } else {
        $url = CMSIMPLE_URL . '?&xh_backups&xh_success=backedup';
        header('Location: ' . $url, true, 303);
        exit;
    }
}

/**
 * Returns SCRIPT element containing the localization for admin.js.
 *
 * @return string (X)HTML
 *
 * @global array The localization of the core.
 *
 * @since 1.6
 */
function XH_adminJSLocalization()
{
    global $tx;

    $keys = array(
        'action' => array('cancel', 'ok'),
        'password' => array('fields_missing', 'mismatch', 'wrong'),
        'error' => array('server'),
        'settings' => array('backupsuffix')
    );
    $l10n = array();
    foreach ($keys as $category => $keys2) {
        foreach ($keys2 as $key) {
            $l10n[$category][$key] = $tx[$category][$key];
        }
    }
    $o = '<script type="text/javascript">/* <![CDATA[ */XH.i18n = '
        . XH_encodeJson($l10n) . '/* ]]> */</script>' . PHP_EOL;
    return $o;
}

?>
