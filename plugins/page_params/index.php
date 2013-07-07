<?php
/* utf8-marker = äöüß */
/**
 * Page-Parameters - main index.php
 * Plugin (V.2.1-compatible)
 *
 * Stores page-parameters (heading, template)
 * and let the user take control of visibility
 * of the page.
 * index.php is called by pluginloader and
 * manipulates the respective CMSimple-data.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @maintenance Jerry Jakobsfeld http://simplesolutions.dk
 * @version 1.1.0 December 2012
 * @package pluginloader
 * @subpackage page_params
 */
/**
 * Check if PLUGINLOADER is calling and die if not
 */
if(!defined('PLUGINLOADER_VERSION')){die('Plugin '. basename(dirname(__FILE__)) . ' requires a newer version of the Pluginloader. No direct access.');}

/**
 * Add used interests to router
 */
$pd_router -> add_interest('heading');
$pd_router -> add_interest('show_heading');
$pd_router -> add_interest('template');
$pd_router -> add_interest('published');
$pd_router -> add_interest('publication_date');  //extention by simpleSolutions
$pd_router -> add_interest('expires');           //extention by simpleSolutions
$pd_router -> add_interest('show_last_edit');
$pd_router -> add_interest('linked_to_menu');
$pd_router -> add_interest('header_location');
$pd_router -> add_interest('use_header_location');

/**
 * Add a tab for admin-menu
 */
$pd_router -> add_tab($plugin_tx['page_params']['tab'], $pth['folder']['plugins'].'page_params/page_params_view.php');

/**
 * Set template, overwrite CMSimple.
 */
if(isset($pd_current['template'])
&& $pd_current['template'] !== $cf['site']['template']
&& trim($pd_current['template']) !== ''
&& is_dir($pth['folder']['templates'].$pd_current['template'])
)
{
	$cf['site']['template'] = $pd_current['template'];
	$pth['folder']['template'] = $pth['folder']['templates'].$cf['site']['template'].'/';
	$pth['file']['template'] = $pth['folder']['template'].'template.htm';
	$pth['file']['stylesheet'] = $pth['folder']['template'].'stylesheet.css';
	$pth['folder']['menubuttons'] = $pth['folder']['template'].'menu/';
	$pth['folder']['templateimages'] = $pth['folder']['template'].'images/';
}

/**
 * Overwrite CMsimple by page-parameters
 * but only if not in edit-mode
 */
if(!$edit && $pd_current){
	if($pd_current['show_heading'] == '1'){
		$temp = '/(<h[1-'.$cf['menu']['levels'].'].*>).+(<\/h[1-'.$cf['menu']['levels'].']>)/isU';
		if(trim($pd_current['heading']) == ''){
			$c[$pd_s] = preg_replace($temp, '', $c[$pd_s]);
		}else{
			$c[$pd_s] = preg_replace($temp, '\\1 '.(string)$pd_current['heading'].'\\2',$c[$pd_s]);
		}
	}
	if($pd_current['show_last_edit'] == '1' && $pd_current['last_edit'] !== ''){
		$c[$pd_s] .= '<div id = "pp_last_update">' . $plugin_tx['page_params']['last_edit']
                  .  ' ' . date($tx['lastupdate']['dateformat'], $pd_current['last_edit']) . '</div>';
	}
}

/**
 * Add a CMSimple-hide if page needs to be
 * viewed eg. in Template as a newsbox.
 * (page-parameter 'linked_to_menu'=0)
 * If page is unpublished ('published'=0)
 * content of this page will be overwritten
 * by CMSimple-hide.
 */
if(!$adm OR ($adm && !$edit))
{
	$pages = $pd_router -> find_all();
	foreach($pages as $key => $values)
	{
		if($values['use_header_location'] == '1' AND trim($values['header_location']) !== '')
		{
			if(!preg_match('"(http|https|ftp|mailto):"si', $values['header_location']))
			{
				$values['header_location'] = 'http://'.$_SERVER['HTTP_HOST'].$sn.$values['header_location'];
			}
			if ($key == $s)
			{
			$c[$key] = '#CMSimple header("Location:'. $values['header_location'] .'"); exit; #';
			}
		}
		if($values['linked_to_menu'] == '0')
		{
			$c[$key] = '#CMSimple hide#' . $c[$key];
		}
		//changed by simplesolutions
		else if(!pd_published($values)) {
			$c[$key] = '#CMSimple hide#';
		}
	}
}

/************************************************************************
* extention by simpleSolutions
*************************************************************************/
function pd_published($pd_page)
{
	global $plugin_tx;
	
	if ($pd_page['published'] == '0') {
		return false;
	}
	$publication_date=isset($pd_page['publication_date'])?trim($pd_page['publication_date']):"";
	$expires= isset($pd_page['expires'])?trim($pd_page['expires']):"";
	if (($expires != "") || ($publication_date!="")) {
		$timezone=(intval($plugin_tx['page_params']['time_zone']) + intval(date("I")))*60*60;	//GTM time correction and day saving time 
		$current = intval(strtotime(gmstrftime ("%Y-%m-%d %H:%M")))+$timezone;	
		if (!defined('PHP_INT_MAX'))
			define('PHP_INT_MAX',intval('9223372036854775807'));
		$int_publication_date= ($publication_date!="") ? strtotime($publication_date) : 0;
		$int_expiration_date=  ($expires!="") ? strtotime($expires) : PHP_INT_MAX;
		if (!($current>$int_publication_date && $current<$int_expiration_date)) {
			return false;	
		}
	}
	return true;
}
?>
