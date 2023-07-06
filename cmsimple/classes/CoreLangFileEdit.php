<?php

namespace XH;

/**
 * Editing of core language files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
 */
class CoreLangFileEdit extends CoreArrayFileEdit
{
    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        global $sl, $cf, $tx;

        parent::__construct();
        $this->varName = 'tx';
        $this->params = array(
            'form' => 'array',
            'file' => 'language',
            'action' => 'save'
        );
        $this->redir = '?file=language&action=array&xh_success=language';
        $this->cfg = array();
        foreach ($tx as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
                // don't show or save the following
                if ($cat == 'meta' && $name =='codepage') {
                    continue;
                }
                $co = array('val' => $val, 'type' => 'text', 'isAdvanced' => false);
                if ($cat == 'subsite' && $name == 'template') {
                    if ($sl === $cf['language']['default']) {
                        $co['type'] = 'hidden';
                    } else {
                        $co['type'] = 'enum';
                        $co['vals'] = $this->selectOptions('templates', '/^([^\.]*)$/i');
                        array_unshift($co['vals'], '');
                    }
                }
                $this->cfg[$cat][$name] = $co;
            }
        }
    }
}
