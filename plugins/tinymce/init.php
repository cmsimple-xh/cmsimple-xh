<?php

/**
 * @version $Id$
 */

// utf-8-marker: äöüß

/**
 * Returns the JS to activate the configured filebrowser.
 *
 * @return void
 */
    function tinymce_filebrowser() {
        global $adm, $cf;

        if (!$adm) { return ''; }  // no filebrowser, if editor is called from front-end

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
                    return $script;
                }
            }
        }

        //default filebrowser
        $_SESSION['tinymce_fb_callback'] = 'wrFilebrowser';
        $url =  CMSIMPLE_ROOT . 'plugins/filebrowser/editorbrowser.php?editor=tinymce&prefix=' . CMSIMPLE_BASE . '&base=./';
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
function include_tinymce() {
    global $adm, $pth, $h, $u, $l, $sn, $hjs;
    static $again = FALSE;

    if ($again) {return;}
    $again = TRUE;

    if ($adm) {
	include_once $pth['folder']['plugins'] . 'tinymce/' . 'links.php';
	$imageList = 'var myImageList = new Array('.get_images($pth['folder']['images']).');';
	$linkList = 'var myLinkList = new Array('.get_internal_links($h, $u, $l, $sn, $pth['folder']['downloads']).');';
    } else {
	$imageList = $linkList = '';
    }

    $hjs .='
        <script language="javascript" type="text/javascript" src="' . $pth['folder']['plugins'] . 'tinymce/' . 'tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript" src="' . $pth['folder']['plugins'] . 'tinymce/init.js"></script>
	<script type="text/javascript">
	/* <![CDATA[ */
	' . tinymce_filebrowser() . '
	' . $imageList . '
	' . $linkList . '
	/* ]]> */
	</script>
	';
}


/**
 * Returns the config object.
 *
 * @return string
 */
function tinymce_config($xh_editor, $config) {
    global $pth, $sl, $sn, $cf, $plugin_cf, $plugin_tx, $s, $cl;

    if (!isset($plugin_cf['tinymce'])) {
	include_once $pth['folder']['plugins'] . 'tinymce/config/config.php';
    }

    $tiny_mode = isset($plugin_cf['tinymce']['init']) && file_exists($pth['folder']['plugins'] . 'tinymce/' . 'inits/init_' . $plugin_cf['tinymce']['init'] . '.js') ? $plugin_cf['tinymce']['init'] : 'full';
    $initFile = $pth['folder']['plugins'] . 'tinymce/' . 'inits/init_' . $tiny_mode . '.js';
    if ($config) {
        $initFile = false;

        $inits = glob($pth['folder']['plugins'] . 'tinymce/inits/*.js');
        //$options = array();

        foreach ($inits as $init) {
            $temp = explode('_', basename($init, '.js'));

            if (isset($temp[1]) && $temp[1] === $config) {
		$tiny_mode = $config;
		$isFile = false;
		$initFile = $pth['folder']['plugins'] . 'tinymce/' . 'inits/init_' . $tiny_mode . '.js';
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
    $tiny_language = file_exists($pth['folder']['plugins'] . 'tinymce/' . 'tiny_mce/langs/' . $sl . '.js')
	    ? $sl : (file_exists($pth['folder']['plugins'] . 'tinymce/' . 'tiny_mce/langs/' . $cf['language']['default'] . '.js')
	    ? $cf['language']['default'] : 'en');

    /*
     * The styles of this sheet will be used inside the editor.
     *
     * All css classes will be available in the style-selectbox.
     *
     * If you have a lot of classes that are of no use for text editing,
     * you might want to create a special editor.css.
     */
    $tiny_css = $pth['folder']['template'] . 'stylesheet.css';

    $temp = str_replace('%TINY_FOLDER%', $pth['folder']['plugins'] . 'tinymce/', $temp);
    $temp = str_replace('%LANGUAGE%', $tiny_language, $temp);

    //$temp = str_replace('\'%IMAGES%\'', get_images($pth['folder']['images']), $temp);
    //$temp = str_replace('\'%INTERNAL_LINKS%\'', get_internal_links($h, $u, $l, $sn, $pth['folder']['downloads']), $temp);

    $temp = str_replace('%STYLESHEET%', $tiny_css, $temp);
    $temp = str_replace('%BASE_URL%', $sn, $temp);

    if($plugin_cf['tinymce']['headers_page_creating'] && $s >= 0 && $s < $cl) {
        $_blockFormats = array();
        for ( $i = $cf['menu']['levels'] + 1; $i <= 6; $i++ ) {
            $_blockFormats[] = "h$i=h$i";
        };

        $_blockFormats[] = "p=p";

        if($plugin_cf['tinymce']['headers_page_creating'] == 'show page level') {
            for ( $i=1; $i <= $cf['menu']['levels'];$i++ ) {
                $_blockFormats [] = sprintf($plugin_tx['tinymce']['pageheader'],$i) . "=h$i";
            }
        }
        $_blockFormats[] = "dt=dt,dd=dd,code=code,pre=pre";

        $temp = str_replace('%BLOCK_FORMATS%', implode(';',$_blockFormats), $temp);
        unset($_blockFormats);
    } else {
        $temp = str_replace('%BLOCK_FORMATS%', 'h1,h2,h3,h4,h5,h6,p,dt,dd,code,pre', $temp);
    }

    $elementFormat = $cf['xhtml']['endtags'] == 'true' ? 'xhtml' : 'html';
    $temp = str_replace('%ELEMENT_FORMAT%', $elementFormat, $temp);
    if ($xh_editor)
    {
	$temp = str_replace('"%EDITOR_HEIGHT%"', 'height : "'.$cf['editor']['height'].'",', $temp);
    }
    else
    {
	$temp = str_replace('"%EDITOR_HEIGHT%"', '', $temp);
    }
    //$temp = str_replace("%INIT_CLASSES%", $initClasses, $temp);

    $temp = str_replace("%FILEBROWSER_CALLBACK%", $_SESSION['tinymce_fb_callback'], $temp);

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
 function tinymce_replace($elementID = false, $config = '') {
    if(!$elementID){
        return '';
    }

    $temp = tinymce_config(FALSE, $config);

    return 'new tinymce.Editor("' . $elementID .'", ' . $temp . ').render();';
}


/**
 * Instantiates the editor(s) on the textarea(s) given by $element_classes.
 * $config is exactly the same as for EDITOR_replace().
 *
 * @param string $element_classes  The classes of the textarea(s) that should become an editor instance. An empty array means .xh-editor.
 * @param string $config  The configuration string.
 * @global string $onload
 * @return void
 */
 function init_tinymce($classes = array(), $config = false) {
    global $hjs, $onload;
    static $run = 0;

    include_tinymce();

    $initClasses = 'xh-editor';
    if (is_array($classes) && (bool) $classes) {
        $initClasses = implode('|', $classes);
    }

    $temp = tinymce_config($initClasses == 'xh-editor', $config);

    $hjs .= '
	<script language="javascript" type="text/javascript">
	/* <![CDATA[ */
	function tinyMCE_initialize' . $run . '() {
	    tinyMCE_instantiateByClasses(\'' . $initClasses . '\', ' . $temp . ');
	}
	/* ]]> */
	</script>
	';

    $onload .= 'tinyMCE_initialize' . $run . '();';
    $run++;
    return;
}

/*
 * End of file plugins/tinymce/tinymce.php
 */
