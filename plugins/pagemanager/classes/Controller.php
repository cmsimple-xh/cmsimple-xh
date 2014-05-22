<?php

/**
 * The controller class of Pagemanager_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: Controller.php 179 2014-01-28 13:24:08Z cmb $
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

/**
 * The controller class of Pagemanager_XH.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class Pagemanager_Controller
{
    /**
     * The pagemanager model.
     *
     * @var object
     */
    var $model;

    /**
     * The pages object.
     *
     * @var object
     */
    var $pages;

    /**
     * Initializes a newly create object.
     *
     * @global array The paths of system files and folders.
     */
    function Pagemanager_Controller()
    {
        global $pth;

        include_once $pth['folder']['plugin_classes'] . 'Model.php';
        $this->model = new Pagemanager_Model();
    }

    /**
     * Returns a rendered template.
     *
     * @param string $template A template name.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     * @global array The configuration of the core.
     */
    function render($template)
    {
        global $pth, $cf;

        $template = "{$pth['folder']['plugins']}pagemanager/views/$template.php";
        ob_start();
        include $template;
        $o = ob_get_clean();
        if (!$cf['xhtml']['endtags']) {
            $o = str_replace('/>', '>', $o);
        }
        return $o;
    }

    /**
     * Returns the system checks.
     *
     * @return array
     *
     * @global array The paths of system files and folders.
     * @global array The localization of the core.
     * @global array The localization of the plugins.
     */
    function systemChecks()
    {
        global $pth, $tx, $plugin_tx;

        $ptx = $plugin_tx['pagemanager'];
        $phpVersion = '4.3.0';
        $checks = array();
        $key = sprintf($ptx['syscheck_phpversion'], $phpVersion);
        $ok = version_compare(PHP_VERSION, $phpVersion) >= 0;
        $checks[$key] = $ok ? 'ok' : 'fail';
        foreach (array('pcre', 'xml') as $ext) {
            $key = sprintf($ptx['syscheck_extension'], $ext);
            $checks[$key] = extension_loaded($ext) ? 'ok' : 'fail';
        }
        $key = $ptx['syscheck_magic_quotes'];
        $checks[$key] = !get_magic_quotes_runtime() ? 'ok' : 'fail';
        $xhVersion = 'CMSimple_XH 1.6beta';
        $ok = strpos(CMSIMPLE_XH_VERSION, 'CMSimple_XH') === 0
            && version_compare(CMSIMPLE_XH_VERSION, $xhVersion) >= 0;
        $xhVersion = substr($xhVersion, 12);
        $key = sprintf($ptx['syscheck_xhversion'], $xhVersion);
        $checks[$key] = $ok ? 'ok' : 'fail';
        $ok = file_exists($pth['folder']['plugins'].'jquery/jquery.inc.php');
        $checks[$ptx['syscheck_jquery']] = $ok ? 'ok' : 'fail';
        $folders = array();
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = $pth['folder']['plugins'] . 'pagemanager/' . $folder;
        }
        foreach ($folders as $folder) {
            $key = sprintf($ptx['syscheck_writable'], $folder);
            $checks[$key] = is_writable($folder) ? 'ok' : 'warn';
        }
        return $checks;
    }

    /**
     * Returns the path of the plugin icon file.
     *
     * @return string
     *
     * @lobal array The paths of system files and folders.
     */
    function pluginIconPath()
    {
        global $pth;

        return $pth['folder']['plugins'] . 'pagemanager/pagemanager.png';
    }

    /**
     * Returns the path of a state icon file.
     *
     * @param string $state "ok", "warn" or "fail".
     *
     * @return string
     *
     * @global array The paths of system files and folders.
     */
    function stateIconPath($state)
    {
        global $pth;

        return "{$pth['folder']['plugins']}pagemanager/images/$state.png";
    }

    /**
     * Returns the path of the ajax loader GIF file.
     *
     * @return string
     *
     * @global array The paths of system files and folders.
     */
    function ajaxLoaderPath()
    {
        global $pth;

        return "{$pth['folder']['plugins']}pagemanager/images/ajax-loader-bar.gif";
    }

    /**
     * Returns a language string.
     *
     * @param string $key A key.
     *
     * @return string
     *
     * @global array The localization of the plugins.
     */
    function lang($key)
    {
        global $plugin_tx;

        return $plugin_tx['pagemanager'][$key];
    }

    /**
     * Returns the view of a single tool.
     *
     * @param string $tool A tool name.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     * @global array The configuration of the plugins.
     * @global array The localization of the core.
     * @global array The localization of the plugins.
     */
    function tool($tool)
    {
        global $pth, $plugin_cf, $tx, $plugin_tx;

        $imgdir = $pth['folder']['plugins'] . 'pagemanager/images/';
        $horizontal = !$plugin_cf['pagemanager']['toolbar_vertical'];
        $img = $imgdir . $tool . '.png';
        $id = "pagemanager-$tool";
        $o = '';
        $style = $tool === 'save' ? ' style="display: none"' : '';
        $onclick = 'PAGEMANAGER.tool(\''.$tool.'\')';
        $onclick = $tool !== 'help' ? " onclick=\"$onclick\"" : '';
        if ($tool === 'save') {
            $tooltip = XH_hsc($plugin_tx['pagemanager']['button_save']);
        } else {
            $tooltip = XH_hsc($plugin_tx['pagemanager']['op_'.$tool]);
        }
        if ($tool !== 'help') {
            $o .= '<button type="button" id="' . $id . '" ' . $style . $onclick
                . ' title="' . $tooltip . '"' . '></button>';
        } else {
            $o .= '<a href="' . $pth['file']['plugin_help']
                . '" target="_blank" id="' . $id . '" title="' . $tooltip . '"></a>';
        }
        if (!$horizontal) {
            $o .= tag('br');
        }
        $o .= "\n";
        return $o;
    }

    /**
     * Returns the widget configuration.
     *
     * @return string JSON.
     *
     * @global array  The paths of system files and folders.
     * @global string The script name.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global array  The configuration of the plugins.
     * @global array  The localization of the plugins.
     */
    function jsConfig()
    {
        global $pth, $sn, $cf, $tx, $plugin_cf, $plugin_tx;

        $pcf = $plugin_cf['pagemanager'];
        $ptx = $plugin_tx['pagemanager'];
        $config = array(
            'okButton' => $ptx['button_ok'],
            'cancelButton' => $ptx['button_cancel'],
            'deleteButton' => $ptx['button_delete'],
            'menuLevels' => (int) $cf['menu']['levels'],
            'verbose' => (bool) $pcf['verbose'],
            'menuLevelMessage' => $ptx['message_menu_level'],
            'cantRenameError' => $ptx['error_cant_rename'],
            'deleteLastMessage' => $ptx['message_delete_last'],
            'confirmDeletionMessage' => $ptx['message_confirm_deletion'],
            'leaveWarning' => $ptx['message_warning_leave'],
            'leaveConfirmation' => $ptx['message_confirm_leave'],
            'animation' => (int) $pcf['treeview_animation'],
            'loading' => $ptx['treeview_loading'],
            'newNode' => $ptx['treeview_new'],
            'imageDir' => $pth['folder']['plugins'].'pagemanager/images/',
            'menuLevelMessage' => $ptx['message_menu_level'],
            'theme' => $pcf['treeview_theme'],
            'createOp' => $ptx['op_create'],
            'createAfterOp' => $ptx['op_create_after'],
            'renameOp' => $ptx['op_rename'],
            'deleteOp' => $ptx['op_delete'],
            'cutOp' => $ptx['op_cut'],
            'copyOp' => $ptx['op_copy'],
            'pasteOp' => $ptx['op_paste'],
            'pasteAfterOp' => $ptx['op_paste_after'],
            'noSelectionMessage' => $ptx['message_no_selection'],
            'duplicateHeading' => $tx['toc']['dupl'],
            'offendingExtensionError' => $ptx['error_offending_extension'],
            'hasCheckboxes' => $pcf['pagedata_attribute'] !== '',
            'dataURL' => $sn . '?&pagemanager&admin=plugin_main'
                . '&action=plugin_data&edit'
        );
        return XH_encodeJson($config);
    }

    /**
     * Returns the class name of the toolbar.
     *
     * @return string
     *
     * @global array The configuration of the plugins.
     */
    function toolbarClass()
    {
        global $plugin_cf;

        return $plugin_cf['pagemanager']['toolbar_vertical']
            ? 'pagemanager-vertical' : 'pagemanager-horizontal';
    }

    /**
     * Returns whether the toolbar shall be shown.
     *
     * @return bool
     *
     * @global array The configuration of the plugins.
     */
    function hasToolbar()
    {
        global $plugin_cf;

        return (bool) $plugin_cf['pagemanager']['toolbar_show'];
    }

    /**
     * Returns the available tools.
     *
     * @return array
     */
    function tools()
    {
        return array(
            'save', 'expand', 'collapse', 'create', 'create_after', 'rename',
            'delete', 'cut', 'copy', 'paste', 'paste_after', 'help'
        );
    }

    /**
     * Returns the URL for saving.
     *
     * @return string
     *
     * @global string The script name.
     */
    function submissionURL()
    {
        global $sn;

        $xhpages = isset($_GET['xhpages']) ? '&pagemanager-xhpages' : '';
        return "$sn?&pagemanager&$xhpages&edit";
    }

    /**
     * Returns the path of the JS script file.
     *
     * @return string
     *
     * @global array The paths of system files and folders.
     */
    function jsScriptPath()
    {
        global $pth;

        return "{$pth['folder']['plugins']}pagemanager/pagemanager.js";
    }

    /**
     * Returns the page administration view.
     *
     * @return string (X)HTML.
     *
     * @global array  The paths of system files and folders.
     */
    function editView()
    {
        global $pth;

        include_once $pth['folder']['plugins'] . 'jquery/jquery.inc.php';
        include_jQuery();
        include_jQueryUI();
        include_jQueryPlugin(
            'jsTree',
            $pth['folder']['plugins'] . 'pagemanager/jstree/jquery.jstree.js'
        );
        return $this->render('widget');
    }

    /**
     * Saves the submitted site structure.
     *
     * @return (X)HTML.
     *
     * @global array  The paths of system files and folders.
     * @global array  The localization of the plugins.
     * @global object The CSRF protection object.
     */
    function save()
    {
        global $pth, $plugin_tx, $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        $ptx = $plugin_tx['pagemanager'];
        if ($this->model->save(stsl($_POST['xml']))) {
            echo XH_message('success', $ptx['message_save_success']);
        } else {
            $message = sprintf(
                $ptx['message_save_failure'], $pth['file']['content']
            );
            echo XH_message('fail', $message);
        }
        exit;
    }

    /**
     * Returns the view of a single page.
     *
     * @param int $index A page index.
     *
     * @return string XML.
     *
     * @global array  The configuration of the plugins.
     * @global object The page data router.
     */
    function page($index)
    {
        global $plugin_cf, $pd_router;

        $pcf = $plugin_cf['pagemanager'];
        $pageData = $pd_router->find_page($index);
        if ($pcf['pagedata_attribute'] === '') {
            $pdattr = '';
        } elseif ($pageData[$pcf['pagedata_attribute']] === '') {
            $pdattr = ' data-pdattr="1"';
        } else {
            $pdattr = $pageData[$pcf['pagedata_attribute']];
            $pdattr = " data-pdattr=\"$pdattr\"";
        }
        $heading = $this->model->headings[$index];
        $mayRename = $this->model->mayRename[$index];
        $rename = $mayRename ? '' : ' class="pagemanager-no-rename"';
        return "<item id=\"pagemanager-$index\" title=\"$heading\"$pdattr$rename>"
            . "<content><name>$heading</name></content>";
    }

    /**
     * Returns the page structure.
     *
     * @param int $parent The index of the parent page.
     *
     * @return string XML.
     *
     * @global array The paths of system files and folders.
     */
    function pages($parent = null)
    {
        global $pth;

        include_once $pth['folder']['classes'] . 'Pages.php';
        if (!isset($this->pages)) {
            $this->pages = new XH_Pages();
        }
        if (!isset($parent)) {
            $o = '<root>';
        }
        $children = !isset($parent)
            ? $this->pages->toplevels(false)
            : $this->pages->children($parent, false);
        foreach ($children as $index) {
            $o .= $this->page($index) . $this->pages($index) . '</item>';
        }
        if (!isset($parent)) {
            $o .= '</root>';
        }
        return $o;
    }

    /**
     * Dispatches according to the current request.
     *
     * @return string The (X)HTML.
     *
     * @global string The admin parameter.
     * @global string The action parameter.
     * @global string Whether pagemanager administration is requested.
     * @global array  The paths of system files and folders.
     * @global string The requested function.
     * @global array  The configuration of the core.
     */
    function dispatch()
    {
        global $admin, $action, $pagemanager, $pth, $plugin, $f, $cf;

        $o = '';
        if ($f === 'xhpages'
            && in_array($cf['pagemanager']['external'], array('', 'pagemanager'))
        ) {
            $o .= $this->editView();
        } elseif (isset($pagemanager) && $pagemanager === 'true') {
            $o .= print_plugin_admin('on');
            switch ($admin) {
            case '':
                $o .= $this->render('info');
                break;
            case 'plugin_main':
                switch ($action) {
                case 'plugin_data':
                    $this->model->getHeadings();
                    header('Content-Type: application/xml; charset=UTF-8');
                    echo $this->pages();
                    exit;
                case 'plugin_save':
                    $o .= $this->save();
                    break;
                default:
                    $o .= $this->editView();
                }
                break;
            default:
                $o .= plugin_admin_common($action, $admin, $plugin);
            }
        }
        return $o;
    }
}

?>
