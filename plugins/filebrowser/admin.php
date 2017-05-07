<?php

/**
 * Internal Filebrowser -- admin.php
 *
 * @category  CMSimple_XH
 * @package   Filebrowser
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

$_XH_filebrowser = new Filebrowser\Controller();
$_XH_filebrowser->setBrowseBase(CMSIMPLE_BASE);
$_XH_filebrowser->setBrowserPath($pth['folder']['plugins'] . 'filebrowser/');
$_XH_filebrowser->setMaxFileSize('images', $cf['images']['maxsize']);
$_XH_filebrowser->setMaxFileSize('downloads', $cf['downloads']['maxsize']);

if (XH_wantsPluginAdministration('filebrowser')) {
    $o .= print_plugin_admin('off');

    if (!$admin) {
        $admin = 'plugin_config';
    }
    if (!$action) {
        $action = 'plugin_edit';
    }

    $o .= plugin_admin_common($action, $admin, $plugin);
    return;
}

if (!$cf['filebrowser']['external']
    && ($images || $downloads || $userfiles || $media)
) {
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

    $bjs .= '<script type="text/javascript" src="' . $pth['folder']['plugins']
        . 'filebrowser/js/filebrowser.min.js"></script>';

    $temp = isset($_GET['subdir'])
        ? str_replace(array('..', '.'), '', $_GET['subdir'])
        : ltrim($pth['folder'][$f], './');

    $_XH_filebrowser->baseDirectory = ltrim($pth['folder']['userfiles'], './');
    if (strpos($temp, $_XH_filebrowser->baseDirectory) !== 0) {
        $temp = $_XH_filebrowser->baseDirectory;
    }

    $_XH_filebrowser->currentDirectory =  rtrim($temp, '/') . '/';
    $_XH_filebrowser->linkType = $f;
    $_XH_filebrowser->setLinkParams($f);
    $_XH_filebrowser->determineCurrentType();

    if (!empty($_SERVER['CONTENT_LENGTH']) && empty($_POST)) {
        $_XH_filebrowser->view->error(
            'error_file_too_big_php',
            array(ini_get('post_max_size'), 'post_max_size')
        );
    }
    if (isset($_POST['deleteFile']) && isset($_POST['filebrowser_file'])) {
        $_XH_csrfProtection->check();
        $_XH_filebrowser->deleteFile($_POST['filebrowser_file']);
    }
    if (isset($_POST['deleteFolder']) && isset($_POST['folder'])) {
        $_XH_csrfProtection->check();
        $_XH_filebrowser->deleteFolder($_POST['folder']);
    }
    if (isset($_POST['upload'])) {
        $_XH_csrfProtection->check();
        $_XH_filebrowser->uploadFile();
    }
    if (isset($_POST['createFolder'])) {
        $_XH_csrfProtection->check();
        $_XH_filebrowser->createFolder();
    }
    if (isset($_POST['renameFile'])) {
        $_XH_csrfProtection->check();
        $_XH_filebrowser->renameFile();
    }

    $_XH_filebrowser->readDirectory();

    $o .= $_XH_filebrowser->render('cmsbrowser');

    $f = 'filebrowser';
    $images = $downloads = $userfiles = $media = false;
}

if (isset($_GET['filebrowser']) && $_GET['filebrowser'] == 'editorbrowser') {
    Filebrowser_forEditor();
    exit;
}

/**
 * Handles the editorbrowser.
 *
 * @return void
 *
 * @global array                  The paths of system files and folders.
 * @global XH\CSRFProtection      The CSRF protector.
 * @global Filebrowser_Controller The filebrowser controller.
 */
function Filebrowser_forEditor()
{
    global $pth, $_XH_csrfProtection, $_XH_filebrowser;

    $_XH_filebrowser->setBrowseBase('./');

    if ($_GET['type'] === 'file') {
        $_GET['prefix'] = '?&amp;download=';
    }
    $type = null;
    if (isset($_GET['type'])) {
        $type = $_GET['type'];
        if ($type == 'image') {
            $type = 'images';
        } elseif ($type == 'file') {
            $type = 'downloads';
        }
    }

    if ($type && array_key_exists($type, $_XH_filebrowser->baseDirectories)) {
        $_XH_filebrowser->linkType = $type;

        $_XH_filebrowser->setLinkPrefix($_GET['prefix']);
        $_XH_filebrowser->linkType = $type;

        $src = $_GET;
        $src['type'] = $type;
        unset($src['subdir']);
        // the following is a simplyfied http_build_query()
        $dst = array();
        foreach ($src as $key => $val) {
            $dst[] = urlencode($key) . '=' . urlencode($val);
        }
        $dst = implode('&', $dst);
        $_XH_filebrowser->setlinkParams($dst);

        $_XH_filebrowser->baseDirectory
            = $_XH_filebrowser->baseDirectories['userfiles'];
        $_XH_filebrowser->currentDirectory
            = $_XH_filebrowser->baseDirectories[$type];

        if (isset($_GET['subdir'])) {
            $subdir = str_replace(array('../', './', '?', '<', '>', ':'), '', $_GET['subdir']);

            if (strpos($subdir, $_XH_filebrowser->baseDirectory) === 0) {
                $_XH_filebrowser->currentDirectory = rtrim($subdir, '/') . '/';
            }
        }
        $_XH_filebrowser->determineCurrentType();

        if (isset($_POST['upload'])) {
            $_XH_csrfProtection->check();
            $_XH_filebrowser->uploadFile();
        }
        if (isset($_POST['createFolder'])) {
            $_XH_csrfProtection->check();
            $_XH_filebrowser->createFolder();
        }

        $_XH_filebrowser->readDirectory();

        $jsFile = $pth['folder']['plugins'] . basename($_GET['editor'])
            . '/editorhook.php';
        if (!file_exists($jsFile)) {
            $jsFile = $pth['folder']['plugin'] . 'editorhooks/'
                . basename($_GET['editor']) . '/script.php';
        }

        $script = '';
        if (file_exists($jsFile)) {
            include $jsFile;
        }

        $_XH_filebrowser->view->partials['script'] = $script;
        $_XH_filebrowser->view->partials['test'] = '';
        header('Content-Type: text/html; charset=UTF-8');
        echo $_XH_filebrowser->render('editorbrowser');
    }
}
