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
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */


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
        $o .= '<li>' . ucfirst($temp) . '</li>' . "\n";
    }
    $o .= '</ul>' . "\n" . "\n";

    $o .= '<p><b>' . $tx['sysinfo']['php_version'] . '</b></p>' . "\n"
        . '<ul>' . "\n" . '<li>' . phpversion() . '</li>' . "\n"
        . '<li><a href="./?&phpinfo" target="blank"><b>'
        . $tx['sysinfo']['phpinfo_link'] . '</b></a> &nbsp; '
        . $tx['sysinfo']['phpinfo_hint'] . '</li>' . "\n" . '</ul>' . "\n" . "\n";

    $o .= '<h4>' . $tx['sysinfo']['helplinks'] . '</h4>' . "\n" . "\n";
    $o .= <<<HTML
<ul>
<li><a href="http://www.cmsimple-xh.com/">cmsimple-xh.com &raquo;</a></li>
<li><a href="http://www.cmsimple.org/">cmsimple.org &raquo;</a></li>
<li><a href="http://www.cmsimpleforum.com/">cmsimpleforum.com &raquo;</a></li>
<li><a href="http://www.cmsimplewiki.com/">cmsimplewiki.com &raquo;</a></li>
</ul>

HTML;

    $checks = array(
        'phpversion' => '4.3',
        'extensions' => array(
            array('date', false),
            'pcre',
            array('session', false),
            array('xml', false)
        ),
        'writable' => array(),
        'other' => array()
    );
    $temp = array('content', 'images', 'downloads', 'userfiles', 'media');
    foreach ($temp as $i) {
        $checks['writable'][] = $pth['folder'][$i];
    }
    $temp = array('config', 'log', 'language', 'content', 'template', 'stylesheet');
    foreach ($temp as $i) {
        $checks['writable'][] = $pth['file'][$i];
    }
    $checks['writable'] = array_unique($checks['writable']);
    sort($checks['writable']);
    if ($tx['locale']['all'] == '') {
        $checks['other'][] = array(true, false, $tx['syscheck']['locale_default']);
    } else {
        $checks['other'][] = array(
            setlocale(LC_ALL, $tx['locale']['all']), false,
            sprintf($tx['syscheck']['locale_available'], $tx['locale']['all'])
        );
    }
    $checks['other'][] = array(!get_magic_quotes_runtime(),
                             false, $tx['syscheck']['magic_quotes']);
    $o .= XH_systemCheck($checks);
    return $o;
}


/**
 * Returns the general settings view.
 *
 * @global array  The localization of the core.
 *
 * @return string The (X)HTML.
 *
 * @since 1.6
 *
 * @todo Add $sn to links.
 */
function XH_settingsView()
{
    global $tx;

    $o = '<p>' . $tx['settings']['warning'] . '</p>' . "\n"
        . '<h4>' . $tx['settings']['systemfiles'] . '</h4>' . "\n" . '<ul>' . "\n";

    foreach (array('config', 'language') as $i) {
        $o .= '<li><a href="?file=' . $i . '&amp;action=array">'
            . utf8_ucfirst($tx['action']['edit']) . ' '
            . $tx['filetype'][$i] . '</a></li>' . "\n";
    }

    foreach (array('stylesheet', 'template') as $i) {
        $o .= '<li><a href="?file=' . $i . '&amp;action=edit">'
            . utf8_ucfirst($tx['action']['edit']) . ' '
            . $tx['filetype'][$i] . '</a></li>' . "\n";
    }
    foreach (array('log') as $i) {
        $o .= '<li><a href="?file=' . $i . '&amp;action=view">'
            . utf8_ucfirst($tx['action']['view']) . ' '
            . $tx['filetype'][$i] . '</a></li>' . "\n";
    }
    $o .= '</ul>' . "\n";

    $o .= '<h4>' . $tx['settings']['backup'] . '</h4>' . "\n";
    $o .= XH_backupsView();
    return $o;
}

/**
 * Returns the backup view.
 *
 * @global array  The paths of system files and folders.
 * @global array  The localization of the core.
 * @global object The CSRF protection object.
 *
 * @return string The (X)HTML.
 *
 * @since 1.6
 *
 * @todo Add $sn to links.
 */
function XH_backupsView()
{
    global $pth, $tx, $_XH_csrfProtection;

    $o = '<ul>' . "\n";
    if (isset($_GET['xh_success'])) {
        $o .= XH_message('success', null, stsl($_GET['xh_success']));
    }
    $o .= '<li>' . utf8_ucfirst($tx['filetype']['content']) . ' <a href="'
        . '?file=content&amp;action=view">'
        . $tx['action']['view'] . '</a>' . ' <a href="?file=content">'
        . $tx['action']['edit'] . '</a>' . ' <a href="'
        . '?file=content&amp;action=download">' . $tx['action']['download']
        . '</a>'
        . ' <form action="" method="post" class="xh_inline_form">'
        . tag('input type="hidden" name="file" value="content"')
        . tag('input type="hidden" name="action" value="delete"')
        . tag(
            'input type="submit" class="submit" value="'
            . $tx['action']['delete'] . '"'
        )
        . $_XH_csrfProtection->tokenInput()
        . '</form>'
        . '</li>' . "\n";
    $o .= '</ul>' . "\n" . tag('hr') . "\n" . '<p>'
        . $tx['settings']['backupexplain1'] . '</p>' . "\n" . '<p>'
        . $tx['settings']['backupexplain2'] . '</p>' . "\n" . '<ul>' . "\n";
    $fs = sortdir($pth['folder']['content']);
    foreach ($fs as $p) {
        if (XH_isContentBackup($p)) {
            $size = filesize($pth['folder']['content'] . '/' . $p);
            $size = round(($size) / 102.4) / 10;
            $o .= '<li><a href="?file=' . $p . '&amp;action=view">'
                . $p . '</a> (' . $size . ' KB)'
                . ' <form action="" method="post" class="xh_inline_form">'
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

    $menu_row = '<table {{STYLE_ROW}} cellpadding="1" cellspacing="0">' . "\n"
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
 *
 * @todo Add internationalization for missing editor message.
 */
function XH_contentEditor()
{
    global $sn, $su, $s, $u, $c, $e, $cf, $tx, $_XH_csrfProtection;

    $su = $u[$s]; // TODO: is changing of $su correct here???

    $editor = $cf['editor']['external'] == '' || init_editor();
    if (!$editor) {
        $msg = sprintf('External editor %s missing', $cf['editor']['external']);
        $e .= '<li>' . $msg . '</li>' . "\n";
    }
    $o = '<form method="POST" id="ta" action="' . $sn . '">'
        . tag('input type="hidden" name="selected" value="' . $u[$s] . '"')
        . tag('input type="hidden" name="function" value="save"')
        . '<textarea name="text" id="text" class="xh-editor" style="height: '
        . $cf['editor']['height'] . 'px; width: 100%;" rows="30" cols="80">'
        . htmlspecialchars($c[$s], ENT_QUOTES, 'UTF-8')
        . '</textarea>'
        . $_XH_csrfProtection->tokenInput();
    if ($cf['editor']['external'] == '' || !$editor) {
        $value = utf8_ucfirst($tx['action']['save']);
        $o .= tag('input type="submit" value="' . $value . '"');
    }
    $o .= '</form>';
    return $o;
}


/**
 * Saves content.htm and pagedata.php after submitting changes
 * from the content editor.
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
    // replace "p" elements around plugin calls and scripting with "div"s
    // TODO: keep an eye on changes regarding the plugin call
    $text = preg_replace(
        '/<p>({{{PLUGIN:.*?}}}|#CMSimple .*?#)<\/p>/is', '<div>$1</div>', $text
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
        header("Location: " . $sn . "?" . $su);
        exit;
    } else {
        e('notwritable', 'content', $pth['file']['content']);
    }
}

/**
 * Deletes all contents.
 *
 * @return void
 *
 * @global array  The content of the pages.
 * @global int    The number of pages.
 * @global array  The paths of system files and folders.
 * @global array  An (X)HTML fragment with error messages.
 * @global object The pagedata router.
 */
function XH_deleteContents()
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
        $url = CMSIMPLE_URL . '?&settings&xh_success=deleted';
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
    $url = CMSIMPLE_URL . '?&settings&xh_success=restored';
    header('Location: ' . $url, true, 303);
    exit;
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
        'password' => array('fields_missing', 'mismatch', 'wrong')
    );
    $l10n = array();
    foreach ($keys as $category => $keys2) {
        foreach ($keys2 as $key) {
            $l10n[$category][$key] = $tx[$category][$key];
        }
    }
    $o = '<script type="text/javascript">/* <![CDATA[ */xh.i18n = '
        . json_encode($l10n) . '/* ]]> */</script>' . PHP_EOL;
    return $o;
}

?>
