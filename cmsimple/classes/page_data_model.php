<?php
/* utf8-marker = äöüß */
/**
 * Page-Data - Module page_data_model
 * Part of the Pluginloader of $CMSIMPLE_XH_VERSION$
 *
 * Handles the page-data-array including
 * read and write of the files.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @version $Id: page_data_model.php 315 2012-10-31 00:09:01Z cmb69 $
 * @package pluginloader
 * @subpackage page_data
 */

/**
 * PL_Page_Data_Model
 * 
 * @access public
 */
class PL_Page_Data_Model{

	var $data, $params, $tabs, $original_headings, $temp_data;

	/**
	 * PL_Page_Data_Model::PL_Page_Data_Model()
	 * 
	 * @param mixed $h CMSimple's headings-array
	 * @return
	 */
	function PL_Page_Data_Model($h, $contentHead)
	{
		global $pth, $c;
		
		$this -> headings = $h;
		if (preg_match('/<\?php(.*?)\?>/isu', $contentHead, $m)) {
			eval($m[1]);
		}
		$this -> params = isset($page_data_fields)
			? $page_data_fields
			: array('url', 'last_edit');
		$this -> temp_data = isset($temp_data) ? $temp_data : array();
		$page_data = array();
		foreach ($c as $i => $j) {
			if (preg_match('/<\?php(.*?)\?>/is', $j, $m)) {
				eval($m[1]);
				$c[$i] = preg_replace('/<\?php.*?\?>/is', '', $j);
			}
		}
		$this -> data = $page_data;
		$this -> read();
	}
	
	/**
	 * PL_Page_Data_Model::read()
	 * 
	 * @return
	 */
	function read(){
		foreach($this -> headings as $id => $value){
			foreach($this -> params as $param){
				if(!isset($this -> data[$id][$param])){
					switch ($param) {
					case 'url':
						$this -> data[$id][$param] = uenc(strip_tags($value));
						break;
					default:
						$this -> data[$id][$param] = '';
					}
				}
			}
		}
	}

	/**
	 * PL_Page_Data_Model::refresh()
	 * 
	 * @param mixed $data
	 * @return
	 */
	function refresh($data = null){
		if(isset($data)){
			$this -> data = $data;
			$this -> save();
			return true;
		}
		return false;
	}

	/**
	 * PL_Page_Data_Model::add_param()
	 * 
	 * @param mixed $field
	 * @return
	 */
	function add_param($field){
		$this -> params[] = $field;
		$this -> save();
	}

	/**
	 * PL_Page_Data_Model::add_tab()
	 * 
	 * @param mixed $title
	 * @param mixed $view_file
	 * @return
	 */
	function add_tab($title, $view_file){
		$this -> tabs[$title] = $view_file;
	}

	/**
	 * PL_Page_Data_Model::find_key()
	 * 
	 * @param mixed $key
	 * @return
	 */
	function find_key($key){
		return $key>=0 ? $this->data[$key] : NULL;
	}

	/**
	 * PL_Page_Data_Model::find_field_value()
	 * 
	 * @param mixed $field
	 * @param mixed $value
	 * @return array $results
	 */
	function find_field_value($field, $value){
		$results = array();
		foreach($this->data as $id => $page){
			if(strstr($page[$field],$value)){
				$results[$id] = $page;
			}
		}
		return $results;
	}

	/**
	 * PL_Page_Data_Model::find_arrayfield_value()
	 * 
	 * @param mixed $field
	 * @param mixed $value
	 * @param mixed $separator
	 * @return array $results
	 */
	function find_arrayfield_value($field, $value, $separator){
		$results = array();
		foreach($this->data as $id => $page){
			$array = explode($separator, $page[$field]);
				
			foreach($array as $page_data){
				if($value == trim($page_data)){
					$results[$id] = $page;
				}
			}
		}
		return $results;
	}

	/**
	 * PL_Page_Data_Model::find_field_value_sortkey()
	 * 
	 * @param mixed $field
	 * @param mixed $value
	 * @param mixed $sort_key
	 * @param mixed $sort_flag
	 * @param mixed $separator
	 * @return
	 */
	function find_field_value_sortkey($field, $value, $sort_key, $sort_flag, $separator){
		if($separator){
			$results = $this -> find_arrayfield_value($field, $value, $separator);
		} else {
			$results = $this -> find_field_value($field, $value);
		}
		foreach($results as $key => $value) {
			$temp[] = $value[$sort_key];
			$ids[] = $key;
		}
		array_multisort($temp, $sort_flag, $ids);
		$results = array();
		if(is_array($ids) && count($ids) > 0){
			foreach($ids as $id){

				$results[$id] = $this -> data[$id];
			}
		}
		return $results;
	}


	/**
	 * PL_Page_Data_Model::create()
	 * 
	 * @param mixed $params
	 * @return
	 */
	function create($params = null){
		$clean = array();
		foreach($this -> params as $field){
			$clean[$field] = '';
		}
		$page = array_merge($clean, $params);
		return $page;
	}

	/**
	 * PL_Page_Data_Model::replace()
	 * 
	 * @param mixed $pages
	 * @param mixed $index
	 * @return
	 */
	function replace($pages, $index){
		array_splice($this -> data, $index, 1, $pages);
		$this -> save();
	}


	/**
	 * PL_Page_Data_Model::store_temp()
	 * 
	 * @param mixed $page
	 * @return
	 */
	function store_temp($page){
		foreach($page as $field => $value){
			if(in_array($field, $this -> params)){
				$this->temp_data[$field] = $value;
			}
		}
	}

	/**
	 * PL_Page_Data_Model::delete()
	 * 
	 * @param mixed $key
	 * @return
	 */
	function delete($key){
		array_splice($this -> data, $key, 1);
		$this -> save();
	}

	/**
	 * PL_Page_Data_Model::update_key()
	 * 
	 * @param mixed $key
	 * @param mixed $params
	 * @return
	 */
	function update_key($key, $params){
		foreach($params as $field => $value){
			$this->data[$key][$field] = $value;
		}
		$this->save();
	}

	/**
	 * PL_Page_Data_Model::save()
	 * 
	 * @return
	 */
	function save(){
		XH_saveContents();
	}
}
?>