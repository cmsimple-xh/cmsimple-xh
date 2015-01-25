<?php
/**
 * tinyMCE Editor - admin module
 *
 * Handles reading and writing of plugin files - init selectors are dynamically loaded from
 * ./inits/ representation of init_.js files
 *
 * PHP version 4 and 5
 *
 * @package tinymce
 * @copyright	1999-2009 Peter Harteg
 * @copyright	2009-2012 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license	http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version	@CMSIMPLE_XH_VERSION@, @CMSIMPLE_XH_BUILD@
 * @version $Id$
 * @link	http://cmsimple-xh.org/
 * @since      File available since Release 1.6.0
 * @author     manu <http://www.pixolution.ch/>
 *
 */
/* utf-8 marker: äöü */

if (!XH_ADM) {     return; }

/*
 * Register the plugin type.
 */
if (function_exists('XH_registerPluginType')) {
    XH_registerPluginType  ('editor', $plugin);
}

/*
 * Register the plugin menu items.
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(false);
}

initvar('tinymce');

if ($tinymce) {
    if (!class_exists('XH_CSRFProtection')) {
        $o.= XH_message('fail','needs CMSimple_XH Version 1.6 or higher!');
        return;
    }

    //Helper-functions
    function tinymce_getInits() {
        global $pth;
        $inits = glob($pth['folder']['plugins'] . 'tinymce/inits/*.js');
        
        $options = array();
        foreach ($inits as $init) {
            $temp = explode('_', basename($init, '.js'));
            if (isset($temp[1])) {
                $options[] = $temp[1];
            }
        }
        return $options;
    }

    initvar('admin');
    initvar('action');

    $plugin = basename(dirname(__FILE__), "/");
    
    if ($admin == 'plugin_config' || $admin == 'plugin_language') {
        $o .= print_plugin_admin('on');
    } else {
        $o .= print_plugin_admin('off');
    }
    
    $o .= plugin_admin_common($action, $admin, $plugin);

        
    if ($admin == '' || $admin == 'plugin_main') {
        $o .= '<script type="text/javascript" src="' . $pth['folder']['plugins'] . 'tinymce/tiny_mce/tiny_mce.js"></script>';
        $tinymce_version = '<script type="text/javascript">document.write(tinymce.majorVersion + " (revision " + tinymce.minorVersion + ")");</script>';
    
        $o .= '<h1>TinyMCE for CMSimple_XH</h1>';
        $o .= '<p>Version for @CMSIMPLE_XH_VERSION@</p>';
        $o .= '<p>TinyMCE version '. $tinymce_version . ' &ndash; <a href="http://www.tinymce.com/" target="_blank">http://www.tinymce.com/</a>';
        $o .= tag('br');
        $o .= 'Available language packs: cs, da, de, en, et, fr, it, nl, pl, ru, sk tw, zh.</p>';
        $o .= '<p>CMSimple_XH & Filebrowser integration';
        $o .= tag('br');
        $o .= 'up to version 1.5.6 &ndash; <a href="http://www.zeichenkombinat.de/" target="_blank">Zeichenkombinat.de</a>';
        $o .= tag('br');
        $o .= 'from &nbsp;version 1.5.7 &ndash; <a href="http://www.pixolution.ch/" target="_blank">pixolution.ch</a></p>';

    }
}
/*
 * EOF tinymce/admin.php
 */
