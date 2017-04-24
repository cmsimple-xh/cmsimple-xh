<?php
/**
 * The page data facade.
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
 * Handles all the data that has to be collected
 * to generate the page-data-array.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Martin Damken <kontakt@zeichenkombinat.de>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class PageDataRouter
{
    /**
     * The model.
     *
     * @var object
     */
    private $model;

    /**
     * The currently registered interests.
     *
     * @var array
     */
    private $currentInterests = array();

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
        $this->model = new PageDataModel($h, $pageDataFields, $tempData, $pageData);
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
        return $this->model->storedFields();
    }

    /**
     * Returns the currently registered interests.
     *
     * Must not be called before all plugins have been loaded.
     *
     * @return array
     *
     * @since 1.6
     */
    public function getCurrentInterests()
    {
        return $this->currentInterests;
    }

    /**
     * Registers a field for the page data.
     *
     * @param string $field The name of the page data field.
     *
     * @return void
     */
// @codingStandardsIgnoreStart
    public function add_interest($field)
    {
// @codingStandardsIgnoreEnd
        if (!in_array($field, $this->model->params)) {
            $this->model->addParam($field);
        }
        $this->currentInterests[] = $field;
    }

    /**
     * Unregisters a field for the page data. To permanently remove the field,
     * one has to call {@link XH_saveContents()} subsequently.
     *
     * @param string $field A page data field name.
     *
     * @return void
     *
     * @since 1.6
     */
    public function removeInterest($field)
    {
        $this->model->removeParam($field);
        $n = array_search($field, $this->currentInterests);
        if ($n !== false) {
            array_splice($this->currentInterests, $n, 1);
        }
    }

    /**
     * Registers a page data tab.
     *
     * @param string $tab_name The title of the tab.
     * @param string $tab_view The filename of the view.
     * @param string $cssClass A CSS class name.
     *
     * @return void
     */
// @codingStandardsIgnoreStart
    public function add_tab($tab_name, $tab_view, $cssClass = null)
    {
// @codingStandardsIgnoreEnd
        $this->model->addTab($tab_name, $tab_view, $cssClass);
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
        return $this->model->refresh($data);
    }

    /**
     * Returns the page data of a single page.
     *
     * @param int $id The page index.
     *
     * @return array
     */
// @codingStandardsIgnoreStart
    public function find_page($id)
    {
// @codingStandardsIgnoreEnd
        $page = $this->model->findKey($id);
        return $page;
    }

    /**
     * Returns the page data of all pages.
     *
     * @return array
     */
// @codingStandardsIgnoreStart
    public function find_all()
    {
// @codingStandardsIgnoreEnd
        return $this->model->data;
    }

    /**
     * Returns the page data for a new page, without actually creating the page.
     *
     * @param array $params Default data of the page.
     *
     * @return array
     */
// @codingStandardsIgnoreStart
    public function new_page(array $params = array())
    {
// @codingStandardsIgnoreEnd
        $page = $this->model->create($params);
        return $page;
    }

    /**
     * Appends a new page.
     *
     * @param array $params Default data of the page.
     *
     * @return void
     *
     * @since 1.6
     */
    public function appendNewPage(array $params = array())
    {
        $pageData = $this->model->create($params);
        $this->model->appendPage($pageData);
    }

    /**
     * Replaces the page data of a single page. Returns whether that succeeded.
     *
     * @param array $pages The new page data.
     * @param int   $index The index of the page.
     *
     * @return bool
     */
    public function insert(array $pages, $index)
    {
        return $this->model->replace($pages, $index);
    }

    /**
     * Deletes the page data of a single page. Returns whether that succeeded.
     *
     * @param int $key The index of the page.
     *
     * @return bool
     */
    public function destroy($key)
    {
        return $this->model->delete($key);
    }

    /**
     * Returns an array of all pages containing $value in $field.
     *
     * If $separator is given the $field will be translated to an array
     *  - explode($separator, $value) - before the search.
     *
     * @param string $field     The name of the field.
     * @param string $value     The value to look for.
     * @param string $separator The list item separator.
     *
     * @return array
     */
// @codingStandardsIgnoreStart
    public function find_field_value($field, $value, $separator = null)
    {
// @codingStandardsIgnoreEnd
        if ($separator) {
            $results = $this->model->findArrayfieldValue($field, $value, $separator);
            return $results;
        }
        $results = $this->model->findFieldValue($field, $value);
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
// @codingStandardsIgnoreStart
    public function find_field_value_sortkey($field, $value, $sortKey,
        $sortFlag = null, $sep = null
    ) {
// @codingStandardsIgnoreEnd
        $results = $this->model->findFieldValueSortkey($field, $value, $sortKey, $sortFlag, $sep);
        return $results;
    }

    /**
     * Updates the page data according to changes from the online editor.
     * Returns whether that succeeded.
     *
     * @param array $headings The page headings contained in the current edit.
     * @param int   $index    The page index.
     *
     * @return bool
     */
// @codingStandardsIgnoreStart
    public function refresh_from_texteditor(array $headings, $index)
    {
// @codingStandardsIgnoreEnd
        if (count($headings) == 0) {
            /*
             * Current page has been deleted:
             * Store it temporary, maybe the user
             * wants to paste it in somewhere again,
             * and remove it from the page infos
             */
            $this->keep_in_mind($index);
            return $this->destroy($index);
        } elseif (count($headings) > 1) {
            /*
             * At least one page was inserted:
             * Create an array of the new pages
             * and insert it into the page data
             */
            $new_pages = array();
            $current_page = $this->find_page($index);
            foreach ($headings as $heading) {
                $url = trim(xh_rmws(strip_tags($heading)));
                $url = uenc($url);

                switch ($url) {
                    case $current_page['url']:
                        /*
                         * Keeping the current page data:
                         * this attempt fails, if NEW pages are
                         * added AND current heading was CHANGED
                         */
                        foreach ($current_page as $field => $value) {
                            $params[$field] = $value;
                        }
                        break;
                    case $this->model->temp_data['url']:
                        /*
                         * This is the 'url' of the recently deleted
                         * page. Most probably it was just pasted in
                         * again. So don't be shy, get the old infos
                         * for this new page
                         */
                        foreach ($this->model->temp_data as $field => $value) {
                            $params[$field] = $value;
                        }
                        break;
                    default:
                        /*
                         * The 'url' is used for ... look right above
                         */
                        $params['url'] = $url;
                        break;
                }
                $params['last_edit'] = time();
                $new_pages[] = $params;
                $params = array();
            }
            return $this->model->replace($new_pages, $index);
        } elseif (count($headings) == 1) {
            /*
             * The heading may have changed, stay up to date.
             */
            $url = trim(xh_rmws(strip_tags($headings[0])));
            $params['url'] = uenc($url);
            $params['last_edit'] = time();
            return $this->update($index, $params);
        }
    }

    /**
     * Updates the page data of a single page and returns whether that succeeded.
     *
     * @param int   $s      The index of the page.
     * @param array $params The dictionary of fields to update.
     *
     * @return bool
     */
    public function update($s, array $params)
    {
        $update_params = array();
        foreach ($params as $field => $update) {
            if (in_array($field, $this->model->params)) {
                $update_params[$field] = $update;
            }
        }
        return $this->model->updateKey($s, $params);
    }

    /**
     * Returns the page data tab views.
     *
     * @param int $s The index of the page.
     *
     * @global bool
     * @global string
     * @global string
     * @global string
     * @global object The publisher.
     *
     * @return string HTML
     */
// @codingStandardsIgnoreStart
    public function create_tabs($s)
    {
// @codingStandardsIgnoreElse
        global $edit, $f, $o, $su, $xh_publisher;

        if (is_array($this->model->tabs)
            && count($this->model->tabs) > 0 && $edit
        ) {
            if ($s == -1 && !$f && $o == '' && $su == '') { // Argh! :(
                $pd_s = $xh_publisher->getFirstPublishedPage();
            } else {
                $pd_s = $s;
            }
            $page = $this->find_page($pd_s);
            if ($pd_s > -1) {
                $view_provider = new PageDataView($page, $this->model->tabs);
                return $view_provider->tabs() . $view_provider->views();
            }
        }
        return '';
    }

    /**
     * Stores page data in the recycle bin.
     *
     * @param int $pd_s The index of the page.
     *
     * @return void
     */
// @codingStandardsIgnoreStart
    private function keep_in_mind($pd_s)
    {
// @codingStandardsIgnoreEnd
        $page = $this->find_page($pd_s);
        $this->model->storeTemp($page);
    }

    /**
     * Returns the global page data arrays as a PHP tag.
     *
     * @return string The PHP tag.
     *
     * @since 1.6
     */
    public function headAsPHP()
    {
        $flds = array();
        foreach ($this->model->params as $param) {
            $flds[] = "'" . addcslashes($param, '\'\\') . "'";
        }
        $o = "<?php\n\$page_data_fields=array("
            . implode(',', $flds)
            . ");\n";
        $flds = array();
        foreach ($this->model->temp_data as $key => $val) {
            $escval = addcslashes($val, '\'\\');
            $flds[] = "'$key'=>'$escval'";
        }
        $o .= "\$temp_data=array(\n"
            . implode(",\n", $flds)
            . "\n);\n?>\n";
        return $o;
    }

    /**
     * Returns the page data of a single page as PHP tag.
     *
     * @param int $id The index of the page.
     *
     * @return string The PHP tag.
     *
     * @since 1.6
     */
    public function pageAsPHP($id)
    {
        $data = $this->find_page($id);
        $flds = array();
        foreach ($data as $key => $val) {
            $escval = addcslashes($val, '\'\\');
            $flds[] = "'$key'=>'$escval'";
        }
        $o = "<?php\n\$page_data[]=array(\n"
            . implode(",\n", $flds)
            . "\n);\n?>\n";
        return $o;
    }
}
