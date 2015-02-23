<?php

/**
 * Editing of core text files.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * Editing of core text files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_CoreTextFileEdit extends XH_TextFileEdit
{
    /**
     * Construct an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The requested special file.
     * @global array  The localization of the core.
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

?>
