<?php

namespace XH;

/**
 * Editing of plugin language files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @see       http://cmsimple-xh.org/
 * @since     1.6
 */
class PluginLanguageFileEdit extends PluginArrayFileEdit
{
    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        global $pth, $plugin, $tx, $plugin_tx;

        parent::__construct();
        $this->caption = ucfirst($plugin) . ' &ndash; '
            . utf8_ucfirst($tx['filetype']['language']);
        $this->filename = $pth['file']['plugin_language'];
        $this->params = array('admin' => 'plugin_language',
                              'action' => 'plugin_save');
        $this->redir = '?&' . $plugin
            . '&admin=plugin_language&action=plugin_edit&xh_success=language';
        $this->varName = 'plugin_tx';
        $this->cfg = array();
        foreach ($plugin_tx[$plugin] as $key => $val) {
            list($cat, $name) = $this->splitKey($key);
            $co = array('val' => $val, 'type' => 'text', 'isAdvanced' => false);
            $this->cfg[$cat][$name] = $co;
        }
    }
}
