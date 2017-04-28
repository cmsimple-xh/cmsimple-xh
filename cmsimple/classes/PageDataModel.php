<?php

/**
 * The page data model.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

/**
 * Handles the page-data-array including reading and writing of the files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Martin Damken <kontakt@zeichenkombinat.de>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class PageDataModel
{
    /**
     * The page headings (a copy of $h).
     *
     * @var array
     */
    private $headings;

    /**
     * The list of page data fields.
     *
     * @var array
     */
    public $params;

    /**
     * The page data.
     *
     * @var array
     */
    public $data;

    /**
     * The page data of the latest deleted page (recycle bin).
     *
     * @var array
     */
    public $temp_data;

    /**
     * The filenames of the views of page data tabs.
     *
     * @var array
     */
    public $tabs;

    /**
     * Constructs an instance.
     *
     * @param array $h              The page headings.
     * @param array $pageDataFields The page data fields.
     * @param array $tempData       The most recently deleted page data.
     * @param array $pageData       The page data.
     */
    public function __construct(array $h, array $pageDataFields, array $tempData, array $pageData)
    {
        $this->headings = $h;
        $this->params = !empty($pageDataFields)
            ? $pageDataFields
            : array('url', 'last_edit');
        $this->temp_data = $tempData;
        $this->data = $pageData;
        $this->fixUp();
    }

    /**
     * Returns all fields that are stored in the page data.
     *
     * @return array
     *
     * @since 1.6
     */
    public function storedFields()
    {
        $fields = $this->params;
        $fields = array_merge($fields, array_keys($this->temp_data));
        foreach ($this->data as $page) {
            $fields = array_merge($fields, array_keys($page));
        }
        $fields = array_values(array_unique($fields));
        return $fields;
    }

    /**
     * Fixes the page data after reading.
     *
     * @return void
     *
     * @global int   The index of the current page.
     * @global array The page data of the current page.
     */
    private function fixUp()
    {
        global $pd_s, $pd_current;

        foreach ($this->headings as $id => $value) {
            foreach ($this->params as $param) {
                if (!isset($this->data[$id][$param])) {
                    switch ($param) {
                        case 'url':
                            $this->data[$id][$param] = uenc(strip_tags($value));
                            break;
                        default:
                            $this->data[$id][$param] = '';
                    }
                }
            }
        }
        if (isset($pd_current)) {
            $pd_current = $this->data[$pd_s];
        }
    }

    /**
     * Replaces the existing page data.
     *
     * @param array $data The new page data.
     *
     * @return bool Whether the page data have been refreshed.
     */
    public function refresh(array $data = null)
    {
        if (isset($data)) {
            $this->data = $data;
            return $this->save();
        }
        return false;
    }

    /**
     * Registers a page data field.
     *
     * @param string $field The page data field to add.
     *
     * @return void
     */
    public function addParam($field)
    {
        $this->params[] = $field;
        $this->fixUp();
    }

    /**
     * Removes a page data field.
     *
     * @param string $field A page data field to remove.
     *
     * @return void
     */
    public function removeParam($field)
    {
        $n = array_search($field, $this->params);
        array_splice($this->params, $n, 1);
        foreach (array_keys($this->headings) as $id) {
            unset($this->data[$id][$field]);
        }
        unset($this->temp_data[$field]);
    }

    /**
     * Registers a page data tab.
     *
     * @param string $title     The title of the tab.
     * @param string $view_file The filename of the view.
     * @param string $cssClass  A CSS class name.
     *
     * @return void
     */
    public function addTab($title, $view_file, $cssClass = null)
    {
        $this->tabs[$title] = array($view_file, $cssClass);
    }

    /**
     * Returns the page data of a single page.
     *
     * @param int $key The index of the page.
     *
     * @return array
     */
    public function findKey($key)
    {
        return $key >= 0 && $key < count($this->data)
            ? $this->data[$key] : null;
    }

    /**
     * Returns the page data of all pages which contain a value in a field.
     *
     * @param string $field The name of the field.
     * @param mixed  $value The value to look for.
     *
     * @return array
     */
    public function findFieldValue($field, $value)
    {
        $results = array();
        foreach ($this->data as $id => $page) {
            if (isset($page[$field])
                && strpos($page[$field], $value) !== false
            ) {
                $results[$id] = $page;
            }
        }
        return $results;
    }

    /**
     * Returns the page data of all pages which contain a value in a list field.
     *
     * @param string $field     The name of the field.
     * @param string $value     The value to look for.
     * @param string $separator The list item separator.
     *
     * @return array
     */
    public function findArrayfieldValue($field, $value, $separator)
    {
        $results = array();
        foreach ($this->data as $id => $page) {
            $array = explode($separator, $page[$field]);

            foreach ($array as $page_data) {
                if ($value == trim($page_data)) {
                    $results[$id] = $page;
                }
            }
        }
        return $results;
    }

    /**
     * Returns the sorted page data of all pages,
     * which contain a value in a (list) field.
     *
     * @param string $field    The name of the field.
     * @param string $value    The value to look for.
     * @param string $sortKey  The name of the field to sort by.
     * @param int    $sortFlag The sort options as for array_multisort().
     * @param string $sep      The list item separator.
     *
     * @return array
     */
    public function findFieldValueSortkey($field, $value, $sortKey, $sortFlag, $sep)
    {
        if ($sep) {
            $results = $this->findArrayfieldValue($field, $value, $sep);
        } else {
            $results = $this->findFieldValue($field, $value);
        }
        $temp = array();
        $ids = array();
        foreach ($results as $key => $value) {
            $temp[] = $value[$sortKey];
            $ids[] = $key;
        }
        array_multisort($temp, $sortFlag, $ids);
        $results = array();
        if (is_array($ids) && count($ids) > 0) {
            foreach ($ids as $id) {
                $results[$id] = $this->data[$id];
            }
        }
        return $results;
    }

    /**
     * Returns the page data for a new page, without actually creating the page.
     *
     * @param array $params Default page data.
     *
     * @return array
     */
    public function create(array $params = array())
    {
        $clean = array();
        foreach ($this->params as $field) {
            $clean[$field] = '';
        }
        $page = array_merge($clean, $params);
        return $page;
    }

    /**
     * Appends a new page.
     *
     * @param array $params Page data of the page.
     *
     * @return void
     *
     * @since 1.6
     */
    public function appendPage(array $params)
    {
        $this->data[] = $params;
    }

    /**
     * Replaces the page data of a single page. Returns whether that succeeded.
     *
     * @param array $pages The new page data.
     * @param int   $index The index of the page.
     *
     * @return bool
     */
    public function replace(array $pages, $index)
    {
        array_splice($this->data, $index, 1, $pages);
        return $this->save();
    }

    /**
     * Stores page data in the recycle bin.
     *
     * @param array $page The page data.
     *
     * @return void
     */
    public function storeTemp(array $page)
    {
        foreach ($page as $field => $value) {
            if (in_array($field, $this -> params)) {
                $this->temp_data[$field] = $value;
            }
        }
    }

    /**
     * Deletes the page data of a single page. Returns whether that succeeded.
     *
     * @param int $key The index of the page.
     *
     * @return bool
     */
    public function delete($key)
    {
        array_splice($this->data, $key, 1);
        return $this->save();
    }

    /**
     * Updates the page data of a single page and returns whether that succeeded.
     *
     * @param int   $key    The index of the page.
     * @param array $params The dictionary of fields to update.
     *
     * @return bool
     */
    public function updateKey($key, array $params)
    {
        foreach ($params as $field => $value) {
            $this->data[$key][$field] = $value;
        }
        return $this->save();
    }

    /**
     * Saves the page data and returns whether that succeeded.
     *
     * @return bool
     */
    private function save()
    {
        return XH_saveContents();
    }
}
