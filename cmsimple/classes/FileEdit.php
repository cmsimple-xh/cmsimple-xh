<?php

/**
 * Classes for online editing of text and config files.
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
 * The abstract base class for editing of text and config files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
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
        return XH_writeFile($this->filename, $this->asString());
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
