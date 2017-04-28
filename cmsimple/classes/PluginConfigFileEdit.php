<?php

/**
 * Editing of plugin config files.
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
 * Editing of plugin config files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PluginConfigFileEdit extends PluginArrayFileEdit
{
    /**
     * Constructs an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The name of the currently loading plugin.
     * @global array  The localization of the core.
     * @global array  The configuration of the plugins.
     * @global array  The localization of the plugins.
     */
    public function __construct()
    {
        global $pth, $plugin, $tx, $plugin_cf, $plugin_tx;

        parent::__construct();
        $this->caption = ucfirst($plugin) . ' &ndash; '
            . utf8_ucfirst($tx['filetype']['config']);
        $fn = $pth['folder']['plugins'] . $plugin . '/config/metaconfig.php';
        if (is_readable($fn)) {
            include $fn;
        }
        $mcf = isset($plugin_mcf[$plugin]) ? $plugin_mcf[$plugin] : array();
        $this->filename = $pth['file']['plugin_config'];
        $this->params = array('admin' => 'plugin_config',
                              'action' => 'plugin_save');
        $this->redir = '?&' . $plugin
            . '&admin=plugin_config&action=plugin_edit&xh_success=config';
        $this->varName = 'plugin_cf';
        $this->cfg = array();
        foreach ($plugin_cf[$plugin] as $key => $val) {
            list($cat, $name) = $this->splitKey($key);
            $omcf = isset($mcf[$key])
                ? $mcf[$key]
                : (utf8_strlen($val) <= 50 ? 'string' : 'text');
            $hint = isset($plugin_tx[$plugin]["cf_$key"])
                ? $plugin_tx[$plugin]["cf_$key"] : null;
            $this->cfg[$cat][$name] = $this->option($omcf, $val, $hint);
        }
    }
}
