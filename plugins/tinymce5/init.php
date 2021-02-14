<?php

/**
 * Editor Init Functions -- init.php
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Tinymce5
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

 /**
 * defines admin mode
 */
if (!defined('XH_ADM')) {
    define('XH_ADM', $adm);
}

/**
 * Returns the JS to activate the configured filebrowser.
 *
 * @return string filebrowser script
 */
function tinymce5_filebrowser()
{
    global $cf, $edit;

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
            '/connectors/tinymce5/tinymce5.php';
        if (is_readable($fbConnector)) {
            include_once $fbConnector;
            $init_function = $cf['filebrowser']['external'] . '_tinymce5_init';
            if (function_exists($init_function)) {
                $script = $init_function();
                return $script;
            }
        }
    }

    //default filebrowser
    $_SESSION['tinymce_fb_callback'] = 'wrFilebrowser';
    
    //principle occurance of XH_VERSION is checked in index.php
    if (CMSIMPLE_XH_VERSION != '@CMSIMPLE_XH_VERSION@'
        && version_compare(CMSIMPLE_XH_VERSION, 'CMSimple_XH 1.7', 'lt')
    ) { 
        $url =  CMSIMPLE_ROOT . 
            'plugins/filebrowser/editorbrowser.php?editor=tinymce5&prefix=' . 
            CMSIMPLE_BASE . 
            '&base=./';
    } else {  // CMSimple_XH v1.7 (r1518)
        $url =  CMSIMPLE_ROOT . 
            '?filebrowser=editorbrowser&editor=tinymce5&prefix=' . 
            CMSIMPLE_BASE;           
    }
    
    $script = file_get_contents(dirname(__FILE__) . '/filebrowser.js');
    $script = str_replace('%URL%',  $url, $script);
    return $script;
}

/**
 * Writes the basic JS of the editor to $hjs. No editors are actually created.
 * Multiple calls are allowed; all but the first should be ignored.
 * This is called from init_EDITOR() automatically, but not from EDITOR_replace().
 *
 * @global string $hjs
 * @return void
 */
function include_tinymce5() 
{
    global $edit, $pth, $h, $u, $l, $hjs, $plugin_cf;
    $pcf = $plugin_cf['tinymce5'];
    
    static $again = false;

    if ($again) {
        return;
    }
    $again = true;
    
        $tiny_src = ($pcf['CDN'] == "true") ?
            $pcf['CDN_src'] : 
            $pth['folder']['plugins'] . 
                'tinymce5/' . 
                'tinymce/tinymce.min.js';

    if (XH_ADM) {
        include_once $pth['folder']['plugins'] . 'tinymce5/' . 'links.php';
        $linkList = 'var myLinkList = ' .
            get_internal_links($h, $u, $l, $pth['folder']['downloads']) .
            ';';
    } else {
        $linkList = '';
    }

    $hjs .='
        <script language="javascript" type="text/javascript" src="'. 
        $tiny_src. 
        '"></script>
	<script type="text/javascript">
	' . tinymce5_filebrowser() . '
    var myLinkList;
	' . $linkList . '
	</script>
    <style type="text/css">
        div.mce-fullscreen {z-index: 999;}  /*fullscreen overlays admin toolbar */
    </style>
	';
}


/**
 * Returns the config object.
 *
 * @param string $config    manual config string.
 * @param string $selector  manual manual editor DOM node.
 *
 * @return string editor config
 */
function tinymce5_config($config, $selector)
{
    global $cl, $pth, $sl, $cf, $plugin_cf, $plugin_tx, $s;

    $pcf = $plugin_cf['tinymce5'];
    $ptx = $plugin_tx['tinymce5'];
    $pluginName = basename(dirname(__FILE__), "/");
    $pluginPth = $pth['folder']['plugins'] . $pluginName . '/';

    if (!isset($pcf)) {
        include_once $pluginPth . 'config/config.php';
    }

    $tiny_mode
        = isset($pcf['init']) 
            && file_exists($pluginPth . 'inits/init_' . $pcf['init'] . '.js') ?
            $pcf['init'] : 
            'full';
    $initFile = $pluginPth . 'inits/init_' . $tiny_mode . '.js';
    if ($config) {
        $initFile = false;

        $inits = glob($pluginPth.'inits/*.js');

        foreach ($inits as $init) {
            $temp = explode('_', basename($init, '.js'));

            if (isset($temp[1]) && $temp[1] === $config) {
                $tiny_mode = $config;
                $initFile = $pluginPth . 'inits/init_' . $tiny_mode . '.js';
                break;
            }
        }
    }

    if ($initFile) {
        $temp = file_get_contents($initFile);
    } else {
        $temp = $config;
    }

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
    

    /*
     * The styles of this sheet will be used inside the editor.
     *
     * All css classes will be available in the style-selectbox.
     *
     * If you have a lot of classes that are of no use for text editing,
     * you might want to create a special editor.css.
     */

    $temp  = str_replace(
        '%STYLESHEET%', $pth['folder']['template'] . 
        'stylesheet.css', $temp
    );

    /* %LANGUAGE% = language:"[lang]"  and language_url = path to 
     * tinymce language file(in regard to the TinyMCE CDN Variant) 
     * if lang other than en
     */
    $temp = str_replace(
        '%LANGUAGE%', 'language: "' . $tiny_language .'",'
        . ($tiny_language !='en' && $pcf['CDN'] == true ? 
            '
  language_url: "' . 
            CMSIMPLE_ROOT.'plugins/tinymce5/tinymce/langs/' . 
            $tiny_language.'.js",' : ''), $temp
    );

    /* 
     * compute CMSIMPLEROOT faciliates usage of init_fontawesome.js
     */
    $temp = str_replace(
        '%CMSIMPLE_ROOT%', CMSIMPLE_ROOT, $temp
    );

    $elementFormat = $cf['xhtml']['endtags'] == 'true' ? 'xhtml' : 'html';
    $temp = str_replace('%ELEMENT_FORMAT%', $elementFormat, $temp);
    
    $_named_pageheaders = $_pageheaders = $_headers = array();
    for ( $i = 1; $i <= 6; $i++ ) {
             $_pageheaders [] = "Header $i=h$i";
            $_named_pageheaders [] = sprintf($ptx['pageheader'], $i) . "=h$i";
            $_headers[] = "Header $i=h$i";
    };
    $temp = str_replace('%PAGEHEADERS%', implode(';', $_pageheaders), $temp);
    
    $temp = str_replace('%HEADERS%', implode(';', $_headers), $temp);
    $temp = str_replace(
        '%NAMED_PAGEHEADERS%', 
        implode(
            ';', 
            ($s >= 0 && $s < $cl) ? 
            $_named_pageheaders : 
            $_pageheaders
        ), $temp
    );

    $temp = str_replace('%SELECTOR%', $selector, $temp);
    
    $temp = str_replace(
        '"%FILEBROWSER_CALLBACK%"', 
        XH_ADM ? 
        $_SESSION['tinymce_fb_callback'] : 
        '""', 
        $temp
    );

    return $temp;
}


/**
 * Returns the JS to actually instantiate a single editor on the textarea 
 * given by $element_id. $config can be 'full', 'medium', 'minimal', 'sidebar' 
 * or '' (which will use the users default configuration). Other values are editor 
 * dependent. Typically this will be a string in JSON format enclosed in { },
 * that can contain %PLACEHOLDER%s, that will be substituted.
 *
 * To actually create the editor, the caller has to write the the return value 
 * to the (X)HTML output, properly enclosed as <script>, after the according 
 * <textarea>, or execute the return value by other means.
 *
 * @param string $elementID The id of the textarea that should become an 
 *                          editor instance.
 * @param string $config    The configuration string.
 *
 * @return string  The JS to actually create the editor.
 */
function tinymce5_replace($elementID = false, $config = '') 
{
    if (!$elementID) {
        return '';
    }
    $config = tinymce5_config($config, '#' . $elementID);
   
    return _setInit($config);
}


/**
 * Instantiates the editor(s) on the textarea(s) given by $element_classes.
 * $config is exactly the same as for EDITOR_replace().
 *
 * @param string $classes The element classes of the textarea(s) that should
 *                        become an editor instance.
 *                        An empty array means .xh-editor.
 * @param string $config  The configuration string.
 *
 * @return void
 */
function init_tinymce5($classes = array(), $config = false) 
{
    global $hjs;

    include_tinymce5();
    
    $initClasses = '.xh-editor';
    if (is_array($classes) && (bool) $classes) {
        $initClasses = '.' . implode(',.', $classes);
    }
    
    $temp = tinymce5_config($config, $initClasses);

    $hjs .= '
	<script language="javascript" type="text/javascript">
	' . _setInit($temp) . '
	</script>
	';
    return;
}


/**
 * Helper sequence to set the init JS string correctly.
 *
 * @param string $config The configuration string.
 *
 * @return string the whole editor js string
 */
function _setInit($config) 
{
    static $run = 0;
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

/*
 * End of file plugins/tinymce5/init.php
 */
