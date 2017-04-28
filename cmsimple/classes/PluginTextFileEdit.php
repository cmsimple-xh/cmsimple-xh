<?php

/**
 * Editing of plugin text files.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

/**
 * Editing of plugin text files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PluginTextFileEdit extends TextFileEdit
{
    /**
     * Construct an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The name of the currently loading plugin.
     * @global array  The localization of the core.
     */
    public function __construct()
    {
        global $pth, $plugin, $tx;

        $this->plugin = $plugin;
        $this->filename = $pth['file']['plugin_stylesheet'];
        $this->params = array('admin' => 'plugin_stylesheet',
                              'action' => 'plugin_textsave');
        $this->redir = '?&' . $plugin
            . '&admin=plugin_stylesheet&action=plugin_text&xh_success=stylesheet';
        $this->textareaName = 'plugin_text';
        $this->caption = utf8_ucfirst($plugin) . ' &ndash; '
            . utf8_ucfirst($tx['filetype']['stylesheet']);
        parent::__construct();
    }
}
