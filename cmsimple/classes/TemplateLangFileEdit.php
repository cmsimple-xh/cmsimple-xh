<?php

namespace XH;

/**
 * Editing of core language files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 * @since     1.8
 */
class TemplateLangFileEdit extends TemplateArrayFileEdit
{
    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        global $tpl_tx, $tx;

        parent::__construct();
        $this->caption = utf8_ucfirst($tx['filetype']['template']) . ' '
            . utf8_ucfirst($tx['filetype']['language']);
        $this->varName = 'tpl_tx';
        $this->params = array(
            'form' => 'array',
            'file' => 'template_language',
            'action' => 'save'
        );
        $this->redir = '?file=template_language&action=array&xh_success=language';
        $this->cfg = array();
        foreach ($tpl_tx as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
                $co = array('val' => $val, 'type' => 'text', 'isAdvanced' => false);
                $this->cfg[$cat][$name] = $co;
            }
        }
    }
}
