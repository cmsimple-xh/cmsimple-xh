<?php

namespace XH;

/**
 * The abstract base class for editing of text and config files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
 */
abstract class FileEdit
{
    /**
     * Additional POST parameters.
     *
     * @var array
     */
    protected $params = array();

    /**
     * The name of the plugin.
     *
     * @var string
     */
    protected $plugin = null;

    /**
     * The caption of the form.
     *
     * @var string
     */
    protected $caption = null;

    /**
     * The name of the file to edit.
     *
     * @var string
     */
    protected $filename = null;

    /**
     * URL for redirecting after successful submission (PRG pattern).
     *
     * @var string
     */
    protected $redir = null;

    /**
     * Saves the file. Returns whether that succeeded.
     *
     * @return bool
     */
    protected function save()
    {
        return (bool) XH_writeFile($this->filename, $this->asString());
    }

    /**
     * Returns the form to edit the file contents.
     *
     * @return string HTML
     */
    abstract public function form();

    /**
     * Handles the form submission.
     *
     * If file could be successfully saved, triggers a redirect.
     * Otherwise writes error message to $e, and returns the edit form.
     *
     * @return mixed The HTML resp. void.
     */
    abstract public function submit();

    /**
     * Returns the the file contents as string for saving.
     *
     * @return string
     */
    abstract protected function asString();
}
