<?php

/**
 * Internal Filebrowser -- admin.php
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Filebrowser
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

if (!XH_ADM || $cf['filebrowser']['external']) {
    return true;
}

initvar('filebrowser');

if ($filebrowser) {

    initvar('admin');
    initvar('action');

    $o .= print_plugin_admin('off');

    $o .= '<div class="plugintext">'
        . '<div class="plugineditcaption">Filebrowser for @CMSIMPLE_XH_VERSION@'
        . '</div>' . tag('hr');

    if (!$admin) {
        $admin = 'plugin_config';
    }
    if (!$action) {
        $action = 'plugin_edit';
    }

    $o .= plugin_admin_common($action, $admin, $plugin)
        . '</div>';
    return;
}

if (!($images || $downloads || $userfiles || $media)) {
    return true;
}

if ($images) {
    $f = 'images';
}
if ($downloads) {
    $f = 'downloads';
}
if ($userfiles) {
    $f = 'userfiles';
}
if ($media) {
    $f = 'media';
}

$browser = $_SESSION['xh_browser'];

/**
 * The path of the filebrowser plugin folder.
 */
define('XHFB_PATH', $pth['folder']['plugins'] . 'filebrowser/');

$hjs .= '<script type="text/javascript" src="' . XHFB_PATH . 'js/filebrowser.js">'
    . '</script>';

$subdir = isset($_GET['subdir'])
    ? str_replace(array('..', '.'), '', $_GET['subdir'])
    : ltrim($pth['folder'][$f], './');

$browser->baseDirectory = ltrim($pth['folder']['userfiles'], './');
if (strpos($subdir, $browser->baseDirectory) !== 0) {
    $subdir = $browser->baseDirectory;
}

$browser->currentDirectory =  rtrim($subdir, '/') . '/';
$browser->linkType = $f;
$browser->setLinkParams($f);

if (!empty($_SERVER['CONTENT_LENGTH']) && empty($_POST)) {
    $browser->view->error(
        'error_file_too_big_php', array(ini_get('post_max_size'), 'post_max_size')
    );
}
if (isset($_POST['deleteFile']) && isset($_POST['filebrowser_file'])) {
    $_XH_csrfProtection->check();
    $browser->deleteFile($_POST['filebrowser_file']);
}
if (isset($_POST['deleteFolder']) && isset($_POST['folder'])) {
    $_XH_csrfProtection->check();
    $browser->deleteFolder($_POST['folder']);
}
if (isset($_POST['upload'])) {
    $_XH_csrfProtection->check();
    $browser->uploadFile();
}
if (isset($_POST['createFolder'])) {
    $_XH_csrfProtection->check();
    $browser->createFolder();
}
if (isset($_POST['renameFile'])) {
    $_XH_csrfProtection->check();
    $browser->renameFile();
}

$browser->readDirectory();

$o .= $browser->render('cmsbrowser');

$f = 'filebrowser';
$images = $downloads = $userfiles = $media = false;

?>
