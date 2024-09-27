<?php

/**
 * Copyright 2011-2023 Christoph M. Becker
 * Copyright 2024 The CMSimple_XH developers
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

use XH\CSRFProtection;
use XH\Pages;
use XH\PageDataRouter;

class MainAdminController
{
    /**
     * @var string
     */
    private $pluginFolder;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var Pages
     */
    private $pages;

    /**
     * @var array<string,string>
     */
    private $config;

    /**
     * @var array<string,string>
     */
    private $lang;

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
        global $pth, $plugin_cf, $plugin_tx, $pd_router, $_XH_csrfProtection;

        $this->pluginFolder = "{$pth['folder']['plugins']}pagemanager/";
        $this->model = new Model;
        $this->pages = new Pages;
        $this->config = $plugin_cf['pagemanager'];
        $this->lang = $plugin_tx['pagemanager'];
        $this->pdAttr = $this->config['pagedata_attribute'];
        $this->pdRouter = $pd_router;
        $this->csrfProtector = $_XH_csrfProtection;
    }

    /**
     * @return void
     */
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
        $bjs .= '<script>var PAGEMANAGER = ' . $this->jsConfig() . ';</script>'
              . '<script src="' . XH_hsc("{$this->pluginFolder}pagemanager.min.js") . '"></script>';
        $view = new View('widget');
        $view->title = $title;
        $view->submissionUrl = $this->submissionURL();
        $view->isIrregular = $this->model->isIrregular();
        $view->ajaxLoaderPath = "{$this->pluginFolder}images/ajax-loader-bar.gif";
        $view->hasToolbar = (bool) $this->config['toolbar_show'];
        $view->tools = array(
            'save' => 'save',
            'toggle' => 'open_in_full',
            'open' => 'add_box',
            'add' => 'description',
            'rename' => 'sell',
            'remove' => 'delete',
            'cut' => 'content_cut',
            'copy' => 'content_copy',
            'paste' => 'content_paste',
            'edit' => 'edit',
            'preview' => 'visibility',
            'help' => 'menu_book'
        );
        $view->pdattr = $this->config['pagedata_attribute'];
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
        $uricharOrg = array_map(
            function ($char) {
                return preg_quote($char, '/');
            },
            explode(XH_URICHAR_SEPARATOR, $tx['urichar']['org'])
        );
        array_unshift($uricharOrg, "\xC2\xAD");
        $uricharNew = explode(XH_URICHAR_SEPARATOR, $tx['urichar']['new']);
        array_unshift($uricharNew, "");
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
                'open' => 'add_box',
                'add' => 'description',
                'rename' => 'sell',
                'remove' => 'delete',
                'cut' => 'content_cut',
                'copy' => 'content_copy',
                'paste' => 'content_paste',
                'edit' => 'edit',
                'preview' => 'visibility'
            ),
            'duplicateHeading' => $tx['toc']['dupl'],
            'offendingExtensionError' => $this->lang['error_offending_extension'],
            'hasCheckboxes' => $this->config['pagedata_attribute'] !== '',
            'dataURL' => (string) $url->with('pagemanager', '')->with('admin', 'plugin_main')
                ->with('action', 'plugin_data')->with('edit', ''),
            'uriCharOrg' => $uricharOrg,
            'uriCharNew' => $uricharNew,
        );
        return (string) json_encode($config);
    }

    /**
     * @return void
     */
    public function dataAction()
    {
        $this->model->calculateHeadings();
        $json = json_encode($this->getPagesData());
        if ($json !== false) {
            header('Content-Type: application/json; charset=UTF-8');
            echo $json;
        } else {
            header("HTTP/1.0 500 Internal Server Error");
            header('Content-Type: test/plain; charset=UTF-8');
            echo json_last_error_msg();
        }
    }

    /**
     * @param ?int $parent
     * @return list<array<string,mixed>>
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
     * @return array<string,mixed>
     */
    private function getPageData($index)
    {
        global $sn, $u;

        $pageData = $this->pdRouter->find_page($index);
        $res = array(
            'text' => $this->model->getHeading($index),
            'li_attr' => array(
                'id' => "pagemanager_{$index}",
                'data-url' => "$sn?$u[$index]"
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

    /**
     * @return void
     */
    public function saveAction()
    {
        global $pth;

        $this->csrfProtector->check();
        if ($_POST['pagemanager_pdattr'] !== $this->config['pagedata_attribute']) {
            echo XH_message('fail', $this->lang['message_pdattr']);
            return;
        }
        if ($this->model->save($_POST['json'])) {
            echo XH_message('success', $this->lang['message_save_success']);
        } else {
            $message = sprintf($this->lang['message_save_failure'], $pth['file']['content']);
            echo XH_message('fail', $message);
        }
    }
}
