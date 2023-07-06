<?php

namespace XH;

/**
 * The abstract base class for editing of text files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
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
     */
    public function form()
    {
        global $sn, $tx, $title, $_XH_csrfProtection;

        $title = $this->caption;
        $action = isset($this->plugin) ? $sn . '?&amp;' . $this->plugin : $sn;
        $value = utf8_ucfirst($tx['action']['save']);
        if (isset($_GET['xh_success'])) {
            $filetype = utf8_ucfirst($tx['filetype'][$_GET['xh_success']]);
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
     */
    public function submit()
    {
        global $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        $this->text = $_POST[$this->textareaName];
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
