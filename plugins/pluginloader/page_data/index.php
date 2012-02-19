<?php
/* utf8-marker = äöüß */
/**
 * Page-Data
 * Part of the Pluginloader V.2.1.x
 *
 * Generates an array with an element for each
 * page, generated in CMSimple. This allows to
 * store separate data for each page, which
 * can be handeled by plugins.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @version 1.0.03
 * @package pluginloader
 * @subpackage page_data
 */
define('PL_PAGE_DATA_FOLDER', $pth['folder']['plugins'] . $pluginloader_cfg['foldername_pluginloader'] . '/page_data/');
define('PL_PAGE_DATA_FILE', $pth['folder']['content'] . 'pagedata.php');
define('PL_PAGE_DATA_STYLESHEET', PL_PAGE_DATA_FOLDER . 'css/stylesheet.css');
define('PL_URI_SEPARATOR', $cf['uri']['seperator']);

require_once(PL_PAGE_DATA_FOLDER . 'page_data_router.php');
require_once(PL_PAGE_DATA_FOLDER . 'page_data_model.php');
require_once(PL_PAGE_DATA_FOLDER . 'page_data_views.php');

/**
 * Check if page-data-file exists, if not: try to
 * create a new one with basic data-fields.
 */
if (!file_exists(PL_PAGE_DATA_FILE)) {
    if ($fh = fopen(PL_PAGE_DATA_FILE, 'w')) {
        fwrite($fh, '<?php' . "\n" . '$page_data_fields[] = \'url\';' . "\n" . '$page_data_fields[] = \'last_edit\';' . "\n" . '?>');
        chmod(PL_PAGE_DATA_FILE, 0666);
        fclose($fh);
    } else {
        e('cntwriteto', 'file', PL_PAGE_DATA_FILE);
    }
}

/**
 * Create an instance of PL_Page_Data_Router
 */
$pd_router = new PL_Page_Data_Router(PL_PAGE_DATA_FILE, $h);

if ($adm) {

    /**
     * Check for any changes to handle
     * First: check for changes from texteditor
     */
    if ($function == 'save') {
        /**
         * Collect the headings and pass them over to the router
         */
        $text = preg_replace("/<h[1-" . $cf['menu']['levels'] . "][^>]*>(&nbsp;|&#160;|\xC2\xA0| )?<\/h[1-" . $cf['menu']['levels'] . "]>/isu", "", stsl($text));
        preg_match_all('/<h[1-' . $cf['menu']['levels'] . '].*>(.+)<\/h[1-' . $cf['menu']['levels'] . ']>/isU', $text, $matches);
        $pd_router->refresh_from_texteditor($matches[1], $s);
    }

    /**
     * Second: check for hanges from MenuManager
     */
    if (isset($menumanager) && $menumanager && $action == 'saverearranged' && (isset($text) ? strlen($text) : 0 ) > 0) {
        $pd_router->refresh_from_menu_manager($text);
    }

    /**
     * Finally check for some changed page infos
     */
    if ($s > -1 && isset($_POST['save_page_data'])) {
        $params = $_POST;
        if (get_magic_quotes_gpc() === 1) {
            array_walk($params, create_function('&$data', '$data=stripslashes($data);'));
        }
        unset($params['save_page_data']);
        $pd_router->update($s, $params);
    }
}
/**
 * Now we are up to date
 * If no page has been selected yet, we
 * are on the start page: Get its index
 */
if ($s == -1 && !$f && $o == '' && $su == '') {
    $pd_s = 0;
} else {
    $pd_s = $s;
}

/**
 * Get the infos about the current page
 */
$pd_current = $pd_router->find_page($pd_s);
?>