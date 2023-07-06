<?php

namespace XH;

/**
 * Editing of core config files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
 */
class CoreConfigFileEdit extends CoreArrayFileEdit
{
    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        global $pth, $cf, $tx;

        parent::__construct();
        $this->varName = 'cf';
        $this->params = array(
            'form' => 'array',
            'file' => 'config',
            'action' => 'save'
        );
        $this->redir = '?file=config&action=array&xh_success=config';
        $this->cfg = array();
        $mcf = array();
        $fn = $pth['folder']['cmsimple'] . 'metaconfig.php';
        if (is_readable($fn)) {
            include $fn;
        }
        foreach ($cf as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
                // The following are there for backwards compatibility,
                // and have to be suppressed in the config form.
                if ($cat == 'security' && $name == 'type'
                    || $cat == 'scripting' && $name == 'regexp'
                    || $cat == 'site' && $name == 'title'
                    || $cat == 'xhtml'
                ) {
                    continue;
                }
                $omcf = isset($mcf[$cat][$name]) ? $mcf[$cat][$name] : null;
                $hint = isset($tx['help']["{$cat}_$name"])
                    ? $tx['help']["{$cat}_$name"] : null;
                $this->cfg[$cat][$name] = $this->option($omcf, $val, $hint);
            }
            if (empty($this->cfg[$cat])) {
                unset($this->cfg[$cat]);
            }
        }
    }
}
