<?php

/**
 * Admin only functions.
 *
 * @package	XH
 * @copyright	1999-2009 <http://cmsimple.org/>
 * @copyright	2009-2012 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license	http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version	$CMSIMPLE_XH_VERSION$, $CMSIMPLE_XH_BUILD$
 * @version     $Id$
 * @link	http://cmsimple-xh.org/
 */


/**
 * Returns the system information view.
 *
 * @since   1.6
 *
 * @return  string  The (X)HTML.
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
    $o .= '<ul>'
        . '<li><a href="http://www.cmsimple-xh.com/">cmsimple-xh.com &raquo;</a></li>'
        . '<li><a href="http://www.cmsimple.org/">cmsimple.org &raquo;</a></li>'
        . '<li><a href="http://www.cmsimpleforum.com/">cmsimpleforum.com &raquo;</a></li>'
        . '<li><a href="http://www.cmsimplewiki.com/">cmsimplewiki.com &raquo;</a></li>'
        . '</ul>' . "\n" . "\n";

    $temp = array('phpversion' => '4.3',
                  'extensions' => array(array('date', false),
                                        'pcre',
                                        array('session', false),
                                        array('xml', false)),
                  'writable' => array(),
                  'other' => array());
    foreach (array('content', 'images', 'downloads', 'userfiles', 'media') as $i) {
        $temp['writable'][] = $pth['folder'][$i];
    }
    foreach (array('config', 'log', 'language', 'langconfig', 'content',
                   'template', 'stylesheet')
             as $i)
    {
        $temp['writable'][] = $pth['file'][$i];
    }
    $temp['writable'] = array_unique($temp['writable']);
    sort($temp['writable']);
    $temp['other'][] = array(!get_magic_quotes_runtime(),
                             false, $tx['syscheck']['magic_quotes']);
    $o .= XH_systemCheck($temp);
    return $o;
}


/**
 * Returns the general settings view.
 *
 * @since   1.6
 *
 * @return string  The (X)HTML.
 */
function XH_settingsView()
{
    global $sl, $pth, $cf, $tx;

    // TODO: add $sn to links

    $o = '<p>' . $tx['settings']['warning'] . '</p>' . "\n"
        . '<h4>' . $tx['settings']['systemfiles'] . '</h4>' . "\n" . '<ul>' . "\n";

    $temp = $sl == $cf['language']['default']
        ? array('config', 'langconfig', 'language')
        : array('langconfig', 'language');
    foreach ($temp as $i) {
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

    $o .= '<h4>' . $tx['settings']['backup'] . '</h4>' . "\n" . '<ul>' . "\n";
    $o .= '<li>' . utf8_ucfirst($tx['filetype']['content']) . ' <a href="'
        . '?file=content&amp;action=view">'
        . $tx['action']['view'] . '</a>' . ' <a href="?file=content">'
        . $tx['action']['edit'] . '</a>' . ' <a href="'
        . '?file=content&amp;action=download">' . $tx['action']['download']
        . '</a></li>' . "\n";
    $o .= '</ul>' . "\n" . tag('hr') . "\n" . '<p>'
        . $tx['settings']['backupexplain1'] . '</p>' . "\n" . '<p>'
        . $tx['settings']['backupexplain2'] . '</p>' . "\n" . '<ul>' . "\n";
    $fs = sortdir($pth['folder']['content']);
    foreach ($fs as $p) {
        if (preg_match('/^\d{8}_\d{6}_content.htm$/', $p)) {
            $o .= '<li><a href="?file=' . $p . '&amp;action=view">'
                . $p . '</a> ('
                . (round((filesize($pth['folder']['content'] . '/' . $p)) / 102.4) / 10)
                . ' KB)</li>' . "\n";
        }
    }
    $o .= '</ul>' . "\n";
    return $o;
}


/**
 * Create menu of plugin (add row, add tab), constructed as a table.
 *
 * @param string $add Add a ROW, a TAB or DATA (Userdefineable content). SHOW will return the menu.
 * @param string $link The link, the TAB will lead to.
 * @param string $target Target of the link (with(!) 'target=').
 * @param string $text Description of the TAB.
 * @param array $style Array with style-data for the containing table-cell
 */
function PluginMenu($add = '', $link = '', $target = '', $text = '', $style = array())
{
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
    $menu_tab = '<td {{STYLE_TAB}}><a{{STYLE_LINK}} href="{{LINK}}" {{TARGET}}>{{TEXT}}</a></td>' . "\n";
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
        $new_menu_tab_data = str_replace('{{STYLE_DATA}}', $style['data'], $new_menu_tab_data);
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
 * @param string $main  Whether the main setting menu item should be shown ('ON'/'OFF').
 * @return string (X)HTML.
 */
function print_plugin_admin($main)
{
    global $sn, $plugin, $pth, $sl, $cf, $tx, $plugin_tx;

    initvar('action');
    initvar('admin');
    PluginFiles($plugin);

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

    PluginMenu('ROW', '', '', '', array());
    if ($main) {
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_main&amp;action=plugin_text',
		   '', $tx_main, array());
    }
    if ($css) {
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_stylesheet&amp;action=plugin_text',
		   '', $tx_css, array());
    }
    if ($config) {
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_config&amp;action=plugin_edit',
		   '', $tx_config, '');
    }
    if ($language) {
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_language&amp;action=plugin_edit',
		   '', $tx_language, '');
    }
    if ($help) {
        PluginMenu('TAB', $pth['file']['plugin_help'],
		   'target="_blank"', $tx_help, '');
    }
    return PluginMenu('SHOW');
}


/**
 * Handles reading and writing of plugin files (e.g. en.php, config.php, stylesheet.css).
 *
 * @param bool $action Possible values: 'empty' = Error: empty value detected
 * @param array $admin Array, that contains debug_backtrace()-data
 * @param bool $plugin The called varname
 * @param bool $hint Array with hints for the variables (will be shown as popup)
 *
 * @global string $sn CMSimple's script-name (relative adress including $_GET)
 * @global string $action Ordered action
 * @global string $admin Ordered admin-action
 * @global string $plugin Name of the plugin, that called this function
 * @global string $pth CMSimple's configured pathes in an array
 * @global string $tx CMSimple's text-array
 * @global string $plugin_tx Plugin's text-array
 * @global string $plugin_cf Plugin's config-array
 *
 * @return string Returns the created form or the result of saving the data
 */
function plugin_admin_common($action, $admin, $plugin, $hint=array())
{
    // TODO: do something about the fake parameters
    // TODO: note that $hint is ignored now
    global $action, $admin, $plugin, $pth;

    require_once $pth['folder']['classes'] . 'FileEdit.php';
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
 * @since 1.6
 *
 * @return string  The (X)HTML.
 */
function XH_contentEditor()
{
    global $sn, $su, $s, $u, $c, $e, $cf, $tx;

    $su = $u[$s]; // TODO: is changing of $su correct here???

    $editor = $cf['editor']['external'] == '' || init_editor();
    if (!$editor) {
        $e .= '<li>' . sprintf('External editor %s missing', $cf['editor']['external'])
            . '</li>' . "\n"; // FIXME: i18n
    }
    $o = '<form method="POST" id="ta" action="' . $sn . '">'
        . tag('input type="hidden" name="selected" value="' . $u[$s] . '"')
        . tag('input type="hidden" name="function" value="save"')
        . '<textarea name="text" id="text" class="xh-editor" style="height: '
        . $cf['editor']['height'] . 'px; width: 100%;" rows="30" cols="80">'
        . htmlspecialchars($c[$s], ENT_COMPAT, 'UTF-8')
        . '</textarea>';
    if ($cf['editor']['external'] == '' || !$editor) {
        $o .= tag('input type="submit" value="'
                  . utf8_ucfirst($tx['action']['save']) . '"');
    }
    $o .= '</form>';
    return $o;
}


/**
 * Saves content.htm and pagedata.php after submitting changes from the content editor.
 *
 * @since 1.6
 *
 * @return void
 */
function XH_saveEditorContents($text)
{
    global $pth, $cf, $tx, $pd_router, $c, $s, $u, $selected;

    $hot = '<h[1-' . $cf['menu']['levels'] . '][^>]*>';
    $hct = '<\/h[1-' . $cf['menu']['levels'] . ']>'; // TODO: use $1 ?
    $text = stsl($text); // this might be done before the plugins are loaded for backward compatibility
    // remove empty headings
    $text = preg_replace("/$hot(&nbsp;|&#160;|\xC2\xA0| )?$hct/isu", '', $text);

    // handle missing heading on the first page
    if ($s == 0) {
        if (!preg_match('/^<h1[^>]*>.*<\/h1>/isu', $text)
            && !preg_match('/^(<p[^>]*>)?(\&nbsp;| |<br \/>)?(<\/p>)?$/isu', $text))
        {
            $text = '<h1>' . $tx['toc']['missing'] . '</h1>' . "\n" . $text;
        }
    }
    $c[$s] = $text; // keep editor contents, if saving fails

    if (is_writable($pth['file']['content'])) {
        // insert $text to $c
        $text = preg_replace('/<h[1-' . $cf['menu']['levels'] . ']/i', "\x00" . '$0', $text);
        $pages = explode("\x00", $text);
        array_shift($pages);
        array_splice($c, $s, 1, $pages);

        // delegate changes to $pd_router
        preg_match_all("/$hot(.+?)$hct/isu", $text, $matches);
        $pd_router->refresh_from_texteditor($matches[1], $s);

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
        header("Location: " . $sn . "?" . $su);
        exit;
    } else {
        e('notwritable', 'content', $pth['file']['content']);
    }
}

?>
