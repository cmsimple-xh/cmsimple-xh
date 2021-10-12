<?php

namespace XH;

/**
 * Editing of core text files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @copyright GNU GPLv3 http://www.gnu.org/licenses/gpl-3.0.en.html
 * @see       http://cmsimple-xh.org/
 * @since     1.6
 */
class CoreTextFileEdit extends TextFileEdit
{
    /**
     * Construct an instance.
     */
    public function __construct()
    {
        global $pth, $file, $tx;

        $this->filename = $pth['file'][$file];
        $this->caption = utf8_ucfirst($tx['filetype'][$file]);
        $this->params = array('file' => $file, 'action' => 'save');
        $this->redir = "?file=$file&action=edit&xh_success=$file";
        $this->textareaName = 'text';
        parent::__construct();
    }
}
