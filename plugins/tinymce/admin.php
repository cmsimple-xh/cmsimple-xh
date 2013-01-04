<?php

/**
 * @version $Id$
 */
/* utf-8 marker: äöü */

if (!XH_ADM) {     return; }

initvar('tinymce');

if ($tinymce) {
    $plugin = basename(dirname(__FILE__), "/");
    $o = '<div class="plugintext">';
    $o .= '<div class="plugineditcaption">TinyMCE for CMSimple_XH</div>';
    $o .= '<p>Version for $CMSIMPLE_XH_VERSION$</p>';
    $o .= '<p>TinyMCE version 3.5.8  &ndash; <a href="http://www.tinymce.com/" target="_blank">http://www.tinymce.com/</a></p>';
    $o .= '<p>CMSimpe_xh & Filebrowser integration &ndash; <a href="http://www.pixolution.ch/" target="_blank">http://www.pixolution.ch/</a></p>';
    $o .= tag('br'). tag('br'). tag('br');

    $admin= isset($_POST['admin']) ? $_POST['admin'] : $admin = isset($_GET['admin']) ? $_GET['admin'] : 'plugin_config';
    $action= isset($_POST['action']) ? $_POST['action'] : $action = isset($_GET['action']) ? $_GET['action'] : 'plugin_edit';
		
		if (tinymce_setOptions())
		{
			$o .= plugin_admin_common($action,$admin,$plugin);
		}
}
		
function tinymce_setOptions()
{
	global $pth;

	$inits = glob($pth['folder']['plugins'] . 'tinymce/inits/*.js');
	$options = array();

	foreach ($inits as $init) {
			$temp = explode('_', basename($init, '.js'));

			if (isset($temp[1])) {
					$options[] = $temp[1];
			}
	}
	return (bool) $options;
}
/*
 * EOF tinymce/admin.php
 */