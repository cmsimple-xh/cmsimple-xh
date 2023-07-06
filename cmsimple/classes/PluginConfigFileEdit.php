<?php

namespace XH;

/**
 * Editing of plugin config files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
 */
class PluginConfigFileEdit extends PluginArrayFileEdit
{
    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        global $pth, $plugin, $tx, $plugin_cf, $plugin_tx;

        parent::__construct();
        $this->caption = ucfirst($plugin) . ' &ndash; '
            . utf8_ucfirst($tx['filetype']['config']);
        $plugin_mcf = array();
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
