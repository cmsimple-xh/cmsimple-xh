<?php

namespace XH;

/**
 * Editing of plugin text files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
 */
class PluginTextFileEdit extends TextFileEdit
{
    /**
     * Construct an instance.
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
