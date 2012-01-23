<?php

// utf-8-marker: äöüß

global $adm, $edit;
if (!($adm && $edit)) {
    return ' ';
}

function tinymce_filebrowser(){
    global $cf /*$backend_hooks*/;
    
    $url = '';
	$script = ''; //holds the code of the callback-function
	
    /*
    switch ($backend_hooks['filebrowser']) {
        case 'hi_kcfinder':
            $url =  CMSIMPLE_ROOT . 'plugins/hi_kcfinder/kcfinder/browse.php?opener=tinymce';
            break;

        default:
            $url =  CMSIMPLE_ROOT . 'plugins/filebrowser/editorbrowser.php?editor=tinymce&prefix='. CMSIMPLE_BASE .'&base=./';
            
    }
	*/
	
	//Einbindung alternativer Filebrowser, gesteuert über $backend_hooks['filebrowser']
	//und den Namen des aufrufenden Editors
	if (/*$backend_hooks['filebrowser']*/ $cf['filebrowser']['external'] != FALSE) {
		$fbConnector = CMSIMPLE_BASE . 'plugins/'. $cf['filebrowser']['external'] /*$backend_hooks['filebrowser']*/ .'/connectors/tinymce/tinymce.php';
		if (is_readable($fbConnector)) {
			include_once($fbConnector);
			$init_function = /*$backend_hooks['filebrowser']*/$cf['filebrowser']['external'].'_tinymce_init';
			if (function_exists($init_function)) {
				$script = $init_function();
			}
		return $script;
		}
		
	} else {
	
		//default filebrowser
		$_SESSION['tinymce_fb_callback'] = 'wrFilebrowser';
		$url =  CMSIMPLE_ROOT . 'plugins/filebrowser/editorbrowser.php?editor=tinymce&prefix='. CMSIMPLE_BASE .'&base=./';
		$script = file_get_contents(dirname(__FILE__) . '/filebrowser.js');
		$script = str_replace('%URL%',  $url, $script);
		return $script;
	
	}
}

function include_tinymce(){
    global $pth, $hjs;
    static $again = FALSE;
    if ($again) {return;}
    $again = TRUE;
    $hjs .='
        <script language="javascript" type="text/javascript" src="' . $pth['folder']['plugins'] . 'tinymce/' . 'tiny_mce/tiny_mce.js"></script>
        ';
}

function tinymce_replace($elementID = false, $config = ''){
    if(!$elementID){
        return;
    }
   
   return '
       <script type="text/javascript">
       /* <![CDATA[ */
            new tinymce.Editor("' . $elementID .'", { ' . $config . '}).render();
	/* ]]> */
       </script>
       ';
}

function init_tinymce($classes = array(), $config = false) {
    global $sl, $cf, $plugin_cf, $pth, $hjs, $o, $h, $u, $l, $sn, $onload;
    static $run = 0;
    
    include_tinymce();
    

    $initClasses = 'xh-editor';
    $tiny_mode = isset($plugin_cf['tinymce']['init']) && file_exists($pth['folder']['plugins'] . 'tinymce/' . 'inits/init_' . $plugin_cf['tinymce']['init'] . '.js') ? $plugin_cf['tinymce']['init'] : 'full';
    $initFile = $pth['folder']['plugins'] . 'tinymce/' . 'inits/init_' . $tiny_mode . '.js';

    if (is_array($classes) && (bool) $classes) {
        $initClasses = implode('|', $classes);
    }

    if ($config !== FALSE) {
        $initFile = false;
        
        $inits = glob($pth['folder']['plugins'] . 'tinymce/inits/*.js');
        $options = array();

        foreach ($inits as $init) {
            $temp = explode('_', basename($init, '.js'));

            if (isset($temp[1]) && $temp[1] === $config) {
               $tiny_mode = $config;
               $isFile = false;
           $initFile = $pth['folder']['plugins'] . 'tinymce/' . 'inits/init_' . $tiny_mode . '.js';
               break;
            }
        }
        
        if(!$initFile){
            $initFile = $config;
            
        }
        
       
    }
    
    if (!$run) {
	$hjs .= '<script type="text/javascript" src="'.$pth['folder']['plugins'].'tinymce/init.js"></script>'."\n";
    }
    

    /*
     * use english if tiny doesn't know $sl resp. $cf['default']['language']
     */
    $tiny_language = file_exists($pth['folder']['plugins'] . 'tinymce/' . 'tiny_mce/langs/' . $sl . '.js') ? $sl
	: (file_exists($pth['folder']['plugins'] . 'tinymce/' . 'tiny_mce/langs/' . $cf['language']['default'] . '.js') ? $cf['language']['default']
	: 'en');

    /*
     * The styles of this sheet will be used inside the editor.
     * 
     * All css classes will be available in the style-selectbox.
     * 
     * If you have a lot of classes that are of no use for text editing,
     * you might want to create a special editor.css.
     */
    $tiny_css = $pth['folder']['template'] . 'stylesheet.css';

    include_once $pth['folder']['plugins'] . 'tinymce/' . 'links.php';

    $temp = file_get_contents($initFile);

    $temp = str_replace('%TINY_FOLDER%', $pth['folder']['plugins'] . 'tinymce/', $temp);
    $temp = str_replace('%LANGUAGE%', $tiny_language, $temp);


    //$temp = str_replace('\'%IMAGES%\'', get_images($pth['folder']['images']), $temp);
    //$temp = str_replace('\'%INTERNAL_LINKS%\'', get_internal_links($h, $u, $l, $sn, $pth['folder']['downloads']), $temp);

    $temp = str_replace('%STYLESHEET%', $tiny_css, $temp);
    $temp = str_replace('%BASE_URL%', $sn, $temp);

    $elementFormat = $cf['xhtml']['endtags'] == 'true' ? 'xhtml' : 'html';
    $temp = str_replace('%ELEMENT_FORMAT%', $elementFormat, $temp);
	if ($initClasses == 'xh-editor') 
	{
		$temp = str_replace('"%EDITOR_HEIGHT%"', 'height : "'.$cf['editor']['height'].'",', $temp);
	} 
	else 
	{
		$temp = str_replace('"%EDITOR_HEIGHT%"', '', $temp);
	}
	//$temp = str_replace("%INIT_CLASSES%", $initClasses, $temp);
	
	//$temp .= tinymce_filebrowser();
	
	$imageList = !$run ? 'var myImageList = new Array('.get_images($pth['folder']['images']).');' : '';
	$linkList = !$run ? 'var myLinkList = new Array('.get_internal_links($h, $u, $l, $sn, $pth['folder']['downloads']).');' : '';
	$filebrowser = !$run ? tinymce_filebrowser() : '';
	
	$temp = str_replace("%FILEBROWSER_CALLBACK%", $_SESSION['tinymce_fb_callback'], $temp);

    $hjs .= <<<SCRIPT
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
$imageList
$linkList
$filebrowser
function tinyMCE_initialize$run() {
    tinyMCE_instantiateByClasses('$initClasses', $temp);
}
/* ]]> */
</script>

SCRIPT;

    $onload .= 'tinyMCE_initialize'.$run.'();';
    $run++;
    return;
}

/*
 * End of file plugins/tinymce/tinymce.php
 */