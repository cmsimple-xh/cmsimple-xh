<?php

namespace XH;

/**
 * Editing of template config files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 * @since     1.8
 */
class TemplateConfigFileEdit extends TemplateArrayFileEdit
{
    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        global $pth, $tpl_cf, $tpl_tx, $tx;

        parent::__construct();
        $this->caption = utf8_ucfirst($tx['filetype']['template']) . ' '
            . utf8_ucfirst($tx['filetype']['config']);
        $this->varName = 'tpl_cf';
        $this->params = array(
            'form' => 'array',
            'file' => 'template_config',
            'action' => 'save'
        );
        $this->redir = '?file=template_config&action=array&xh_success=config';
        $this->cfg = array();
        $tpl_mcf = array();
        $fn = "{$pth['folder']['template_config']}metaconfig.php";
        if (is_readable($fn)) {
            include $fn;
        }
        foreach ($tpl_cf as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
                $omcf = isset($tpl_mcf[$cat][$name]) ? $tpl_mcf[$cat][$name] : null;
                $hint = isset($tpl_tx['help']["${cat}_$name"])
                    ? $tpl_tx['help']["${cat}_$name"] : null;
                $this->cfg[$cat][$name] = $this->option($omcf, $val, $hint);
            }
            if (empty($this->cfg[$cat])) {
                unset($this->cfg[$cat]);
            }
        }
    }
}
