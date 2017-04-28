<?php

/**
 * The abstract base class for editing of text files.
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
 * The abstract base class for editing of text files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
abstract class TextFileEdit extends FileEdit
{
    /**
     * The name of the textarea.
     *
     * @var string
     */
    protected $textareaName = null;

    /**
     * The contents of the file.
     *
     * @var string
     */
    protected $text = null;

    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        $contents = XH_readFile($this->filename);
        if ($contents !== false) {
            $this->text = $contents;
        } else {
            e('cntopen', 'file', $this->filename);
            $this->text = '';
        }
    }

    /**
     * Returns the form to edit the file contents.
     *
     * @return string HTML
     *
     * @global string The script name.
     * @global array  The localization of the core.
     * @global string The title of the current page.
     * @global object The CSRF protection object.
     */
    public function form()
    {
        global $sn, $tx, $title, $_XH_csrfProtection;

        $title = $this->caption;
        $action = isset($this->plugin) ? $sn . '?&amp;' . $this->plugin : $sn;
        $value = utf8_ucfirst($tx['action']['save']);
        if (isset($_GET['xh_success'])) {
            $filetype = utf8_ucfirst($tx['filetype'][stsl($_GET['xh_success'])]);
            $message =  XH_message('success', $tx['message']['saved'], $filetype);
        } else {
            $message = '';
        }
        $button = '<input type="submit" class="submit" value="' . $value . '">';
        $o = '<h1>' . $this->caption . '</h1>' . $message
            . '<form action="' . $action . '" method="post">'
            . '<textarea rows="25" cols="80" name="' . $this->textareaName
            . '" class="xh_file_edit">'
            . XH_hsc($this->text)
            . '</textarea>';
        foreach ($this->params as $param => $value) {
            $o .= '<input type="hidden" name="' . $param . '" value="'
                . $value . '">';
        }
        $o .= $_XH_csrfProtection->tokenInput()
            . $button . '</form>';
        return $o;
    }

    /**
     * Handles the form submission.
     *
     * If file could be successfully saved, triggers a redirect.
     * Otherwise writes error message to $e, and returns the edit form.
     *
     * @return mixed
     *
     * @global object The CSRF protection object.
     */
    public function submit()
    {
        global $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        $this->text = stsl($_POST[$this->textareaName]);
        if ($this->save() !== false) {
            header('Location: ' . CMSIMPLE_URL . $this->redir, true, 303);
            XH_exit();
        } else {
            e('cntsave', 'file', $this->filename);
            return $this->form();
        }
    }

    /**
     * Returns the the file contents as string for saving.
     *
     * @return string
     */
    protected function asString()
    {
        return $this->text;
    }
}
