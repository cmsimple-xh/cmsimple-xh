<?php

/**
 * Internal Filebrowser -- editorbrowser.php
 *
 * This script is called directly, to display the editor's file browser
 * in a separate window.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Filebrowser
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/* utf-8 marker: äöü */

/**
 * The view class.
 */
require_once './classes/filebrowser_view.php';

/**
 * The model class.
 */
require_once './classes/filebrowser.php';

if (!isset($_SESSION)) {
    session_start();
}

$fb_access = false;
if ($_SESSION['xh_session'] === session_id()
    && isset($_COOKIE['status']) && $_COOKIE['status'] === 'adm'
) {
    $fb_access = true;
}
if ($fb_access === false) {
    die('Nope');
}

$base = './../../';
$browser = $_SESSION['xh_browser'];
$browser->setBrowseBase($base);

if ($_GET['type'] === 'file') {
    $_GET['prefix'] = '?&amp;download=';
}
$fb_type = null;
if (isset($_GET['type'])) {
    $fb_type = $_GET['type'];
    if ($fb_type == 'image') {
        $fb_type = 'images';
    } else if ($fb_type == 'file') {
        $fb_type = 'downloads';
    }
}

if ($fb_type && array_key_exists($fb_type, $browser->baseDirectories)) {
    $browser->linkType = $fb_type;

    $browser->setLinkPrefix($_GET['prefix']);
    $browser->linkType = $fb_type;

    $src = $_GET;
    $src['type'] = $fb_type;
    unset($src['subdir']);
    // the following is a simplyfied http_build_query()
    $dst = array();
    foreach ($src as $key => $val) {
        $dst[] = urlencode($key) . '=' . urlencode($val);
    }
    $dst = implode('&', $dst);
    $browser->setlinkParams($dst);

    $browser->baseDirectory = $browser->baseDirectories['userfiles'];
    $browser->currentDirectory = $browser->baseDirectories[$fb_type];

    if (isset($_GET['subdir'])) {
        $subdir = str_replace(
            array('../', './', '?', '<', '>', ':'), '', $_GET['subdir']
        );

        if (strpos($subdir, $browser->baseDirectory) === 0) {
            $browser->currentDirectory = rtrim($subdir, '/') . '/';
        }
    }

    if (isset($_POST['upload'])) {
        $browser->uploadFile();
    }
    if (isset($_POST['createFolder'])) {
        $browser->createFolder();
    }
    if (isset($_POST['renameFile'])) {
        $browser->renameFile();
    }

    $browser->readDirectory();

    $jsFile = './editorhooks/' . basename($_GET['editor']) . '/script.php';

    $script = '';
    if (file_exists($jsFile)) {
        include $jsFile;
    }
    $test = '';

    $browser->view->partials['script'] = $script;
    $browser->view->partials['test'] = $test;
    $browser->browserPath = '';
    header('Content-Type: text/html; charset=UTF-8');
    echo $browser->render('editorbrowser');
}

?>
