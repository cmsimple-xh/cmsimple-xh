<?php
/**
 * Page-Data - Module page_data_router
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: page_data_router.php 315 2012-10-31 00:09:01Z cmb69 $
 * @link      http://cmsimple-xh.org/
 */


/* utf8-marker = äöüß */


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
class PL_Page_Data_Router
{
    /**
     * The model.
     *
     * @var object
     *
     * @access protected
     */
    var $model;

    /**
     * The page data of the current page.
     *
     * @var array
     *
     * @access protected
     */
    var $current_page;

    /**
     * Constructs an instance.
     *
     * @param array $h              The page headings.
     * @param array $pageDataFields The page data fields.
     * @param array $tempData       The most recently deleted page data.
     * @param array $pageData       The page data.
     *
     * @return void
     *
     * @access public
     */
    function PL_Page_Data_Router($h, $pageDataFields, $tempData, $pageData)
    {
        $this->model = new PL_Page_Data_Model(
            $h, $pageDataFields, $tempData, $pageData
        );
    }

    /**
     * Registers a field for the page data.
     *
     * @param string $field The name of the page data field.
     *
     * @return void
     *
     * @access public
     */
    function add_interest($field)
    {
        if (!in_array($field, $this->model->params)) {
            $this->model->add_param($field);
        }
    }

    /**
     * Registers a page data tab.
     *
     * @param string $tab_name The title of the tab.
     * @param string $tab_view The filename of the view.
     *
     * @return void
     *
     * @access public
     */
    function add_tab($tab_name, $tab_view)
    {
        $this->model->add_tab($tab_name, $tab_view);
    }

    /**
     * Returns the page data of a single page.
     *
     * @param int $id The page index.
     *
     * @return array
     *
     * @access public
     */
    function find_page($id)
    {
        $page = $this->model->find_key($id);
        return $page;
    }

    /**
     * Returns the page data of all pages.
     *
     * @return array
     *
     * @access public
     */
    function find_all()
    {
        return $this->model->data;
    }

    /**
     * Adds a new page and returns its page data.
     *
     * @param array $params The page data of the page.
     *
     * @return array
     *
     * @access public
     */
    function new_page($params = null)
    {
        $page = $this->model->create($params);
        return $page;
    }

    /**
     * Replaces the page data of a single page.
     *
     * @param array $pages The new page data.
     * @param int   $index The index of the page.
     *
     * @return void
     *
     * @access public
     */
    function insert($pages, $index)
    {
        $this->model->replace($pages, $index);
    }

    /**
     * Deletes the page data of a single page.
     *
     * @param int $key The index of the page.
     *
     * @return void
     *
     * @access public
     */
    function destroy($key)
    {
        $this->model->delete($key);
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
     *
     * @access public
     */
    function find_field_value($field, $value, $separator = null)
    {
        if ($separator) {
            $results = $this->model->find_arrayfield_value(
                $field, $value, $separator
            );
            return $results;
        }
        $results = $this->model->find_field_value($field, $value);
        return $results;
    }

    /**
     * Returns the sorted page data of all pages,
     * which contain a value in a (list) field.
     *
     * @param string $field     The name of the field.
     * @param string $value     The value to look for.
     * @param string $sort_key  The name of the field to sort by.
     * @param int    $sort_flag The sort options as for array_multisort().
     * @param string $separator The list item separator.
     *
     * @return array
     *
     * @access public
     */
    function find_field_value_sortkey($field, $value, $sort_key,
        $sort_flag = null, $separator = null
    ) {
        $results = $this->model->find_field_value_sortkey(
            $field, $value, $sort_key, $sort_flag, $separator
        );
        return $results;
    }

    /**
     * Updates the page data according to changes from the online editor.
     *
     * @param array $headings The page headings contained in the current edit.
     * @param int   $index    The page index.
     *
     * @return void
     *
     * @access public
     */
    function refresh_from_texteditor($headings, $index)
    {
        if (count($headings) == 0) {
            /*
             * Current page has been deleted:
             * Store it temporary, maybe the user
             * wants to paste it in somewhere again,
             * and remove it from the page infos
             */
            $this->keep_in_mind($index);
            $this->destroy($index);
        }
        if (count($headings) > 1) {
            /*
             * At least one page was inserted:
             * Create an array of the new pages
             * and insert it into the page data
             */
            $new_pages = array();
            $current_page = $this->find_page($index);
            foreach ($headings as $key => $heading) {
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
            $this->model->replace($new_pages, $index);
        }
        if (count($headings) == 1) {
            /*
             * The heading may have changed, stay up to date.
             */
            $url = trim(xh_rmws(strip_tags($headings[0])));
            $params['url'] = uenc($url);
            $params['last_edit'] = time();
            $this->update($index, $params);
        }
    }

    /**
     * Updates the page data according to changes from the menumanager plugin.
     *
     * @param string $changes The changed page structure.
     *
     * @return void
     *
     * @access public
     *
     * @todo Remove sometimes in the future.
     */
    function refresh_from_menu_manager($changes)
    {
        $changes = explode(',', $changes);
        /*
         * Create an up-to-date page data array ...
         */
        $new_data = array();
        /*
         * index counter is needed for changed headings
         */
        $i = 0;
        foreach ($changes as $temp) {
            $infos = explode('^', $temp);
            $old_position = $infos[0];
            if ($old_position == 'New') {
                /*
                 * Page was added: create a new record
                 * These informations are created by default
                 */
                $params = array();
                $title = trim(strip_tags($infos[2]));
                $url = uenc($title);
                $params['url'] = $url;
                $new_data[] = $this->new_page($params);
            } else {
                /*
                 * Get the old record
                 */
                $new_data[] = $this->find_page($old_position);
            }
            if (isset($infos[3])) {
                /*
                 * if the heading has changed:
                 * update 'url'
                 */
                $url = uenc(trim(strip_tags($infos[3])));
                $new_data[$i]['url'] = $url;
            }
            $i++;
        }
        /*
         * Replace the old data with the new array
         */
        $this->model->refresh($new_data);
    }

    /**
     * Updates the page data of a single page.
     *
     * @param int   $s      The index of the page.
     * @param array $params The dictionary of fields to update.
     *
     * @return void
     *
     * @access public
     */
    function update($s, $params)
    {
        $update_params = array();
        foreach ($params as $field => $update) {
            if (in_array($field, $this->model->params)) {
                $update_params[$field] = $update;
            }
        }
        $this->model->update_key($s, $params);
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
     *
     * @return string  The (X)HTML.
     *
     * @access public
     */
    function create_tabs($s)
    {
        global $edit, $f, $o, $su;

        if (is_array($this->model->tabs)
            && count($this->model->tabs) > 0 && $edit
        ) {
            if ($s == -1 && !$f && $o == '' && $su == '') { // Argh! :(
                $pd_s = 0;
            } else {
                $pd_s = $s;
            }
            $page = $this->find_page($pd_s);
            if ($pd_s > -1) {
                $view_provider = new PL_Page_Data_View($page, $this->model->tabs);
                return $view_provider->pd_forms();
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
     *
     * @access protected
     */
    function keep_in_mind($pd_s)
    {
        $page = $this->find_page($pd_s);
        $this->model->store_temp($page);
    }

    /**
     * Returns the global page data arrays as a PHP tag.
     *
     * @return string The PHP tag.
     *
     * @since  1.6
     *
     * @access public
     */
    function headAsPHP()
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
     * @access public
     *
     * @since 1.6
     */
    function pageAsPHP($id)
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

?>
