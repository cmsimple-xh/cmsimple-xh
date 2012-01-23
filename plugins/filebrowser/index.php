<?php
/* utf-8 marker: äöü */
if(!$adm) { return true;}

if(!isset($_SESSION)){session_start();}
 

$temp = trim($sn, "/") . '/';
$xh_fb = new XHFileBrowser();
$xh_fb->setBrowseBase(CMSIMPLE_BASE);
$xh_fb->setBrowserPath($pth['folder']['plugins'] . 'filebrowser/');
$xh_fb->setMaxFileSize('images', $cf['images']['maxsize']);
$xh_fb->setMaxFileSize('downloads', $cf['downloads']['maxsize']);


$_SESSION['xh_browser'] = $xh_fb;
$_SESSION['xh_session'] = session_id();

//if ($edit
//        
//        && file_exists($pth['folder']['plugins'] . 'filebrowser/editorhooks/' . $cf['editor']['external'] . '/index.php')
//) {
//
//    
//    $hjs .= '<script type="text/javascript">' . "\n";
//    $hjs .= include $pth['folder']['plugins'] . 'filebrowser/editorhooks/' . $cf['editor']['external'] . '/index.php';
//    $hjs .= "\n</script>\n";
//}


