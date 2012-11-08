<?php
/* utf8-marker = äöüß */
/**
 * Page-Data - Module page_data_views
 * Part of the Pluginloader of $CMSIMPLE_XH_VERSION$
 *
 * Provides an interface for plugins to
 * handle the page_data.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @version $Id: page_data_views.php 314 2012-10-30 23:43:19Z cmb69 $
 * @package pluginloader
 * @subpackage page_data
 */

/**
 * PL_Page_Data_View
 * 
 * @access public
 */
class PL_Page_Data_View{
	var $page, $tabs;
	/**
	 * PL_Page_Data_View::PL_Page_Data_View()
	 * 
	 * @param mixed $page
	 * @param mixed $tabs
	 * @return
	 */
	function PL_Page_Data_View($page, $tabs = null){
		$this->page = $page;
		$this -> tabs = $tabs;
	}
	
	/**
	 * PL_Page_Data_View::pd_forms()
	 * 
	 * @return string $view Returns created view
	 */
	function pd_forms()
	{
		global $h, $plugin_tx, $sn, $su, $hjs;
		
		$view = "\n". '<div id = "pd_tabs">';
		
		foreach($this -> tabs as $title => $code){
			$view .= "\n\t".'<a class="inactive_tab" id="tab_'.$title.'" onclick="xh.toggleTab(\''.$title.'\');"><span>'.$title.'</span></a>';
		}
		
		$view .= "\n</div>\n".'<div id="pd_views">';

		foreach($this -> tabs as $title => $file){
			$view .= "\n".'<div id="PLTab_'.$title.'" class="inactive_view">'. "\n\t".'<a id="pd_editor_toggle" class="pd_open" onclick="xh.toggleTab(\''.$title.'\');">&nbsp;</a>'; 
			if(file_exists($file)){
				include_once($file);
				$function = explode('.',basename($file));
				$function = $function[0];
				
				$view .= $function($this -> page);
			}
			else {$view .= "Could not find ". $file;}
			$view .= "\n"."</div>\n";
		}
		$view .= "\n".'</div>';
		
		return $view;
	}
}
?>