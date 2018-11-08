<?php

namespace XH;

/**
 * Initiates a page object. Its methods act as a wrapper
 * of the use of XH\Pages methods for better OOP access
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see     http://cmsimple-xh.org/
 * @since    1.8
 */

Class Page
{
    
    /**
     * Instance of XH\Pages.
     *
     * @var pages
     *
     */
    private $pages;

    /**
     * Index selected page.
     *
     * @var selected
     *
     */
    private $selected;

    /**
     * Constructs an instance.
     *
     * @param obj pages instance
     *
     * @param int $selected Intex of selected page
     *
     * @global int Index selected page.
     */
    public function __construct($pages,$selected = NULL)
    {
        global $s;
        
        $this->pages = $pages;

        $this->selected = isset($selected)? $selected: $s;
    }

    /**
     * Returns the page object of parent page.
     *
     * @return obj Object of the parent page.
     */
    public function getParent()
    {
        $parent = $this->pages->parent($this->selected);
        return new Page($this->pages,$parent);
    }

    /**
     * Returns the URL of the selected page.
     *
     * @return string URL of the selected page
     */
    public function getUrl()
    {
        return $this->pages->url($this->selected);
    }
}
