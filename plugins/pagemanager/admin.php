<?php

/**
 * Administration of Pagemanager_XH.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2015 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

use Pagemanager\Controller;

/**
 * Pagemanager version.
 */
define('PAGEMANAGER_VERSION', '3.0dev1');

/**
 * Functional wrapper for Pagemananger_Model::themes().
 *
 * @return array
 *
 * @global object The pagemanager controller.
 */
function Pagemanager_themes()
{
    global $_Pagemanager;

    return $_Pagemanager->model->themes();
}

/*
 * Initialize the global controller object and handle requests.
 */
$_Pagemanager = new Controller();
$o .= $_Pagemanager->dispatch();

?>
