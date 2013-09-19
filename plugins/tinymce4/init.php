<?php

// utf-8-marker: äöüß
if (!defined('XH_ADM')) define('XH_ADM', $adm); 
/**
 * Returns the JS to activate the configured filebrowser.
 *
 * @return void
 */
function tinymce4_filebrowser() {
    global $cf, $edit;

    if (!(XH_ADM && $edit)) { return ''; }  // no filebrowser, if editor is called from front-end

    $url = '';
    $script = ''; //holds the code of the callback-function

    //Einbindung alternativer Filebrowser, gesteuert über $cf['filebrowser']['external']
    //und den Namen des aufrufenden Editors
    if ($cf['filebrowser']['external'] != FALSE) {
	$fbConnector = CMSIMPLE_BASE . 'plugins/' . $cf['filebrowser']['external'] . '/connectors/tinymce/tinymce.php';
	if (is_readable($fbConnector)) {
	    include_once($fbConnector);
	    $init_function = $cf['filebrowser']['external'] . '_tinymce_init';
	    if (function_exists($init_function)) {
		    $script = $init_function();
	    }
	return $script;
	}

    } else {

	//default filebrowser
	$_SESSION['tinymce_fb_callback'] = 'wrFilebrowser';
	$url =  CMSIMPLE_ROOT . 'plugins/filebrowser/editorbrowser.php?editor=tinymce4&prefix=' . CMSIMPLE_BASE . '&base=./';
	$script = file_get_contents(dirname(__FILE__) . '/filebrowser.js');
	$script = str_replace('%URL%',  $url, $script);
	return $script;

    }
}


/**
 * Writes the basic JS of the editor to $hjs. No editors are actually created.
 * Multiple calls are allowed; all but the first should be ignored.
 * This is called from init_EDITOR() automatically, but not from EDITOR_replace().
 *
 * @global string $hjs
 * @return void
 */
function include_tinymce4() {
    global $edit, $pth, $h, $u, $l, $sn, $hjs, $plugin_cf;
    static $again = FALSE;

    if ($again) {return;}
    $again = TRUE;

    if (XH_ADM && $edit) {
        include_once $pth['folder']['plugins'] . 'tinymce4/' . 'links.php';
        $imageList = 'var myImageList = '.get_images($pth['folder']['images']).';';
        $linkList = 'var myLinkList = '.get_internal_links($h, $u, $l, $sn, $pth['folder']['downloads']).';';
    } else {
        $imageList = $linkList = '';
    }
    
    $hjs .='
        <script language="javascript" type="text/javascript" src="' . $pth['folder']['plugins'] . 'tinymce4/' . 'tinymce/tinymce.min.js"></script>
	<script type="text/javascript">
	/* <![CDATA[ */
	' . tinymce4_filebrowser() . '
	' . $imageList . '
	' . $linkList . '
	/* ]]> */
	</script>
    <style type="text/css">
        div.mce-fullscreen {z-index: 999;}  /*fullscreen overlays admin toolbar */
    </style>
	';
}


/**
 * Returns the config object.
 *
 * @return string
 */
function tinymce4_config($xh_editor, $config, $selector) {
    global $edit, $pth, $sl, $sn, $cf, $plugin_cf;

    if (!isset($plugin_cf['tinymce4'])) {
	include_once $pth['folder']['plugins'] . 'tinymce4/config/config.php';
    }

    $tiny_mode = isset($plugin_cf['tinymce']['init']) && file_exists($pth['folder']['plugins'] . 'tinymce4/' . 'inits/init_' . $plugin_cf['tinymce4']['init'] . '.js') ? $plugin_cf['tinymce4']['init'] : 'full';
    $initFile = $pth['folder']['plugins'] . 'tinymce4/' . 'inits/init_' . $tiny_mode . '.js';
    if ($config) {
        $initFile = false;

        $inits = glob($pth['folder']['plugins'] . 'tinymce4/inits/*.js');
        //$options = array();

        foreach ($inits as $init) {
            $temp = explode('_', basename($init, '.js'));

            if (isset($temp[1]) && $temp[1] === $config) {
                $tiny_mode = $config;
                $isFile = false;
                $initFile = $pth['folder']['plugins'] . 'tinymce4/' . 'inits/init_' . $tiny_mode . '.js';
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
    $tiny_language = file_exists($pth['folder']['plugins'] . 'tinymce4/' . 'tinymce/langs/' . $sl . '.js')
	    ? $sl : (file_exists($pth['folder']['plugins'] . 'tinymce4/' . 'tinymce/langs/' . $cf['language']['default'] . '.js')
	    ? $cf['language']['default'] : 'en');

    /*
     * The styles of this sheet will be used inside the editor.
     *
     * All css classes will be available in the style-selectbox.
     *
     * If you have a lot of classes that are of no use for text editing,
     * you might want to create a special editor.css.
     */
    $temp = XH_decodeJSON($temp);
    
    $temp -> content_css = $pth['folder']['template'] . 'stylesheet.css';

    if ($tiny_language != 'en') $temp -> language = $tiny_language; //no tiny langfile for en

    $elementFormat = $cf['xhtml']['endtags'] == 'true' ? 'xhtml' : 'html';
    $temp -> element_format = $elementFormat;
    
    if ($xh_editor)
    {
        if (!isset($temp -> selector)) $temp -> selector = 'textarea#text';
        $temp -> height = $cf['editor']['height'];
    }
    else
    {
        unset($temp -> height);
        if ($selector) $temp -> selector = $selector;
    }

//Inhibit filebrowser and image-/linkslists in frontend mode    
    if (!XH_ADM || !$edit){
        unset($temp -> image_list);
        unset($temp -> link_list);
        unset($temp -> file_browser);
        unset($temp -> file_browser_callback);
    }
    
    $temp = XH_encodeJSON($temp);
    return $temp;
}


/**
 * Returns the JS to actually instantiate a single editor on the textarea given by $element_id.
 * $config can be 'full', 'medium', 'minimal', 'sidebar' or '' (which will use the users default configuration).
 * Other values are editor dependent. Typically this will be a string in JSON format enclosed in { },
 * that can contain %PLACEHOLDER%s, that will be substituted.
 *
 * To actually create the editor, the caller has to write the the return value to the (X)HTML output,
 * properly enclosed as <script>, after the according <textarea>, or execute the return value by other means.
 *
 * @param string $element_id  The id of the textarea that should become an editor instance.
 * @param string $config  The configuration string.
 * @return string  The JS to actually create the editor.
 */
 function tinymce4_replace($elementID = false, $config = '') {
    if(!$elementID){
        return '';
    }
    $config = tinymce4_config(FALSE, $config, '#' . $elementID);
   
    return _setInit($config);
}


/**
 * Instantiates the editor(s) on the textarea(s) given by $element_classes.
 * $config is exactly the same as for EDITOR_replace().
 *
 * @param string $element_classes  The classes of the textarea(s) that should become an editor instance. An empty array means .xh-editor.
 * @param string $config  The configuration string.
 * @return void
 */
 function init_tinymce4($classes = array(), $config = false) {
    global $hjs;

    include_tinymce4();
    
    if (!is_array($classes) && (bool) $classes === false) return;

    $initClasses = 'xh-editor';
    if (is_array($classes) && (bool) $classes) {
        $initClasses = '.' . implode(',.', $classes);
    }
    
    $temp = tinymce4_config($initClasses == 'xh-editor', $config, $initClasses);

    $hjs .= '
	<script language="javascript" type="text/javascript">
	/* <![CDATA[ */' . _setInit($temp) . '
	/* ]]> */
	</script>
	';
    return;
}

function _setInit($config) {
    static $run = 0;
    $js = str_replace('tinyArgs','tinyArgs'.$run,'
    var tinyArgs = ' . $config . ';
    if (tinyArgs.image_list && myImageList.length > 0 ) 
        tinyArgs.image_list = myImageList;
    else
        delete tinyArgs.image_list;
    if (tinyArgs.link_list) 
        tinyArgs.link_list = myLinkList;
    if (tinyArgs.file_browser) 
        tinyArgs.file_browser_callback = ' . $_SESSION['tinymce_fb_callback'] . ';
    tinyArgs.height = eval(tinyArgs.height);
    if (typeof tinyArgs.height !== "number") 
        delete tinyArgs.height;
    tinymce.init(tinyArgs);
    ');
    $run++;
    return $js;
}

/*
 * End of file plugins/tinymce4/init.php
 */