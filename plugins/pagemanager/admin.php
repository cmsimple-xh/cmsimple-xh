<?php

/**
 * Administration of Pagemanager_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Pagemanager
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2011-2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: admin.php 179 2014-01-28 13:24:08Z cmb $
 * @link      http://3-magi.net/?CMSimple_XH/Pagemanager_XH
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Pagemanager controller.
 */
require_once $pth['folder']['plugin_classes'] . 'Controller.php';

/**
 * Pagemanager version.
 */
define('PAGEMANAGER_VERSION', '2.0.3');

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
$_Pagemanager = new Pagemanager_Controller();
$o .= $_Pagemanager->dispatch();

?>
