<?php
/**
 * jQuery for CMSimple
 *
 * @author Holger Irmler
 * @link http://cmsimple.holgerirmler.de
 * @version 1.2 - 2011-06-23
 * @package jQuery
 **/
/* utf8-marker = äöüß */

if($plugin_cf['jquery']['autoload'] == "1"){
	include_once($pth['folder']['plugins'].'jquery/jquery.inc.php');
	include_jQuery();
	include_jQueryUI();
}
?>