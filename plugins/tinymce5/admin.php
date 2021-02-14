<?php
/**
 * tinyMCE Editor - admin module
 *
 * Handles reading and writing of plugin files - init selectors are dynamically 
 * loaded from ./inits/ representation of init_.js files
 *
 * PHP version 4 and 5
 *
 * @category CMSimple_XH
 *
 * @package   Tinymce5
 * @author    manu <info@pixolution.ch>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 * @since     File available since Release 1.6.0
 */
/* utf-8 marker: äöü */

if (!XH_ADM ) {
    return;
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


if (XH_wantsPluginAdministration('tinymce5')) {
    if (!class_exists('XH_CSRFProtection')) {
        $o .= XH_message('fail', 'needs CMSimple_XH Version 1.6 or higher!');
        return;
    }

    //Helper-functions

    /**
     * Returns all available init option.
     *
     * @return array options
     */
    function tinymce_getInits() 
    {
        global $action, $pth;
        $inits = glob($pth['folder']['plugins'] . 'tinymce5/inits/*.js');
        
        $options = array();
        foreach ($inits as $init) {
            $temp = explode('_', basename($init, '.js'));
            if (isset($temp[1])) {
                $options[] = $temp[1];
            }
        }
        return $options;
    }

    $admin = isset($_POST['admin']) ? $_POST['admin'] : $admin = isset($_GET['admin']) ? $_GET['admin'] : '';

    $plugin = basename(dirname(__FILE__), "/");
    
    if ($admin == 'plugin_config' || $admin == 'plugin_language') {
        $o .= print_plugin_admin('on');
    } else {
        $o .= print_plugin_admin('off');
    }
    
    $o .= plugin_admin_common($action, $admin, $plugin);

        
    if ($admin == '' || $admin == 'plugin_main') {
        $tiny_src = $plugin_cf['tinymce5']['CDN'] == true ?
            $plugin_cf['tinymce5']['CDN_src'] :
            $pth['folder']['plugins'] .  'tinymce5/' . 'tinymce/tinymce.min.js';
            
        $o .= '<script type="text/javascript" src="' . $tiny_src . '"></script>';
        $tinymce_version 
            = '<script type="text/javascript">
                if (typeof(tinymce) === "undefined" || tinymce === null) {
                    alert("tinyMCE not present! Either offline or local library missing.") 
                }
                else {
                    document.write(tinymce.majorVersion + " (revision " 
                    + tinymce.minorVersion + ")");
                        
                }
            </script>';
    
        $o .= '<h1>TinyMCE5 for CMSimple_XH</h1>';
        $o .= '<p>Version for '.CMSIMPLE_XH_VERSION.'</p>';
        $o .= '<p>Plugin version '.XH_pluginVersion($plugin).'</p>';
        $o .= '<p>TinyMCE ';
        $o .= $plugin_cf['tinymce5']['CDN'] == true ? 'Content delivery network (CDN) ': '';
        $o .= 'version ' . $tinymce_version . 
            '  &ndash; <a href="https://www.tiny.cloud/beta/" ' . 
            'target="_blank">www.tiny.cloud/</a>';
        $o .= tag('br');
        $o .= 'Available language packs: cs, da, de, en, et, fr, it, nl, pl, ';
        $o .= 'ru, sk tw, zh.</p>';
        $o .= '<p>CMSimple_XH';
        $o .= tag('br');
        $o .= 'up to version 1.5.6 &ndash; <a href="http://www.zeichenkombinat';
        $o .= '.de/" target="_blank">Zeichenkombinat.de</a>';
        $o .= tag('br');
        $o .= 'from &nbsp;version 1.5.7 &ndash; <a href="http://www.pixolution';
        $o .= '.ch/" target="_blank">pixolution.ch</a></p>';
        $o .=tag('br');
    }
}

if (isset($_GET['tinydrive']) && $_GET['tinydrive'] == 'requesttoken') {
    requestJwtToken();
    exit;
}

use \Firebase\JWT\JWT;


function requestJwtToken(){
    global $plugin_cf, $pth;

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

    $privateKey = $plugin_cf['tinymce5']['TinyDrive_JWT_private_key'];
    $payload = array(
      "sub" => $plugin_cf['tinymce5']['TinyDrive_JWT_subId'],     // Unique user id string
      "name" => $plugin_cf['tinymce5']['TinyDrive_JWT_Name'],     // Full name of user
      "exp" => time() + 60 * 10 // 10 minutes expiration
    );

    try {
      while (ob_get_level()) ob_end_clean();
      $token = JWT::encode($payload, $privateKey, 'RS256');
      http_response_code(200);
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode(array("token" => $token));
      exit;
    } catch (Exception $e) {
      http_response_code(500);
      header('Content-Type: application/json');
      echo $e->getMessage();
    }
}

/*
 * EOF tinymce5/admin.php
 */
