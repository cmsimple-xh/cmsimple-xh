<?php

/**
 * The links for the link checker.
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
 * The links for the link checker.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class Link
{
    /**
     * Unknown URI scheme.
     */
    const STATUS_UNKNOWN = 0;

    /**
     * A mailto: link.
     */
    const STATUS_MAILTO = -1;

    /**
     * General internal checking failure.
     */
    const STATUS_INTERNALFAIL = -2;

    /**
     * General external checking failure.
     */
    const STATUS_EXTERNALFAIL = -3;

    /**
     * Content for checking internal link couldn't be found.
     */
    const STATUS_CONTENT_NOT_FOUND = -4;

    /**
     * Linked file couldn't be found.
     */
    const STATUS_FILE_NOT_FOUND = -5;

    /**
     * Linked anchor (URI fragment) is missing.
     */
    const STATUS_ANCHOR_MISSING = -6;

    /**
     * The URL.
     *
     * @var string
     */
    private $url;

    /**
     * The link text.
     *
     * @var string The HTML.
     */
    private $text;

    /**
     * The link status.
     *
     * Either a HTTP status code, or one of the STATUS_* constants.
     *
     * @var int
     */
    private $status;

    /**
     * Initializes a new instance.
     *
     * @param string $url  A URL.
     * @param string $text A link text.
     */
    public function __construct($url, $text)
    {
        $this->url = $url;
        $this->text = $text;
    }

    /**
     * Returns the URL.
     *
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Returns the link text.
     *
     * @return string HTML
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns the link status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the link status.
     *
     * @param int $status A link status.
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
