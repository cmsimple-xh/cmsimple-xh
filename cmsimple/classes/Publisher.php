<?php

/**
 * Publishing and hiding of pages.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    Jerry Jakobsfeld <mail@simplesolutions.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

/**
 * Reliable information about published and hidden pages.
 *
 * Publisher respects `#cmsimple hide#` and `#cmsimple remove#` as well as the
 * page data fields `linked_to_menu`, `published`, `publication_date` and
 * `expires`. Note that unpublishing via `#cmsimple remove#` cannot be detected
 * by other means except for Publisher::isPublished(), because it is replaced
 * during content loading. Also note that all pages are published and none is
 * hidden in edit mode.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.7
 */
class Publisher
{
    /**
     * The publishing status of the pages.
     *
     * @var bool[]
     */
    private $published = array();

    /**
     * The hide status of the pages.
     *
     * @var bool[]
     */
    private $hidden = array();

    /**
     * The pages.
     *
     * @var Pages
     */
    private $pages;

    /**
     * Initializes a new instance.
     *
     * @param bool[] $removed The removed status of the pages.
     */
    public function __construct(array $removed)
    {
        global $pd_router, $cl, $edit;

        $pd_router->add_interest('published');
        $pd_router->add_interest('publication_date');
        $pd_router->add_interest('expires');
        $pd_router->add_interest('linked_to_menu');
        if (XH_ADM && $edit) {
            $this->hidden = array_fill(0, $cl, false);
            $this->published = array_fill(0, $cl, true);
        } else {
            foreach ($pd_router->find_all() as $index => $data) {
                $this->hidden[$index] = $data['linked_to_menu'] == '0'
                    || !$removed[$index] && hide($index);
                $this->published[$index] = !$removed[$index]
                    && $this->isPublishedInPageData($data);
            }
        }
        $this->pages = new Pages;
    }

    /**
     * Returns whether a page is published.
     *
     * A page may be unpublished either by `#cmsimple remove#` or via
     * the page data fields `published`, `publication_date` and `expires`.
     * Note that all descendants of an unpublished page are also regarded as
     * being unpublished.
     *
     * @param int $index A page index.
     *
     * @return bool
     */
    public function isPublished($index)
    {
        $parent = $index;
        while (isset($parent) && $this->published[$parent]) {
            $parent = $this->pages->parent($parent, false);
        }
        if (isset($parent)) {
            return false;
        }
        return $this->published[$index];
    }

    /**
     * Returns whether a page is hidden.
     *
     * A page may be hidden either by `#cmsimple hide#` or via the page data
     * field `linked_to_menu`. Note that unpublished pages are not reported as
     * being hidden by this method. Note that all descendants of a hidden page
     * are also regarded as being hidden.
     *
     * @param int $index A page index.
     *
     * @return bool
     */
    public function isHidden($index)
    {
        $parent = $index;
        while (isset($parent) && !$this->hidden[$parent]) {
            $parent = $this->pages->parent($parent, false);
        }
        if (isset($parent)) {
            return true;
        }
        return $this->hidden[$index];
    }

    /**
     * Returns the index of the first published page.
     *
     * @return int -1 if no page is published
     */
    public function getFirstPublishedPage()
    {
        for ($i = 0; $i < $this->pages->getCount(); $i++) {
            if ($this->isPublished($i)) {
                return $i;
            }
        }
        return -1;
    }

    /**
     * Returns whether a page is hidden.
     *
     * @param array $data An array of page data.
     *
     * @return bool
     */
    private function isPublishedInPageData(array $data)
    {
        if ($data['published'] == '0') {
            return false;
        }
        $publication_date = isset($data['publication_date'])
            ? trim($data['publication_date'])
            : '';
        $expires = isset($data['expires']) ? trim($data['expires']) : '';
        if ($expires != '' || $publication_date != '') {
            $current = time();
            $maxInt = defined('PHP_INT_MAX') ? PHP_INT_MAX : 2147483647;
            $int_publication_date = ($publication_date != '')
                ? strtotime($publication_date) : 0;
            $int_expiration_date = ($expires != '')
                ? strtotime($expires) : $maxInt;
            if ($current <= $int_publication_date
                || $current >= $int_expiration_date
            ) {
                return false;
            }
        }
        return true;
    }
}
