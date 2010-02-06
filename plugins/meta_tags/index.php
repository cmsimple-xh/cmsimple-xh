<?php
/**
 * Meta-Tags - main index.php
 * Plugin (V.2.1-compatible)
 *
 * Stores meta-tags (description, keywords,
 * title and robots) per page.
 * index.php is called by pluginloader and 
 * returns (x)html-meta-tags to template.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @version 1.0.00
 * @package pluginloader
 * @subpackage meta_tags
 */
/**
 * Check if PLUGINLOADER is calling and die if not
 */
if(!defined('PLUGINLOADER')) {
	die('Plugin '. basename(dirname(__FILE__)) . ' requires a newer version of the Pluginloader. No direct access.');
}
/**
 * Include language package
 */
include_once "languages/".$sl.'.php';

/**
 * Add used interests to router
 */
$pd_router -> add_interest('description');
$pd_router -> add_interest('keywords');
$pd_router -> add_interest('title');
$pd_router -> add_interest('robots');

/**
 * Add a tab for admin-menu
 */
$pd_router -> add_tab($plugin_tx['meta_tags']['tab'], $pth['folder']['plugins'].'meta_tags/meta_tags_view.php');

/**
 * Set the meta tags contents.
 */
if($pd_current['title']){
		$cf['site']['title'] = $pd_current['title'];
	}
if($pd_current['description']){
	$cf['meta']['description'] = $pd_current['description'];
	}
if($pd_current['keywords']){
	$cf['meta']['keywords'] = $pd_current['keywords'];
	}
if($pd_current['robots']){
		$cf['meta']['robots'] = $pd_current['robots'];
	}
?>