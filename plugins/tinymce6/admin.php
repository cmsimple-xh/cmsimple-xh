<?php
/**
 * tinyMCE6 Editor - admin module
 *
 * PHP version >= 7
 *
 * @category CMSimple_XH
 *
 * @package   Tinymce6
 * @author    manu <info@pixolution.ch>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2023 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 * @since     File available since Release 1.6.0
 */
/* utf-8 marker: äöü */

if (!XH_ADM ) {
    return;
}

/**
  * handle API calls
  *
  */
if (isset($_GET['tinymce6']) && $_GET['tinymce6'] == 'imageuploader') {
    tinymce6_imageUploader();
    exit;
}

/*
 * Register the plugin type.
 */
if (function_exists('XH_registerPluginType')) {
    XH_registerPluginType('editor', $plugin);
}

/*
 * Register the plugin menu items.
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(false);
}

if (XH_wantsPluginAdministration($plugin)) {
    
    //Helper-functions

    /**
     * Returns all available init option.
     *
     * @return array options
     */
    function tinymce_getInits() 
    {
        global $action, $plugin, $pth;
        $inits = glob($pth['folder']['plugins'] . $plugin.'/inits/*.json');
        
        $options = array();
        foreach ($inits as $init) {
            $temp = explode('_', basename($init, '.json'));
            if (isset($temp[1])) {
                $options[] = $temp[1];
            }
        }
        return $options;
    }

/*
 * Register the plugin menu items.
 */

    if (XH_wantsPluginAdministration($plugin)) {
        $o .= print_plugin_admin('on');
        switch ($admin) {
        case '':
        case 'plugin_main':           
            $tiny_src = $plugin_cf[$plugin]['CDN'] == true ?
                $plugin_cf[$plugin]['CDN_src'] :
                $pth['folder']['plugins'] .  $plugin.'/' . 'tinymce/tinymce.min.js';
                
            $o .= '<script src="' . $tiny_src . '"></script>'.PHP_EOL;
            
            $tinymce_version 
                = '<script>
                    if (typeof(tinymce) === "undefined" || tinymce === null) {
                        alert("tinyMCE not present! Either offline or local library missing.") 
                    }
                    else {
                        document.write(tinymce.majorVersion + " (revision " 
                        + tinymce.minorVersion + ")");
                            
                    }
                </script>';
        
            $o .= '<h1>TinyMCE6 for CMSimple_XH</h1>'.PHP_EOL;
            $o .= '<p>TinyMCE ';
            $o .= $plugin_cf['tinymce6']['CDN'] == true ? 'Content delivery network (CDN) ': '';
            $o .= 'version ' . $tinymce_version . 
                '  &ndash; <a href="https://www.tiny.cloud/beta/" ' . 
                'target="_blank">www.tiny.cloud/</a><br>'.PHP_EOL;
            $o .= 'Available language packs: cs, da, de, en, et, fr, it, nl, pl, ';
            $o .= 'ru, sk tw, zh.</p>'.PHP_EOL;
            $o .= '<p>CMSimple_XH<br>'.PHP_EOL;
            $o .= 'up to version 1.5.6 &ndash; <a href="http://www.zeichenkombinat';
            $o .= '.de/" target="_blank">Zeichenkombinat.de</a><br>'.PHP_EOL;
            $o .= 'from &nbsp;version 1.5.7 &ndash; <a href="http://www.pixolution';
            $o .= '.ch/" target="_blank">pixolution.ch</a></p>'.PHP_EOL;
            $o .= '<h2>Credits</h2>'.PHP_EOL;
            $o .= '<p>Font Awesome Plugin made by 
                <a href="https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team" 
                target="_blank">Christoph M.
    Becker</a><br>'.PHP_EOL;
            $o .= 'xhPluginCall Plugin made by 
                <a href="http://www.cmsimple.sk/?Impressum" target="_blank">Tata</a>
                and <a href="https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team" 
                target="_blank">manu</a><br>'.PHP_EOL;
            $o .= 'init script inspired by 
                <a href="https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team" 
                target="_blank">cmb69\'s</a> codeeditor\'s init.<br>'.PHP_EOL;
            $o .= 'imageUpload script inspired by 
                <a href="https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team" 
                target="_blank">cmb69\'s</a> filebrowser upload.</p>'.PHP_EOL;
            break;
        default:
            $o .= plugin_admin_common();
        }
        return;
    }
}
    
/**
 * Handles the imageUploader.
 *
 * @return void
 *
 * @global array                  the config array.
 */
function tinymce6_imageUploader()
{
    global $cf;
    
    $imgUploader = new Tinymce6\Uploader();
    $imgUploader->setBrowseBase(CMSIMPLE_BASE);
    $imgUploader->setMaxFileSize('images', $cf['images']['maxsize']);

    $imgUploader->linkType = 'images';

    $imgUploader->baseDirectory
        = $imgUploader->baseDirectories['userfiles'];
    $imgUploader->currentDirectory
        = $imgUploader->baseDirectories['images'];

    if (isset($_GET['subdir'])) {
        $subdir = str_replace(
            array('../', './', '?', '<', '>', ':'), '', $_GET['subdir']
        );

        if (strpos($subdir, $imgUploader->baseDirectory) === 0) {
            $imgUploader->currentDirectory = rtrim($subdir, '/') . '/';
        }
    }
    $imgUploader->determineCurrentType();

    reset ($_FILES);
    if ($imgUploader->uploadFile(current($_FILES))) {
         echo json_encode(array('location' => $imgUploader->fileWritten));
//         XH_logMessage( 'info', 'uploadFile', 'tinymce6', 'fileWritten: ' . $imgUploader->fileWritten);
      
    } else {
        foreach ($imgUploader->errMsg as $key => $val) {
            XH_logMessage( 'error', 'uploadFile', 'tinymce6', $key . ': ' . $val);
        }
        // Notify editor that the upload failed
        header("HTTP/1.0 500 Server Error, See XH logfile");
    }
}
/*
 * EOF tinymce6/admin.php
 */
