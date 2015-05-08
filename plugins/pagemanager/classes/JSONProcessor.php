<?php

/**
 * The JSON processors.
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

/**
 * The JSON processors.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class JSONProcessor
{
    /**
     * The original contents array.
     *
     * @var array
     */
    protected $contents;

    /**
     * The new contents array.
     *
     * @var array
     */
    protected $newContents;

    /**
     * The new page data array.
     *
     * @var array
     */
    protected $pageData;

    /**
     * The maximum nesting level.
     *
     * @var int
     */
    protected $levels;

    /**
     * The current nesting level.
     *
     * @var int
     */
    protected $level;

    /**
     * The current page id (number?).
     *
     * @var int
     */
    protected $id;

    /**
     * The current page heading.
     *
     * @var string
     */
    protected $title;

    /**
     * The name of the page data attribute.
     *
     * @var string
     */
    protected $pdattrName;

    /**
     * The current page data attribute.
     *
     * @var bool
     */
    protected $pdattr;

    /**
     * Whether the current page may be renamed.
     *
     * @var bool
     */
    protected $mayRename;

    /**
     * Initializes a newly created object.
     *
     * @param array  $contents   Page contents.
     * @param int    $levels     Maximum page level.
     * @param string $pdattrName Name of a page data attribute.
     */
    public function __construct($contents, $levels, $pdattrName)
    {
        $this->contents = $contents;
        $this->levels = $levels;
        $this->pdattrName = $pdattrName;
    }

    /**
     * Processes the given JSON.
     *
     * @param string $json JSON.
     *
     * @return void
     */
    public function process($json)
    {
        $this->level = 0;
        $this->newContents = array();
        $this->pageData = array();
        $this->processPages(json_decode($json, true));
    }

    /**
     * Processes an array of pages.
     *
     * @param array $pages An array of pages.
     *
     * @return void
     */
    protected function processPages($pages)
    {
        $this->level++;
        foreach ($pages as $page) {
            $this->processPage($page);
        }
        $this->level--;
    }

    /**
     * Processes a page.
     *
     * @param array $page An array of page related data.
     *
     * @return void
     */
    protected function processPage($page)
    {
        $pattern = '/(copy_)?pagemanager-([0-9]*)/';
        $this->id = empty($page['attr']['id'])
            ? null
            : (int) preg_replace($pattern, '$2', $page['attr']['id']);
        $this->title = htmlspecialchars(
            $page['attr']['title'], ENT_NOQUOTES, 'UTF-8'
        );
        $this->pdattr = isset($page['attr']['data-pdattr'])
            ? $page['attr']['data-pdattr'] : null;
        $this->mayRename = $page['attr']['class'] == '';

        if (isset($this->contents[$this->id])) {
            $this->appendExistingPageContent();
        } else {
            $this->appendNewPageContent();
        }
        $this->appendPageData();

        $this->processPages($page['children']);
    }

    /**
     * Appends an existing page to the content.
     *
     * @return void
     */
    protected function appendExistingPageContent()
    {
        $content = $this->contents[$this->id];
        if ($this->mayRename) {
            $content = $this->replaceHeading($content);
        }
        $this->newContents[] = $content;
    }

    /**
     * Replaces the heading of a page content.
     *
     * @param string $content A page content.
     *
     * @return string HTML
     */
    protected function replaceHeading($content)
    {
        $pattern = '/<h[1-' . $this->levels . ']([^>]*)>'
            . '((<[^>]*>)*)[^<]*((<[^>]*>)*)'
            . '<\/h[1-' . $this->levels . ']([^>]*)>/i';
        $replacement = '<h' . $this->level . '$1>${2}'
            . addcslashes($this->title, '$\\') . '$4'
            . '</h' . $this->level . '$6>';
        return preg_replace($pattern, $replacement, $content, 1);
    }

    /**
     * Appends a new page to the content.
     *
     * @return void
     */
    protected function appendNewPageContent()
    {
        $this->newContents[] = '<h' . $this->level . '>' . $this->title
            . '</h' . $this->level . '>';
    }

    /**
     * Appends a page's page data.
     *
     * @return void
     *
     * @global XH\PageDataRouter The page data router.
     */
    protected function appendPageData()
    {
        global $pd_router;

        if (isset($this->id)) {
            $pageData = $pd_router->find_page($this->id);
        } else {
            $pageData = $pd_router->new_page();
            $pageData['last_edit'] = time();
        }
        if ($this->mayRename) {
            $pageData['url'] = uenc($this->title);
        }
        if ($this->pdattrName !== '') {
            $pageData[$this->pdattrName] = $this->pdattr;
        }
        $this->pageData[] = $pageData;
    }

    /**
     * Returns the new contents array.
     *
     * @return array
     */
    public function getContents()
    {
        return $this->newContents;
    }

    /**
     * Returns the new page data array.
     *
     * @return array
     */
    public function getPageData()
    {
        return $this->pageData;
    }
}

?>
