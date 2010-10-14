<?php
/* utf8-marker = äöüß */
/**
 * Page-Data - Module page_data_views
 * Part of the Pluginloader V.2.1.x
 *
 * Provides an interface for plugins to
 * handle the page_data.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @version 1.0.04
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
	function pd_forms(){
		global $h, $plugin_tx, $sn, $su, $hjs;
		$hjs .= tag('link rel="stylesheet" href="'.PL_PAGE_DATA_STYLESHEET.'" type="text/css"');
		
		$view = "\n". '<div id = "pd_tabs">';
		
		foreach($this -> tabs as $title => $code){
			$view .= "\n\t".'<a class="inactive_tab" id="tab_'.$title.'" onclick="toggle_tab(\''.$title.'\');"><span>'.$title.'</span></a>';
		}
		
		$view .= "\n</div>\n".'<div id="pd_views">';

		foreach($this -> tabs as $title => $file){
			$view .= "\n".'<div id="PLTab_'.$title.'" class="inactive_view">'. "\n\t".'<a id="pd_editor_toggle" class="pd_open" onclick="toggle_tab(\''.$title.'\');">&nbsp;</a>'; 
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
		
		$hjs .=   "\n".'<script type="text/javascript">
		/* <![CDATA[ */
		function toggle_tab(tabID) {
			var curr_view = document.getElementById("PLTab_" + tabID);
			var curr_tab = document.getElementById("tab_" + tabID);
			if(curr_tab.className == "active_tab") {
				curr_view.className = "inactive_view";
				curr_tab.className = "inactive_tab";
				return;
			}
			var views = document.getElementById("pd_views").getElementsByTagName("div");
			var tabs = document.getElementById("pd_tabs").getElementsByTagName("a");
			for (i = 0; i < views.length; i++) {
				if(views[i].id.substr(0, 6) == "PLTab_") {
					views[i].className = "inactive_view";
				}
			}
			for (i = 0; i < tabs.length; i++) {
				tabs[i].className = "inactive_tab";
			}
			curr_tab.className = "active_tab";
			curr_view.className = "active_view";
			return;
		}
		/* ]]> */'."\n".'</script>'."\n";
		return $view;
	}
}
?>