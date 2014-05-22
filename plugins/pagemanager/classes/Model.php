<?php

/**
 * The model class of Pagemanager_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: Model.php 179 2014-01-28 13:24:08Z cmb $
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

/**
 * The model class of Pagemanager_XH.
 *
 * @category CMSimple_XH
 * @package  Pagemanager
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */
class Pagemanager_Model
{
    /**
     * The unmodified page headings.
     *
     * @var array
     */
    var $headings;

    /**
     * Whether the pages may be renamed.
     *
     * @var array
     */
    var $mayRename;

    /**
     * Returns whether a heading may be renamed.
     *
     * Renaming is only allow if the heading doesn't contain any markup,
     * besides the generally recognized XML entities.
     *
     * @param string $heading A page heading.
     *
     * @return string
     *
     * @since 2.0.1
     */
    function mayRename($heading)
    {
        return !preg_match('/<|&(?!(?:amp|quot|lt|gt);)/', $heading);
    }

    /**
     * Returns a cleaned heading.
     *
     * Trims, strips off all tags and decodes HTML entities in the heading.
     * For PHP 4 a simplified fallback is used which does not properly decode
     * the HTML entities, but rather replaces them with the Unicode substitution
     * character.
     *
     * @param string $heading A page heading.
     *
     * @return string
     *
     * @since 2.0.1
     */
    function cleanedHeading($heading)
    {
        $heading = trim(strip_tags($heading));
        if (version_compare(PHP_VERSION, '5', 'ge')) {
            $heading = html_entity_decode($heading, ENT_COMPAT, 'UTF-8');
            $heading = htmlspecialchars($heading, ENT_COMPAT, 'UTF-8');
        } else {
            $pattern = '/&(?!(?:amp|quot|lt|gt);).+;/';
            $heading = preg_replace($pattern, "\xEF\xBF\xBD", $heading);
        }
        return $heading;
    }

    /**
     * Initializes <var>$headings</var> and <var>$mayRename</var>.
     *
     * @return void
     *
     * @global array The content of the pages.
     * @global array The configuration of the core.
     * @global array The localization of the core.
     */
    function getHeadings()
    {
        global $c, $cf, $tx;

        $stop = $cf['menu']['levels'];
        $empty = 0;
        foreach ($c as $i => $page) {
            preg_match('~<h([1-' . $stop . ']).*?>(.*?)</h~isu', $page, $matches);
            $heading = $this->cleanedHeading($matches[2]);
            if ($heading === '') {
                $empty += 1;
                $this->headings[$i] = $tx['toc']['empty'] . ' ' . $empty;
            } else {
                $this->headings[$i] = $heading;
            }
            $this->mayRename[$i] = $this->mayRename($matches[2]);
        }
    }

    /**
     * Returns whether the page structure is irregular.
     *
     * @return  bool
     *
     * @global array The menu levels of the pages.
     * @global int   The number of pages.
     */
    function isIrregular()
    {
        global $l, $cl;

        $stack = array();
        for ($i = 1; $i < $cl; $i++) {
            $delta = $l[$i] - $l[$i - 1];
            if ($delta > 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the available themes.
     *
     * @return array
     *
     * @global array The paths of system files and folders.
     */
    function themes()
    {
        global $pth;

        $themes = array();
        $path = "{$pth['folder']['plugins']}pagemanager/jstree/themes/";
        $dir = opendir($path);
        if ($dir !== false) {
            while (($entry = readdir($dir)) !== false) {
                if ($entry[0] !== '.' && is_dir($path . $entry)) {
                    $themes[] = $entry;
                }
            }
        }
        natcasesort($themes);
        return $themes;
    }

    /**
     * Saves the content. Returns whether that succeeded.
     *
     * @param string $xml An XML document.
     *
     * @return bool
     *
     * @global array  The contents of the pages.
     * @global array  The paths of system files and folders.
     * @global array  The configuration of the core.
     * @global array  The configuration of the plugins.
     * @global object The page data router.
     */
    function save($xml)
    {
        global $c, $pth, $cf, $plugin_cf, $pd_router;

        include_once "{$pth['folder']['plugins']}pagemanager/classes/XMLParser.php";
        $parser = new Pagemanager_XMLParser(
            $c, (int) $cf['menu']['levels'],
            $plugin_cf['pagemanager']['pagedata_attribute']
        );
        $parser->parse($xml);
        $c = $parser->getContents();
        return $pd_router->model->refresh($parser->getPageData());
    }
}

?>
