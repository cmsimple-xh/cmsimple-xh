<?php

/**
 * The JSON generators.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2015 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

namespace Pagemanager;

use XH\Pages;

/**
 * The JSON generators.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class JSONGenerator
{
    /**
     * The pagemanager model.
     *
     * @var Model
     */
    protected $model;

    /**
     * The pages object.
     *
     * @var Pages
     */
    protected $pages;

    /**
     * Initializes a new instance.
     *
     * @param Model $model A pagemanager model.
     * @param Pages $pages A pages object.
     */
    public function __construct(Model $model, Pages $pages)
    {
        $this->model = $model;
        $this->pages = $pages;
    }

    /**
     * Executes the generator.
     *
     * @return void
     */
    public function execute()
    {
        $this->model->getHeadings();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->getPagesData());
    }

    /**
     * Returns the page structure.
     *
     * @param int $parent The index of the parent page.
     *
     * @return array
     */
    protected function getPagesData($parent = null)
    {
        $res = array();
        $children = !isset($parent)
            ? $this->pages->toplevels(false)
            : $this->pages->children($parent, false);
        foreach ($children as $index) {
            $res[] = $this->getPageData($index);
        }
        return $res;
    }

    /**
     * Returns the data of a single page.
     *
     * @param int $index A page index.
     *
     * @return string
     *
     * @global array  The configuration of the plugins.
     * @global object The page data router.
     */
    protected function getPageData($index)
    {
        global $plugin_cf, $pd_router;

        $pdattr = $plugin_cf['pagemanager']['pagedata_attribute'];
        $pageData = $pd_router->find_page($index);

        $res = array(
            'data' => $this->model->headings[$index],
            'attr' => array(
                'id' => "pagemanager-$index",
                'title' => $this->model->headings[$index]
            ),
            'children' => $this->getPagesData($index)
        );
        if ($pdattr !== '') {
            if ($pageData[$pdattr] === '') {
                $res['attr']['data-pdattr'] = '1';
            } else {
                $res['attr']['data-pdattr'] = $pageData[$pdattr];
            }
        }
        if (!$this->model->mayRename[$index]) {
            $res['attr']['class'] = 'pagemanager-no-rename';
        }
        return $res;
    }
}

?>
