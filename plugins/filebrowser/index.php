<?php

/**
 * Internal Filebrowser -- index.php
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

if (!XH_ADM) {
    return true;
}

if (!isset($_SESSION)) {
    session_start();
}

$temp = trim($sn, "/") . '/';
$xh_fb = new XHFileBrowser();
$xh_fb->setBrowseBase(CMSIMPLE_BASE);
$xh_fb->setBrowserPath($pth['folder']['plugins'] . 'filebrowser/');
$xh_fb->setMaxFileSize('images', $cf['images']['maxsize']);
$xh_fb->setMaxFileSize('downloads', $cf['downloads']['maxsize']);

$_SESSION['xh_browser'] = $xh_fb;
$_SESSION['xh_session'] = session_id();

?>
