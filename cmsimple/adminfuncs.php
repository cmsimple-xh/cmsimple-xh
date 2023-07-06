<?php

/**
 * @file adminfuncs.php
 *
 * Admin only functions.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 */

/**
 * Returns the readable version of a plugin.
 *
 * @param string $plugin Name of a plugin.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_pluginVersion($plugin)
{
    global $pth;

    $internalPlugins = array(
        'filebrowser', 'meta_tags', 'page_params', 'tinymce'
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
 * @return string HTML
 *
 * @see http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#system_check
 *
 * @since 1.5.4
 */
function XH_systemCheck(array $data)
{
    global $tx;

    $stx = $tx['syscheck'];

    $o = "<h4>$stx[title]</h4>\n<ul id=\"xh_system_check\">\n";

    if (key_exists('phpversion', $data)) {
        $ok = version_compare(PHP_VERSION, $data['phpversion']) >= 0;
        $o .= XH_systemCheckLi('', $ok ? 'success' : 'fail', sprintf($stx['phpversion'], $data['phpversion']));
    }

    if (key_exists('extensions', $data)) {
        $cat = 'xh_system_check_cat_start';
        foreach ($data['extensions'] as $ext) {
            if (is_array($ext)) {
                $notok = $ext[1] ? 'fail' : 'warning';
                $ext = $ext[0];
            } else {
                $notok = 'fail';
            }
            $o .= XH_systemCheckLi(
                $cat,
                extension_loaded($ext) ? 'success' : $notok,
                sprintf($stx['extension'], $ext)
            );
            $cat = '';
        }
    }

    if (key_exists('writable', $data)) {
        $cat = 'xh_system_check_cat_start';
        foreach ($data['writable'] as $file) {
            if (is_array($file)) {
                $notok = $file[1] ? 'fail' : 'warning';
                $file = $file[0];
            } else {
                $notok = 'warning';
            }
            $o .= XH_systemCheckLi($cat, is_writable($file) ? 'success' : $notok, sprintf($stx['writable'], $file));
            $cat = '';
        }
    }

    if (key_exists('other', $data)) {
        $cat = 'xh_system_check_cat_start';
        foreach ($data['other'] as $check) {
            $notok = $check[1] ? 'fail' : 'warning';
            $o .= XH_systemCheckLi($cat, $check[0] ? 'success' : $notok, $check[2]);
            $cat = '';
        }
    }

    $o .= "</ul>\n";

    return $o;
}

/**
 * Returns a single `<li>` of the system check.
 *
 * @param string $class A CSS class.
 * @param string $state A state.
 * @param string $text  A message text.
 *
 * @return string
 *
 * @since 1.7.0
 */
function XH_systemCheckLi($class, $state, $text)
{
    global $tx;

    $class = "class=\"xh_$state $class\"";
    return "<li $class>"
        . sprintf($tx['syscheck']['message'], $text, $tx['syscheck'][$state])
        . "</li>\n";
}

/**
 * Returns the normalized absolute URL path.
 *
 * @param string $path A relative path.
 *
 * @return string
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
 * @return bool
 *
 * @since 1.6.1
 */
function XH_isAccessProtected($path)
{
    $url = preg_replace('/index\.php$/', '', CMSIMPLE_URL) . $path;
    if (extension_loaded('curl')) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        if (curl_exec($curl)) {
            $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            curl_close($curl);
            return $status >= 400 && $status < 500;
        }
        curl_close($curl);
    }
    $defaultContext = stream_context_set_default(
        array('http' => array('method' => 'HEAD', 'timeout' => 5))
    );
    $headers = get_headers($url);
    stream_context_set_default(stream_context_get_params($defaultContext));
    if ($headers) {
        if (preg_match('/^HTTP\S*\s+4/', $headers[0])) {
            return true;
        }
    }
    return false;
}

/**
 * Returns the system information view.
 *
 * @return string HTML
 *
 * @since 1.6
 */
function XH_sysinfo()
{
    global $pth, $cf, $tx, $sn;

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
        . '<li><a href="' . $sn . '?&phpinfo" target="_blank"><b>'
        . $tx['sysinfo']['phpinfo_link'] . '</b></a> &nbsp; '
        . $tx['sysinfo']['phpinfo_hint'] . '</li>' . "\n" . '</ul>' . "\n" . "\n";

    $o .= '<h4>' . $tx['sysinfo']['helplinks'] . '</h4>' . "\n" . "\n";
    $o .= <<<HTML
<ul>
<li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimple-xh.org/">cmsimple-xh.org &raquo;</a></li>
<li><a target="_blank" rel="noopener" rel="noreferrer" href="https://wiki.cmsimple-xh.org/">wiki.cmsimple-xh.org &raquo;</a></li>
<li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimpleforum.com/">cmsimpleforum.com &raquo;</a></li>
<li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimple-xh.org/?Important-Links">cmsimple-xh.org/?Important-Links &raquo;</a></li>
<li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimple-xh.org/?Plugin-Repository">cmsimple-xh.org/?Plugin-Repository &raquo;</a></li>
<li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimple-xh.org/?Template-Repository">cmsimple-xh.org/?Template-Repository &raquo;</a></li>
</ul>

HTML;

    $stx = $tx['syscheck'];
    $checks = array(
        'phpversion' => '5.5.0',
        'extensions' => array(
            array('intl', false),
            'json',
            'mbstring',
            'session'
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
    $checks['writable'][] = "{$pth['folder']['cmsimple']}.sessionname";
    $checks['writable'] = array_unique($checks['writable']);
    sort($checks['writable']);
    $files = array(
        $pth['file']['config'], $pth['file']['content'], $pth['file']['template']
    );
    foreach ($files as $file) {
        $checks['other'][] = array(
            XH_isAccessProtected($file), false,
            sprintf($stx['access_protected'], $file)
        );
    }
    if ($tx['locale']['all'] == '') {
        $checks['other'][] = array(true, false, $stx['locale_default']);
    } else {
        $checks['other'][] = array(
            setlocale(LC_ALL, $tx['locale']['all']), false,
            sprintf($stx['locale_available'], $tx['locale']['all'])
        );
    }
    $checks['other'][] = array(
        in_array($temp = date_default_timezone_get(), timezone_identifiers_list()) && $temp !== 'UTC',
        false, $stx['timezone']
    );
    $checks['other'][] = array(
        !ini_get('safe_mode'), false, $stx['safe_mode']
    );
    $checks['other'][] = array(
        !ini_get('session.use_trans_sid'), false, $stx['use_trans_sid']
    );
    $checks['other'][] = array(
        ini_get('session.use_only_cookies'), false, $stx['use_only_cookies']
    );
    $checks['other'][] = array(
        ini_get('session.cookie_lifetime') == 0, false, $stx['cookie_lifetime']
    );
    $checks['other'][] = array(
        strpos(ob_get_contents(), "\xEF\xBB\xBF") !== 0,
        false, $stx['bom']
    );
    $checks['other'][] = array(
        !password_verify('test', $cf['security']['password']),
        false, $stx['password']
    );
    $checks['other'][] = array(
        function_exists('fsockopen'), false, $stx['fsockopen']
    );
    $checks['other'][] = array(
        function_exists('curl_init'), false, $stx['curl']
    );
    $o .= XH_systemCheck($checks);
    return $o;
}


/**
 * Returns the general settings view.
 *
 * @return string HTML
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

    $o .= '<h4>' . $tx['settings']['more'] . '</h4>' . "\n"
        . '<ul>' . "\n"
        . '<li><a href="' . $sn . '?&validate">' . $tx['editmenu']['validate'] . '</a></li>'
        . '<li><a href="' . $sn . '?&xh_backups">' . $tx['editmenu']['backups'] . '</a></li>'
        . '<li><a href="' . $sn . '?&xh_pagedata">' .$tx['editmenu']['pagedata'] . '</a></li>'
        . '<li><a href="' . $sn . '?&xh_change_password">' . $tx['editmenu']['change_password'] . '</a></li>'
        . '<li><a href="' . $sn . '?&sysinfo">' . $tx['editmenu']['sysinfo'] . '</a></li>'
        . '</ul>' . "\n";
    return $o;
}

/**
 * Returns the log file view.
 *
 * @return string HTML
 *
 * @since 1.6
 */
function XH_logFileView()
{
    global $pth, $tx, $title;

    $title = $tx['title']['log'];
    return '<h1>' . $tx['title']['log'] . '</h1>'
        . '<pre id="xh_logfile">' . XH_hsc(XH_readFile($pth['file']['log']))
        . '</pre>'
        . '<script>'
        . '(function () {'
        . 'var elt = document.getElementById("xh_logfile");'
        . 'elt.scrollTop = elt.scrollHeight;'
        . '}())'
        . '</script>'
        . '<p>('
        . $tx['log']['timestamp'] . ' &ndash; '
        . $tx['log']['type']      . ' &ndash; '
        . $tx['log']['module']    . ' &ndash; '
        . $tx['log']['category']  . ' &ndash; '
        . $tx['log']['description']
        . ')</p>';
}

/**
 * Returns the backup view.
 *
 * @return string HTML
 *
 * @since 1.6
 */
function XH_backupsView()
{
    global $pth, $sn, $tx, $_XH_csrfProtection;

    $o = '<ul>' . "\n";
    if (isset($_GET['xh_success'])) {
        $o .= XH_message('success', $tx['message'][$_GET['xh_success']]);
    }
    $o .= '<li>' . utf8_ucfirst($tx['filetype']['content']) . ' <a href="'
        . $sn . '?file=content&amp;action=view" target="_blank">'
        . $tx['action']['view'] . '</a>' . ' <a href="' . $sn . '?file=content">'
        . $tx['action']['edit'] . '</a>' . ' <a href="'
        . $sn . '?file=content&amp;action=download">' . $tx['action']['download']
        . '</a>'
        . ' <form action="' . $sn . '?&xh_backups" method="post"'
        . ' class="xh_inline_form" id="xh_backup_form">'
        . '<input type="hidden" name="file" value="content">'
        . '<input type="hidden" name="action" value="backup">'
        . '<input type="hidden" name="xh_suffix" value="extra">'
        . '<input type="submit" class="submit" value="'
        . $tx['action']['backup'] . '">'
        . $_XH_csrfProtection->tokenInput()
        . '</form>'
        . ' <form action="' . $sn . '?&xh_backups" method="post"'
        . ' class="xh_inline_form">'
        . '<input type="hidden" name="file" value="content">'
        . '<input type="hidden" name="action" value="empty">'
        . '<input type="submit" class="submit" value="'
        . $tx['action']['empty'] . '">'
        . $_XH_csrfProtection->tokenInput()
        . '</form>'
        . '</li>' . "\n";
    $o .= '</ul>' . "\n" . '<hr>' . "\n" . '<h2>'
        . $tx['h2']['xh_backups'] . '</h2>' . "\n" . '<p>'
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
                . '<input type="hidden" name="file" value="' . $p . '">'
                . '<input type="hidden" name="action" value="restore">'
                . '<input type="submit" class="submit" value="'
                . $tx['action']['restore'] . '">'
                . $_XH_csrfProtection->tokenInput()
                . '</form>'
                . '</li>' . "\n";
        }
    }
    $o .= '</ul>' . "\n";
    return $o;
}

/**
 * Returns the plugins view.
 *
 * @return string HTML
 *
 * @since 1.7
 */
function XH_pluginsView()
{
    global $sn, $cf, $tx;

    $plugins = XH_plugins(true);
    $hiddenPlugins = explode(',', $cf['plugins']['hidden']);
    $hiddenPlugins = array_map('trim', $hiddenPlugins);
    $plugins = array_diff($plugins, $hiddenPlugins);
    sort($plugins, SORT_NATURAL | SORT_FLAG_CASE);

    $o = '<h1>' . $tx['title']['plugins'] . '</h1><ul>';
    foreach ($plugins as $plugin) {
        $item = array(
            'label' => utf8_ucfirst($plugin),
            'url' => "$sn?$plugin&normal",
            'children' => XH_registerPluginMenuItem($plugin)
        );
        $o .= XH_adminMenuItem($item, 0);
    }
    $o .= '</ul>';
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
 */
function pluginMenu($add = '', $link = '', $target = '', $text = '', array $style = array())
{
    global $_XH_pluginMenu;

    switch (strtoupper($add)) {
        case 'ROW':
            $_XH_pluginMenu->makeRow($style);
            break;
        case 'TAB':
            $_XH_pluginMenu->makeTab($link, $target, $text, $style);
            break;
        case 'DATA':
            $_XH_pluginMenu->makeData($text, $style);
            break;
        case 'SHOW':
            return $_XH_pluginMenu->show();
    }
}

/**
 * Registers the standard plugin menu items for the admin menu.
 *
 * @param bool $showMain Whether to display the main settings item.
 *
 * @return void
 *
 * @since 1.6.2
 */
function XH_registerStandardPluginMenuItems($showMain)
{
    $pluginMenu = new XH\IntegratedPluginMenu();
    $pluginMenu->render($showMain);
}

/**
 * Register a new plugin menu item, or returns the registered plugin menu items,
 * if <var>$label</var> and <var>$url</var> are null.
 *
 * @param string $plugin A plugin name.
 * @param string $label  A menu item label.
 * @param string $url    A URL to link to.
 * @param string $target A target attribute value.
 *
 * @return mixed
 *
 * @since 1.6.2
 */
function XH_registerPluginMenuItem($plugin, $label = null, $url = null, $target = null)
{
    static $pluginMenu = array();

    if (isset($label) && isset($url)) {
        $pluginMenu[$plugin][] = array(
            'label' => $label,
            'url' => $url,
            'target' => $target
        );
    } else {
        if (isset($pluginMenu[$plugin])) {
            return $pluginMenu[$plugin];
        } else {
            return array();
        }
    }
}

/**
 * Returns the admin menu.
 *
 * @param array $plugins A list of plugins.
 *
 * @return string HTML
 *
 * @since 1.6
 */
function XH_adminMenu(array $plugins = array())
{
    global $sn, $edit, $s, $u, $cf, $tx, $su, $plugin_tx;

    if ($s < 0) {
        $su = $u[0];
    }
    $changeMode = $edit ? 'normal' : 'edit';
    $changeText = $edit ? $tx['editmenu']['normal'] : $tx['editmenu']['edit'];

    $filesMenu = array();
    foreach (array('images', 'downloads', 'media') as $item) {
        $filesMenu[] =  array(
            'label' => utf8_ucfirst($tx['editmenu'][$item]),
            'url' => $sn . '?&edit&' . $item
        );
    }
    $settingsMenu = array(
        array(
            'label' => utf8_ucfirst($tx['editmenu']['configuration']),
            'url' => $sn . '?file=config&action=array'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['language']),
            'url' => $sn . '?file=language&action=array'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['template']),
            'url' => $sn . '?file=template&action=edit'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['stylesheet']),
            'url' => $sn . '?file=stylesheet&action=edit'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['log']),
            'url' => $sn . '?file=log&action=view'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['validate']),
            'url' => $sn . '?&validate'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['backups']),
            'url' => $sn . '?&xh_backups'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['pagedata']),
            'url' => $sn . '?&xh_pagedata'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['change_password']),
            'url' => $sn . '?&xh_change_password'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['sysinfo']),
            'url' => $sn . '?&sysinfo'
        )
    );
    $hiddenPlugins = explode(',', $cf['plugins']['hidden']);
    $hiddenPlugins = array_map('trim', $hiddenPlugins);
    $plugins = array_diff($plugins, $hiddenPlugins);
    $total = count($plugins);
    $rows = 12;
    $columns = ceil($total / $rows);
    $rows = ceil($total / $columns);
    $width = 150 * $columns;
    $marginLeft = min($width, 300) - $width;
    sort($plugins, SORT_NATURAL | SORT_FLAG_CASE);
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
            $pluginMenuItem['url'] = $sn . '?' . $plugin . '&normal';
            foreach (XH_registerPluginMenuItem($plugin) as $item) {
                $pluginMenuItem['children'][] = $item;
            }
        }
        $pluginMenu[] = $pluginMenuItem;
    }
    $menu = array(
        array(
            'label' => $changeText,
            'url' => $sn . '?' . $su . '&' . $changeMode,
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['pagemanager']),
            'url' => $sn . '?&normal&xhpages'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['files']),
            'url' => $sn . '?&edit&userfiles',
            'children' => $filesMenu
            ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['settings']),
            'url' => $sn . '?&settings',
            'children' => $settingsMenu
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['plugins']),
            'url' => $sn . '?&xh_plugins',
            'children' => $pluginMenu,
            'id' => 'xh_adminmenu_plugins',
            'style' => 'width:' . $width . 'px; margin-left: ' . $marginLeft . 'px'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['logout']),
            'url' => $sn . '?&logout'
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
 * @since 1.6
 */
function XH_adminMenuItem(array $item, $level = 0)
{
    $indent = str_repeat('    ', $level);
    $t = $indent . '<li>';
    if (isset($item['url'])) {
        $t .= '<a href="' . XH_hsc($item['url']) . '"';
        if (isset($item['target'])) {
            $t .= ' target="' . $item['target'] . '"';
        }
        $t .= '>';
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
        if (isset($item['id'])) {
            $t .= ' id="' . $item['id'] . '"';
        }
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
 * @return string HTML
 */
function print_plugin_admin($main)
{
    global $_XH_pluginMenu;

    return $_XH_pluginMenu->render(strtoupper($main) == 'ON');
}

/**
 * Handles reading and writing of plugin files
 * (e.g. en.php, config.php, stylesheet.css).
 *
 * @return string Returns the created form or the result of saving the data.
 */
function plugin_admin_common()
{
    global $action, $admin;

    switch ($admin) {
        case 'plugin_config':
            $fileEdit = new XH\PluginConfigFileEdit();
            break;
        case 'plugin_language':
            $fileEdit = new XH\PluginLanguageFileEdit();
            break;
        case 'plugin_stylesheet':
            $fileEdit = new XH\PluginTextFileEdit();
            break;
        default:
            return '';
    }
    switch ($action) {
        case 'plugin_edit':
        case 'plugin_text':
            return $fileEdit->form();
        case 'plugin_save':
        case 'plugin_textsave':
            return $fileEdit->submit();
        default:
            return '';
    }
}


/**
 * Returns the content editor and activates it.
 *
 * @return string  HTML
 *
 * @since 1.6
 */
function XH_contentEditor()
{
    global $sn, $su, $s, $u, $c, $e, $cf, $tx, $_XH_csrfProtection, $l, $h, $s;

    $su = $u[$s]; // TODO: is changing of $su correct here???

    $editor = $cf['editor']['external'] == '' || init_editor();
    if (!$editor) {
        $msg = sprintf($tx['error']['noeditor'], $cf['editor']['external']);
        $e .= '<li>' . $msg . '</li>' . "\n";
    }
    $o = '<form method="POST" id="ta" action="' . $sn . '">'
        . '<input type="hidden" name="selected" value="' . $u[$s] . '">';
    //Add page level and heading to post data because the split markers
    //are filtered out if mode is not "advanced"
    if (!$cf['mode']['advanced']) {
        $o .= '<input type="hidden" name="level" value="' . $l[$s] . '">'
            . '<input type="hidden" name="heading" value="' . $h[$s] . '">';
        //replace split-markers
        $tempContent = preg_replace('/<!--XH_ml[1-9]:.*?-->/isu', '', $c[$s]);
    } else {
        $tempContent = $c[$s];
    }
    $o .= '<input type="hidden" name="function" value="save">'
        . '<textarea name="text" id="text" class="xh-editor" style="height: '
        . $cf['editor']['height'] . 'px; width: 100%;" rows="30" cols="80">'
        . XH_hsc($tempContent)
        . '</textarea>'
        . '<script>'
        . 'document.getElementById("text").style.height=(' . $cf['editor']['height']
        . ') + "px";</script>'
        . $_XH_csrfProtection->tokenInput();
    if ($cf['editor']['external'] == '' || !$editor) {
        $value = utf8_ucfirst($tx['action']['save']);
        $o .= '<input type="submit" value="' . $value . '">';
    }
    $o .= '</form>';
    return $o;
}

/**
 * Saves the current contents (including the page data), if edit mode is active.
 *
 * @return bool Whether that succeeded
 *
 * @since 1.6
 */
function XH_saveContents()
{
    global $c, $pth, $tx, $edit, $pd_router;

    if (!(XH_ADM && $edit)) {
        trigger_error(
            'Function ' . __FUNCTION__ . '() must not be called in view mode',
            E_USER_WARNING
        );
        return false;
    }
    $hot = '<!--XH_ml[1-9]:';
    $hct = '-->';
    $title = utf8_ucfirst($tx['filetype']['content']);
    $cnts = "<html><head><title>$title</title>\n"
        . $pd_router->headAsPHP()
        . '</head><body>' . "\n";
    foreach ($c as $j => $i) {
        preg_match("/(.*?)($hot(.+?)$hct)(.*)/isu", $i, $matches);
        $page = $matches[1] . $matches[2] . PHP_EOL . $pd_router->pageAsPHP($j)
            . trim($matches[4], "\r\n");
        $cnts .= $page . "\n";
    }
    $cnts .= '</body></html>';
    if (!file_exists($pth['folder']['content'])) {
        mkdir($pth['folder']['content'], 0x755, true);
    }
    return XH_writeFile($pth['file']['content'], $cnts) !== false;
}

/**
 * Saves content.htm after submitting changes from the content editor.
 *
 * @param string $text The text to save.
 *
 * @return void
 *
 * @since 1.6
 */
function XH_saveEditorContents($text)
{
    global $pth, $cf, $tx, $pd_router, $c, $s, $u, $selected;

    //clean up and inject split-markers
    if (!$cf['mode']['advanced']) {
        $text = preg_replace('/<!--XH_ml[1-9]:.*?-->/isu', '', $text);
        $split = '<!--XH_ml' . $_POST['level'] . ':'
            . $_POST['heading'] . '-->'
            . "\n";
        $text = $split . $text;
    }
    $hot = '<!--XH_ml[1-9]:';
    $hct = '-->';
    // remove empty headings
    $text = preg_replace("/$hot(&nbsp;|&#160;|\xC2\xA0| )?$hct/isu", '', $text);
    // replace P elements around plugin calls and scripting with DIVs
    $text = preg_replace('/<p>({{{.*?}}}|#CMSimple .*?#)<\/p>/isu', '<div>$1</div>', $text);

    // handle missing heading on the first page
    if ($s == 0) {
        if (!preg_match('/^<!--XH_ml[1-9]:.+-->/isu', $text)
            && !preg_match('/^(<p[^>]*>)?(\&nbsp;| |<br \/>)?(<\/p>)?$/isu', $text)
        ) {
            $text = '<!--XH_ml1:' . $tx['toc']['missing'] . '-->' . "\n" . $text;
        }
    }
    $c[$s] = $text; // keep editor contents, if saving fails

    // insert $text to $c
    $text = preg_replace('/<!--XH_ml[1-9]:/is', "\x00" . '$0', $text);
    $pages = explode("\x00", $text);
    // append everything before the first page to the previous page
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
            array_splice($urlParts, -1, 1, uenc(trim(xh_rmws(strip_tags($matches[1][0])))));
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
 * @since 1.6
 */
function XH_restore($filename)
{
    global $pth, $e;

    $tempFilename = $pth['folder']['content'] . 'restore.htm';
    if (!rename($filename, $tempFilename)) {
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
    if (!rename($tempFilename, $pth['file']['content'])) {
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
 * Returns SCRIPT element containing the localization for admin.min.js.
 *
 * @return string HTML
 *
 * @since 1.6
 */
function XH_adminJSLocalization()
{
    global $tx;

    $keys = array(
        'action' => array('advanced_hide', 'advanced_show', 'cancel', 'ok'),
        'error' => array('server'),
        'password' => array('score'),
        'settings' => array('backupsuffix')
    );
    $l10n = array();
    foreach ($keys as $category => $keys2) {
        foreach ($keys2 as $key) {
            $l10n[$category][$key] = $tx[$category][$key];
        }
    }
    $o = '<script>XH.i18n = '
        . XH_encodeJson($l10n) . '</script>' . PHP_EOL;
    return $o;
}

/**
 * Returns whether the administration of a certain plugin is requested.
 *
 * @param string $pluginName A plugin name.
 *
 * @return bool
 *
 * @since 1.6.3
 */
function XH_wantsPluginAdministration($pluginName)
{
    return (bool) preg_match('/(?:^|&)' . preg_quote($pluginName, '/') . '(?=&|$)/', sv('QUERY_STRING'));
}
