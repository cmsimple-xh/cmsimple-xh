<?php

/**
 * @version $Id$
 */

if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/* utf-8 marker: äöü */
require_once $pth['folder']['plugin_classes'] . 'filebrowser_view.php';
require_once $pth['folder']['plugin_classes'] . 'filebrowser.php';
?>