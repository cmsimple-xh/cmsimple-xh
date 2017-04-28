<?php

/**
 * Editing of plugin language files.
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
 * Editing of plugin language files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PluginLanguageFileEdit extends PluginArrayFileEdit
{
    /**
     * Constructs an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The name of the currently loading plugin.
     * @global array  The localization of the core.
     * @global array  The localization of the plugins.
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
