<?php

/**
 * @version $Id$
 */

/* utf-8 marker: äöü */

if (!$adm || $cf['filebrowser']['external'] /*|| $backend_hooks['filebrowser']*/) {
    return true;
}

initvar('filebrowser');

if ($filebrowser) {

    initvar('admin');
    initvar('action');

    $o .= print_plugin_admin('off');

    $o .= '<div class="plugintext">'
        . '<div class="plugineditcaption">Filebrowser for $CMSIMPLE_XH_VERSION$'
        . '</div>' . tag('hr');

    if (!$admin) {
        $admin = 'plugin_config';
    }
    if (!$action) {
        $action = 'plugin_edit';
    }

    if ($admin == 'plugin_config' && $action == 'plugin_edit') {
    $o .= '<div><form method="post" action="' . $sn . '?&amp;' . $plugin . '">';
    $o .= '<p><a class="pl_tooltip" href="#" onclick="return false">
             <img class="helpicon" alt="help" src="' . $pth['folder']['plugins'] . 'pluginloader/css/help_icon.png" />
             <span>' . sprintf($plugin_tx[$plugin]['help'], $pth['folder']['plugins'] . $plugin . '/inits') . '</span></a></p>';
    $o .= '<table>
             <tr>
                 <td>' . $tx['title']['images'] . ':</td>
                 <td><input size="50" type="text" name="' . $pluginloader_cfg['form_namespace'] . 'extensions_images" value="' . $plugin_cf[$plugin]['extensions_images'] . '"></td>
              </tr>
              <tr>
                 <td>' . $tx['title']['downloads'] . ':</td>
                 <td><input size="50" type="text" name="' . $pluginloader_cfg['form_namespace'] . 'extensions_downloads" value="' . $plugin_cf[$plugin]['extensions_downloads'] . '"></td>
              </tr>
              <tr>
                 <td>' . $tx['title']['userfiles'] . ':</td>
                 <td><input size="50" type="text" name="' . $pluginloader_cfg['form_namespace'] . 'extensions_userfiles" value="' . $plugin_cf[$plugin]['extensions_userfiles'] . '"></td>
              </tr>
              <tr>
                 <td>' . $tx['title']['media'] . ':</td>
                 <td><input size="50" type="text" name="' . $pluginloader_cfg['form_namespace'] . 'extensions_media" value="' . $plugin_cf[$plugin]['extensions_media'] . '"></td>
              </tr>
              </table>
              '
            . tag('input type="hidden" name="admin" value="plugin_config"') . "\n"
            . tag('input type="hidden" name="action" value="plugin_save"') . "\n"
            . tag('input type="submit"  name="plugin_submit" value="' . $tx['action']['save'] . '"') . "\n"
            . '</form>
           </div>';
    } else {
        $o .= plugin_admin_common($action, $admin, $plugin);
    }

    $o .= '</div>';

    if ($action === 'plugin_save') {  // refresh
        include $pth['folder']['plugins'] . $plugin . '/config/config.php';
    }

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
define('XHFB_PATH', $pth['folder']['plugins'] . 'filebrowser/');
$hjs .= '<script type="text/javascript" src="' . XHFB_PATH . 'js/filebrowser.js"></script>';

$subdir = isset($_GET['subdir']) ? str_replace(array('..', '.'), '', $_GET['subdir']) : '';

if (strpos($subdir, $browser->baseDirectories[$f]) !== 0) {
    $subdir = $browser->baseDirectories[$f];
}

$browser->baseDirectory = $browser->baseDirectories[$f];
$browser->currentDirectory =  rtrim($subdir, '/') . '/';
$browser->linkType = $f;
$browser->setLinkParams($f);

if (!empty($_SERVER['CONTENT_LENGTH']) && empty($_POST)) {
    $browser->view->error(
        'error_file_too_big_php', array(ini_get('post_max_size'), 'post_max_size')
    );
}

if (isset($_POST['deleteFile']) && isset($_POST['filebrowser_file'])) {
    $browser->deleteFile($_POST['filebrowser_file']);
}
if (isset($_POST['deleteFolder']) && isset($_POST['folder'])) {
    $browser->deleteFolder($_POST['folder']);
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

$o .= $browser->render('cmsbrowser');

$f = 'filebrowser';
$images = $downloads = $userfiles = $media = false;
/*
 * EOF filebrowser/admin.php
 */