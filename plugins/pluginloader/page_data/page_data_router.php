<?php
/* utf8-marker = äöüß */
/**
 * Page-Data - Module page_data_router
 * Part of the Pluginloader V.2.1.x
 *
 * Handles all the data that has to be
 * collected to generate tha page-data-array.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @version 1.0.00
 * @package pluginloader
 * @subpackage page_data
 */

/**
 * PL_Page_Data_Router
 *
 * @access public
 */
class PL_Page_Data_Router{
	var $model, $current_page;

	/**
	 * PL_Page_Data_Router::PL_Page_Data_Router()
	 *
	 * @param mixed $data_file
	 * @param mixed $h
	 * @return
	 */
	function PL_Page_Data_Router($data_file, $h){
		$this -> model = new PL_Page_Data_Model($h);

	}

	/**
	 * PL_Page_Data_Router::add_interest()
	 *
	 * @param mixed $field
	 * @return
	 */
	function add_interest($field){
		if(!in_array($field, $this -> model -> params)){
			$this -> model -> add_param($field);
		}
	}

	/**
	 * PL_Page_Data_Router::add_tab()
	 *
	 * @param mixed $tab_name
	 * @param mixed $tab_view
	 * @return
	 */
	function add_tab($tab_name, $tab_view){
		$this -> model -> add_tab($tab_name, $tab_view);
	}

	/**
	 * PL_Page_Data_Router::find_page()
	 *
	 * @param mixed $id
	 * @return
	 */
	function find_page($id){
		$page = $this -> model -> find_key($id);
		return $page;
	}

	/**
	 * PL_Page_Data_Router::find_all()
	 *
	 * @return
	 */
	function find_all(){
		return $this->model->data;
	}

	/**
	 * PL_Page_Data_Router::new_page()
	 *
	 * @param mixed $params
	 * @return
	 */
	function new_page($params = null){
		$page = $this -> model -> create($params);
		return $page;
	}

	/**
	 * PL_Page_Data_Router::insert()
	 *
	 * @param mixed $pages
	 * @param mixed $index
	 * @return
	 */
	function insert($pages, $index){
		$this -> model -> replace($pages, $index);
	}

	/**
	 * PL_Page_Data_Router::destroy()
	 *
	 * @param mixed $key
	 * @return
	 */
	function destroy($key){

		$this -> model -> delete($key);
	}

	/**
	 * Returns an array of all pages containing $value in $field.
	 * If $separator is given the $field will be translated to an array
	 *  - explode($separator, $value) - before the search.
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $separator
	 * @return array
	 */
	function find_field_value($field, $value, $separator = null){
		if($separator){
			$results = $this -> model -> find_arrayfield_value($field, $value, $separator);
			return $results;
		}
		$results = $this -> model -> find_field_value($field, $value);
		return $results;
	}

	/**
	 * PL_Page_Data_Router::find_field_value_sortkey()
	 *
	 * @param mixed $field
	 * @param mixed $value
	 * @param mixed $sort_key
	 * @param mixed $sort_flag
	 * @param mixed $separator
	 * @return
	 */
	function find_field_value_sortkey($field, $value, $sort_key, $sort_flag = null, $separator = null){
		$results = $this -> model -> find_field_value_sortkey($field, $value, $sort_key, $sort_flag, $separator);
		return $results;
	}

	/**
	 * PL_Page_Data_Router::refresh_from_texteditor()
	 *
	 * @param mixed $headings
	 * @param mixed $index
	 * @return
	 */
	function refresh_from_texteditor($headings, $index){
		if(count($headings) == 0){
			/**
			 * Current page has been deleted:
			 * Store it temporary, maybe the user
			 * wants to paste it in somewhere again,
			 * and remove it from the page infos
			 */
			$this -> keep_in_mind($index);
			$this -> destroy($index);
		}
		if(count($headings) > 1){
			/**
			 * At least one page was inserted:
			 * Create an array of the new pages
			 * and insert it into the page data
			 */
			$new_pages = array();
			$current_page = $this -> find_page($index);
			foreach($headings as $key => $heading){
				$url = preg_replace('/\s+/isu', ' ', trim(strip_tags($heading)));
				$url = uenc($url);

				switch ($url) {
					case $current_page['url']:
						/**
						 * Keeping the current page data:
						 * this attempt fails, if NEW pages are
						 * added AND current heading was CHANGED
						 */
						foreach($current_page as $field => $value){
							$params[$field] = $value;
						}
						break;
					case $this -> model -> temp_data['url']:
						/**
						 * This is the 'url' of the recently deleted
						 * page. Most probably it was just pasted in
						 * again. So don't be shy, get the old infos
						 * for this new page
						 */
						foreach($this -> model -> temp_data as $field => $value){
							$params[$field] = $value;
						}
						break;
					default:
						/**
						 * The 'url' is used for ... look right above
						 */
						$params['url'] = $url;
						break;
				}
				$params['last_edit'] = time();
				$new_pages[] = $params;
				$params = array();
			}
			$this -> model -> replace($new_pages, $index);
		}
		if(count($headings) == 1){
			/**
			 * The heading may have changed, stay up to date.
			 */
			$url = preg_replace('/\s+/isu', ' ', trim(strip_tags($headings[0])));
			$params['url'] = uenc($url);
			$params['last_edit'] = time();
			$this -> update($index, $params);
		}
	}

	/**
	 * PL_Page_Data_Router::refresh_from_menu_manager()
	 *
	 * @param mixed $changes
	 * @return
	 */
	function refresh_from_menu_manager($changes){
		$changes = explode(',', $changes);
		/**
		 * Create an up-to-date page data array ...
		 */
		$new_data = array();
		/**
		 * index counter is needed for changed headings
		 */
		$i = 0;
		foreach($changes as $temp){
			$infos = explode('^', $temp);
			$old_position = $infos[0];
			if($old_position == 'New'){
				/**
				 * Page was added: create a new record
				 * These informations are created by default
				 */
				$params = array();
				$title = trim(strip_tags($infos[2]));
				$url = uenc(strip_tags($title));
				$params['url'] = $url;
				$new_data[] = $this -> new_page($params);
			} else{
				/**
				 * Get the old record
				 */
				$new_data[] = $this -> find_page($old_position);
			}
			if(isset($infos[3])){
				/**
				 * if the heading has changed:
				 * update 'url'
				 */
				$url = uenc(trim(strip_tags($infos[3])));
				$new_data[$i]['url'] = $url;
			}
			$i++;
		}
		/**
		 * Replace the old data with the new array
		 */
		$this -> model -> refresh($new_data);
	}

	/**
	 * PL_Page_Data_Router::update()
	 *
	 * @param mixed $s
	 * @param mixed $params
	 * @return
	 */
	function update($s, $params){
		$update_params = array();
		foreach($params as $field => $update){
			if(in_array($field, $this -> model -> params)){
				$update_params[$field] = $update;
			}
		}
		$this -> model -> update_key($s, $params);
	}

	/**
	 * PL_Page_Data_Router::edit()
	 *
	 * @param mixed $pd_s
	 * @return
	 */
	function edit($pd_s){
		$page = $this -> find_page($pd_s);
		$view_provider = new PL_Page_Data_View($page, $this -> model -> tabs);
		return $view_provider->edit_view($page);
	}

	/**
	 * PL_Page_Data_Router::create_tabs()
	 *
	 * @param mixed $s
	 * @return string Returns views of installed plugins
	 */
	function create_tabs($s){
		global $edit, $f, $o, $su;
		if(is_array($this -> model -> tabs) && count($this -> model ->tabs) > 0 && $edit == true){
			if ($s == -1 && !$f && $o == '' && $su == ''){
				$pd_s = 0;
			} else {
				$pd_s = $s;
			}
			$page = $this -> find_page($pd_s);
			if($pd_s > -1){
			$view_provider = new PL_Page_Data_View($page, $this -> model -> tabs);
			return $view_provider->pd_forms();
			}
		}
		return '';
	}

	/**
	 * PL_Page_Data_Router::keep_in_mind()
	 *
	 * @param mixed $pd_s
	 * @return
	 */
	function keep_in_mind($pd_s){
		$page = $this->find_page($pd_s);
		$this -> model -> store_temp($page);
	}

	/**
	 * PL_Page_Data_Router::check_temp()
	 *
	 * @param mixed $url
	 * @return
	 */
	function check_temp($url){
		$temp = $this -> model -> find_key('temp');
		$test = explode(PL_URI_SEPARATOR, $url);
		if($test[count($test)-1] == $temp['url']){
			$params = array();
			foreach($temp as $field => $value){
				if($field !== 'url'){
					$params[$field] = $value;
				}
			}
			$this -> model -> delete('temp');
			$this -> model -> update_key($url, $params);
			return TRUE;
		}
		return FALSE;
	}
}
?>