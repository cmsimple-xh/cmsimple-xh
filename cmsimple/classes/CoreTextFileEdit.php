<?php

namespace XH;

/**
 * Editing of core text files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
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
