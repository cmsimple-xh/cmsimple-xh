<?php

/**
 * Copyright 2011-2017 Christoph M. Becker
 *
 * This file is part of Pagemanager_XH.
 *
 * Pagemanager_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Pagemanager_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Pagemanager_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Pagemanager;

use Fa;
use XH\Pages;

class MainAdminController extends Controller
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var Pages
     */
    private $pages;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $pdAttr;

    /**
     * @var PageDataRouter
     */
    private $pdRouter;

    /**
     * @var CSRFProtection
     */
    private $csrfProtector;

    public function __construct()
    {
        global $plugin_cf, $pd_router, $_XH_csrfProtection;

        parent::__construct();
        $this->model = new Model;
        $this->pages = new Pages;
        $this->config = $plugin_cf['pagemanager'];
        $this->pdAttr = $this->config['pagedata_attribute'];
        $this->pdRouter = $pd_router;
        $this->csrfProtector = $_XH_csrfProtection;
    }

    public function indexAction()
    {
        global $pth, $title, $hjs, $bjs;

        if (XH_wantsPluginAdministration('pagemanager')) {
            $title = "Pagemanager – {$this->lang['menu_main']}";
        } else {
            $title = $this->lang['menu_main'];
        }
        $hjs .= '<link rel="stylesheet" type="text/css" href="'
            . "{$this->pluginFolder}jstree/themes/{$this->config['treeview_theme']}/style.min.css" . '">';
        include_once $pth['folder']['plugins'] . 'jquery/jquery.inc.php';
        include_jQuery();
        include_jQueryPlugin('jstree', "{$this->pluginFolder}jstree/jstree.min.js");
        $command = new Fa\RequireCommand;
        $command->execute();
        $bjs .= '<script type="text/javascript">var PAGEMANAGER = ' . $this->jsConfig() . ';</script>'
            . '<script type="text/javascript" src="' . XH_hsc("{$this->pluginFolder}pagemanager.js") . '"></script>';
        $view = new View('widget');
        $view->title = $title;
        $view->submissionUrl = $this->submissionURL();
        $view->isIrregular = $this->model->isIrregular();
        $view->ajaxLoaderPath = "{$this->pluginFolder}images/ajax-loader-bar.gif";
        $view->hasToolbar = (bool) $this->config['toolbar_show'];
        $view->tools = array(
            'save' => 'fa fa-save',
            'toggle' => 'fa fa-expand',
            'open' => 'fa fa-plus-square-o',
            'add' => 'fa fa-file-o',
            'rename' => 'fa fa-tag',
            'remove' => 'fa fa-trash-o',
            'cut' => 'fa fa-cut',
            'copy' => 'fa fa-copy',
            'paste' => 'fa fa-paste',
            'edit' => 'fa fa-edit',
            'preview' => 'fa fa-eye',
            'help' => 'fa fa-book'
        );
        $view->csrfTokenInput = new HtmlString($this->csrfProtector->tokenInput());
        $view->render();
    }

    /**
     * @return string
     */
    private function submissionURL()
    {
        global $sn;

        return (string) new Url($sn, array('pagemanager' => '', 'edit' => ''));
    }

    /**
     * @return string
     */
    private function jsConfig()
    {
        global $sn, $tx, $pth;

        $url = new Url($sn, array());
        $config = array(
            'stateKey' => 'pagemanager_' . bin2hex(CMSIMPLE_ROOT),
            'okButton' => $this->lang['button_ok'],
            'cancelButton' => $this->lang['button_cancel'],
            'deleteButton' => $this->lang['button_delete'],
            'menuLevels' => 9,
            'verbose' => (bool) $this->config['verbose'],
            'menuLevelMessage' => $this->lang['message_menu_level'],
            'confirmDeletionMessage' => $this->lang['message_confirm_deletion'],
            'leaveWarning' => $this->lang['message_warning_leave'],
            'leaveConfirmation' => $this->lang['message_confirm_leave'],
            'animation' => (int) $this->config['treeview_animation'],
            'loading' => $this->lang['treeview_loading'],
            'newNode' => $this->lang['treeview_new'],
            'theme' => $this->config['treeview_theme'],
            'openOp' => $this->lang['op_open'],
            'addOp' => $this->lang['op_add'],
            'renameOp' => $this->lang['op_rename'],
            'removeOp' => $this->lang['op_remove'],
            'cutOp' => $this->lang['op_cut'],
            'copyOp' => $this->lang['op_copy'],
            'pasteOp' => $this->lang['op_paste'],
            'editOp' => $this->lang['op_edit'],
            'previewOp' => $this->lang['op_preview'],
            'before' => $this->lang['label_before'],
            'inside' => $this->lang['label_inside'],
            'after' => $this->lang['label_after'],
            'userManual' => $pth['file']['plugin_help'],
            'classes' => array(
                'open' => 'fa-plus-square-o',
                'add' => 'fa-file-o',
                'rename' => 'fa-tag',
                'remove' => 'fa-trash-o',
                'cut' => 'fa-cut',
                'copy' => 'fa-copy',
                'paste' => 'fa-paste',
                'edit' => 'fa-edit',
                'preview' => 'fa-eye'
            ),
            'duplicateHeading' => $tx['toc']['dupl'],
            'offendingExtensionError' => $this->lang['error_offending_extension'],
            'hasCheckboxes' => $this->config['pagedata_attribute'] !== '',
            'dataURL' => (string) $url->with('pagemanager', '')->with('admin', 'plugin_main')
                ->with('action', 'plugin_data')->with('edit', ''),
            'uriCharOrg' => explode(XH_URICHAR_SEPARATOR, $tx['urichar']['org']),
            'uriCharNew' => explode(XH_URICHAR_SEPARATOR, $tx['urichar']['new'])
        );
        return json_encode($config);
    }

    public function dataAction()
    {
        $this->model->calculateHeadings();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->getPagesData());
    }

    /**
     * @param ?int $parent
     * @return array[]
     */
    private function getPagesData($parent = null)
    {
        $res = array();
        $children = !isset($parent)
            ? $this->pages->toplevels(false)
            : $this->pages->children($parent, false);
        foreach ($children as $index) {
            $res[] = $this->getPageData($index);
        }
        return $res;
    }

    /**
     * @param int $index
     * @return array
     */
    private function getPageData($index)
    {
        global $sn, $u;

        $pageData = $this->pdRouter->find_page($index);
        $res = array(
            'text' => $this->model->getHeading($index),
            'li_attr' => array(
                'id' => "pagemanager_{$index}",
                'data-url' => (string) new Url($sn, array($u[$index] => ''))
            ),
            'children' => $this->getPagesData($index)
        );
        if ($this->pdAttr !== '') {
            if ($pageData[$this->pdAttr] === '') {
                $res['state']['checked'] = true;
            } else {
                $res['state']['checked'] = (bool) $pageData[$this->pdAttr];
            }
        }
        if (!$this->model->getMayRename($index)) {
            $res['type'] = 'unrenameable';
        }
        return $res;
    }

    public function saveAction()
    {
        global $pth;

        $this->csrfProtector->check();
        if ($this->model->save(stsl($_POST['json']))) {
            echo XH_message('success', $this->lang['message_save_success']);
        } else {
            $message = sprintf($this->lang['message_save_failure'], $pth['file']['content']);
            echo XH_message('fail', $message);
        }
    }
}
