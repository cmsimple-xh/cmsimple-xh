<?php

Namespace Tinymce6;

/**
 * The plugin Editor Class.
 *
 * PHP version 8.2
 *
 * @category  CMSimple_XH
 * @package   TinyMCE5
 * @author    Emanuel Marinello <marinello@pixolution.ch>
 * @author    Christoph M. Becker <cmbecker69@gmx.de> (program structure)
 * @copyright 2011-2017 Christoph M. Becker <http://3-magi.net/>
 * @copyright 2021 CMSimple_XH <https://www.cmsimple-xh.org>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      https://github.com/cmsimple-xh/cmsimple-xh
 */

/**
 * The plugin controller.
 *
 * @category  CMSimple_XH
 * @package   TinyMCE5
 * @author    Emanuel Marinello <marinello@pixolution.ch>
 * @author    Christoph M. Becker <cmbecker69@gmx.de> (program structure)
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      https://github.com/cmsimple-xh/cmsimple-xh
 */
class Editor
{
    
    const PLUGIN = 'tinymce6';
    
    /**
     * Returns the configuration in JSON format.
     *
     * The configuration string can be `full', `medium', `minimal', `sidebar'
     * or `' (which will use the users default configuration).
     * Other values are taken as file name or as JSON configuration object.
     *
     * @param string $config    The configuration string.
     * @param string $selector  The editor area selector.
     *
     * @return string|false
     *
     * @global array  The paths of system files and folders.
     * @global string The selected language
     * @global array  The system configuration
     * @global string The prepared output
     * @global array  The configuration of the plugins.
     * @global array  The localization of the plugins.
     */
    protected static function config($config, $selector)
    {

        global $pth, $sl, $cf, $o, $plugin_cf, $plugin_tx;

        $pluginName = self::PLUGIN;
        $pcf = $plugin_cf[$pluginName];
        $ptx = $plugin_tx[$pluginName];
        $pluginPth = $pth['folder']['plugins'] . $pluginName . '/'; 

    /* Load Init file  */
        $initFile = $pluginPth . 'inits/init_' . $pcf['init'] . '.json';
        
        if(!file_exists($initFile)) {
            $o.= XH_message('fail','init_file "' . $pcf['init'] . '.json" not found');
            return false;
        }
        
    /* manual config file or config string */    
        if ($config) {
            $initFile = false;

            $inits = glob($pluginPth.'inits/*.json');

            foreach ($inits as $init) {
                $temp = explode('_', basename($init, '.json'));

                if (isset($temp[1]) && $temp[1] === $config) {
                    $initFile = $pluginPth . 'inits/init_' . $pcf['init'] . '.json';
                    break;
                }
            }
        }

        if ($initFile) {
            $temp = file_get_contents($initFile);
        } else {
            $temp = $config;
        }
        
        if (!$temp = json_decode($temp,true)) {
            $o.= XH_message('fail','bad JSON: '.json_last_error_msg ());
            return false;
        }
        
        /*
         * remove description
        */
        
        unset($temp['description']);

        /*
         * use english if tiny doesn't know $sl resp. $cf['default']['language']
         */
         
        if (file_exists($pluginPth . 'tinymce/langs/' . $sl . '.js')) {
            $tiny_language = $sl;
        } elseif (file_exists(
            $pluginPth . 'tinymce/langs/' . 
            $cf['language']['default'] .  '.js'
        )) {
            $tiny_language = $cf['language']['default'];
        } else {
            $tiny_language = 'en';
        }
        
        /* %LANGUAGE% = language:"[lang]"  and language_url = path to 
         * tinymce language file(in regard to the TinyMCE CDN Variant) 
         * if lang other than en
         */
        $temp['language'] =  $tiny_language;
        if ($tiny_language !='en' && $pcf['CDN'] == true ) 
            $temp['language_url'] =  CMSIMPLE_ROOT.'plugins/' . self::PLUGIN . '/tinymce/langs/' . $tiny_language.'.js';
        
        /*
         * fix Firefox scrollToTop Problem
        */
        $temp['content_style'] = 'body {height:unset;}';
        
        /*
         * Omit image upload ability when not in Admin mode
        */
            
        if (!XH_ADM)
            unset ($temp['images_upload_url'],$temp['images_upload_handler']);
            
        /*
         * Set the Selector
        */

        $temp['selector'] = $selector;
        
        $parsedconfig = json_encode($temp);

        /*
         * The styles of this sheet will be used inside the editor.
         *
         * All css classes will be available in the style-selectbox.
         *
         * If you have a lot of classes that are of no use for text editing,
         * you might want to create a special editor.css.
         */

        $parsedconfig = str_replace('%STYLESHEET%',$pth['folder']['template'] . 'stylesheet.css',$parsedconfig);
        $parsedconfig = str_replace('%CMSIMPLE_ROOT%',CMSIMPLE_ROOT,$parsedconfig);
        
        /*
         * obsolete??
        */
        
        $_headers = array();
        for ( $i = 1; $i <= 6; $i++ ) {
                $_headers[] = "Header $i=h$i";
        };
        $parsedconfig = str_replace('%HEADERS%', implode(';', $_headers), $parsedconfig);
        
        
        /*
         * Use the codemirror theme configured for Codeeditor_XH if available.
         */
        if (isset($plugin_cf['codeeditor']['theme'])) {
            $parsedconfig = str_replace('%CODEMIRROR_THEME%',$plugin_cf['codeeditor']['theme'],$parsedconfig);
        }
        
        /* 
         * Enable the file_picker_callback in Admin mode only (functions/callbacks not possible in JSON)
        */
        
        $append = '';
        if (XH_ADM) $append .= ',' . PHP_EOL . '"file_picker_callback":' . $_SESSION['tinymce_fb_callback'];

        
        /* 
         * Set import_selector_filter when importcss_append in format selector is set (regexp not possible in JSON)
        */

        if (isset($temp['importcss_append']) 
            && $temp['importcss_append'] === true
            && empty($temp['importcss_selector_filter'])
        ) {
            $append .= ',' . PHP_EOL . '"importcss_selector_filter": /(?:([a-z0-9\-_]+))(\.[a-z0-9_\-\.]+)$/i';          
        }
        
        /* 
         * blocks the upload of <img> elements with the alt attribute that starts with "demo-img"
         * images_dataimg_filter is deprecated since 5.9.3
        */
        if (isset($temp['images_upload_url']))
            $append .= ',' . PHP_EOL . 'images_dataimg_filter: function(img) {
    return !img.alt.startsWith("demo-img");
  }';        
        
        /*
         * Use the codemirror theme configured for Codeeditor_XH if available.
         */
        if (isset($temp['external_plugins']['codemirror']) && isset($plugin_cf['codeeditor']['theme'])) {
            $append .= ',' . PHP_EOL . ' setup: (editor) => {
		editor.options.register("codemirror_theme", { processor: "string", default: "'.$plugin_cf["codeeditor"]["theme"].'" });
    }';        
        }

        /* 
         * merge the append string into parsed config before the trailing curled bracket
        */

        $lastpos = strrpos ($parsedconfig,'}');
        $parsedconfig = substr($parsedconfig,0,$lastpos) . $append . substr($parsedconfig,$lastpos);
        return $parsedconfig;        
    }

    /**
     * Returns the JavaScript to activate the configured filebrowser.
     *
     * @return void
     *
     * @global bool  Whether the user is logged in as admin.
     * @global array The paths of system files and folders.
     * @global array The configuration of the core.
     */
    protected static function filebrowser()
    {
        global $cf, $edit, $pth;

        if (!XH_ADM) {   // no filebrowser, if editor is called from front-end
            $_SESSION['tinymce_fb_callback'] = ''; // suppress filebrowsercall
            return '';
        }

        $url = '';
        $script = ''; //holds the code of the callback-function

        /** 
         * Einbindung alternativer Filebrowser, gesteuert über 
         * $cf['filebrowser']['external'] und den Namen des aufrufenden Editors.
         */
        if ($cf['filebrowser']['external'] != false) {
            $fbConnector = CMSIMPLE_BASE . 'plugins/' . 
                $cf['filebrowser']['external'] . 
                '/connectors/' . self::PLUGIN . '/' . self::PLUGIN . '.php';
            if (is_readable($fbConnector)) {
                include_once $fbConnector;
                $init_function = $cf['filebrowser']['external'] . '_'. self::PLUGIN .'_init';
                if (function_exists($init_function)) {
                    $script = $init_function();
                    return $script;
                }
            }
        }

        //default filebrowser
        $_SESSION['tinymce_fb_callback'] = 'wrFilebrowser';
        $url =  CMSIMPLE_ROOT . 
            '?filebrowser=editorbrowser&editor=tinymce6&prefix=' . 
            CMSIMPLE_BASE;    
        $script = file_get_contents($pth['folder']['plugins']. self::PLUGIN . '/filebrowser.js');
        $script = str_replace('%URL%',  $url, $script);
        return $script;
    }

    /**
     * Writes the basic JavaScript of the editor to the `head' element.
     * No editors are actually created. Multiple calls are allowed.
     * This is called from init_EDITOR() automatically, but not from
     * EDITOR_replace().
     *
     * @return void
     *
     * @global string (X)HTML to insert in the `head' element.
     * @global array  The paths of system files and folders.
     * @global array  The configuration of the plugins.
     * @global array  The localization of the plugins.
     */
    static function doInclude()
    {
        global $edit, $pth, $h, $u, $l, $hjs, $plugin_cf;
        $pcf = $plugin_cf[self::PLUGIN];
        
        static $again = false;

        if ($again) {
            return;
        }
        $again = true;
        
            $tiny_src = ($pcf['CDN'] == "true") ?
                $pcf['CDN_src'] : 
                $pth['folder']['plugins'] . 
                    self::PLUGIN . 
                    '/tinymce/tinymce.min.js';

        if (XH_ADM) {
            include_once $pth['folder']['plugins'] . self::PLUGIN . '/links.php';
            $linkList = 'var myLinkList = ' .
                get_internal_links($h, $u, $l, $pth['folder']['downloads']) .
                ';';
        } else {
            $linkList = '';
        }

        $hjs .='
            <script src="'. 
            $tiny_src. 
            '" referrerpolicy="origin"></script>
        <script>
        ' . self::filebrowser() . '
        var myLinkList;
        ' . $linkList . '
        </script>
        ';
    }

    /**
     * Returns the JavaScript to actually instantiate a single editor a
     * `textarea' element.
     *
     * To actually create the editor, the caller has to write the the return value
     * to the (X)HTML output, properly enclosed as `script' element,
     * after the according `textarea' element,
     * or execute the return value by other means.
     *
     * @param string $elementId The id of the `textarea' element that should become
     *                          an editor instance.
     * @param string $config    The configuration string.
     *
     * @return string The JavaScript to actually create the editor.
     */
    static function replace($elementId=false, $config = '')
    {

        if (!$elementId) {
            return '';
        }
        $config = self::config($config, '#' . $elementId);
        return self::setInit($config);
    }

    /**
     * Instantiates the editor(s) on the textarea(s) given by $classes.
     * $config is exactly the same as for EDITOR_replace().
     *
     * @param string $classes The classes of the textarea(s) that should become
     *                        an editor instance.
     * @param string|false $config  The configuration string.
     *
     * @return void
     *
     * global string (X)HTML to insert at the bottom of the `body' element.
     */
    static function init($classes = array(), $config = false)
    {
        global $hjs;

        include_tinymce6();
        
        $initClasses = '.xh-editor';
        if (is_array($classes) && (bool) $classes) {
            $initClasses = '.' . implode(',.', $classes);
        }
        
        $temp = self::config($config, $initClasses);

        $hjs .= '
        <script>
        ' . self::setInit($temp) . '
        </script>
        ';
    }

    /**
     * Returns all available themes.
     *
     * @return array
     *
     * @global array The paths of system files and folders.
     */
    static function getThemes()
    {
        global $pth;

        $themes = array('', 'default');
        $foldername = $pth['folder']['plugins'] . 'codeeditor/codemirror/theme';
        if ($dir = opendir($foldername)) {
            while (($entry = readdir($dir)) !== false) {
                if (pathinfo($entry, PATHINFO_EXTENSION) == 'css') {
                    $themes[] = basename($entry, '.css');
                }
            }
        }
        return $themes;
    }

/**
 * Helper sequence to set the init JS string correctly.
 *
 * @param string|false $config The configuration string.
 * @return string the whole editor js string
 *
 */
    private static function setInit($config) 
    {
        static $run = 0;
        if ($config === false) {
            return "";
        }
        $js = str_replace(
            'tinyArgs', 'tinyArgs'.$run, '
            if (typeof(tinymce) === "undefined" || tinymce === null) {
                alert("tinyMCE not present! Either offline or local library missing.")
            } else {
                var tinyArgs = ' . $config . ';
                if (myLinkList) 
                    tinyArgs.link_list = myLinkList;
                tinymce.init(tinyArgs);
            }
            '
        );
        $run++;
        return $js;
}
}

