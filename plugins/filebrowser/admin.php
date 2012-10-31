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
    $plugin = basename(dirname(__FILE__));
    $plugin = basename(dirname(__FILE__), "/");
    $o = '<div class="plugintext">';
    $o .= '<div class="plugineditcaption">Filebrowser for CMSimple_xh</div>';
    $o .= '<p>Version for $CMSIMPLE_XH_VERSION$</p>';



    $admin = isset($_POST['admin']) ? $_POST['admin'] : $admin = isset($_GET['admin']) ? $_GET['admin'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : $action = isset($_GET['action']) ? $_GET['action'] : '';
    $o .= plugin_admin_common($action, $admin, $plugin);

    if ($action === 'plugin_save') {  // refresh
        include $pth['folder']['plugins'] . $plugin . '/config/config.php';
    }




    $o .= '<div><form method="post" action="' . $sn . '?&amp;' . $plugin . '">';
    $o .= '<p><a class="pl_tooltip" href="#" onclick="return false">
             <img class="helpicon" alt="help" src="' . $pth['folder']['flags'] . 'help_icon.png" />
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
           </div>
          </div>';
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

if (isset($_SERVER['CONTENT_LENGTH']) && empty($_POST)) {
    //$browser->view->error('error_not_uploaded', utf8_ucfirst($tx['filetype']['file']));
    $browser->view->error('error_file_too_big',
                          array('?', ini_get('post_max_size')));
}

if (isset($_POST['deleteFile']) && isset($_POST['file'])) {
    $browser->deleteFile($_POST['file']);
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