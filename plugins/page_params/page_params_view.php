<?php
/* utf8-marker = äöüß */
/**
 * Page-Parameters - module page_params_view
 *
 * Creates the menu for the user to change
 * page-parameters per page.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @version $Id$
 * @package pluginloader
 * @subpackage page_params
 */

/**
 * page_params_view()
 *
 * @param array $page Gets cleaned of unallowed
 * doublequotes, that will destroy input-fields
 * @return string $view Returns the created view
 * @global string $sn Scriptname (base-directory)
 * @global string $su Selected-URL (query-string)
 * @global array $plugin_tx Plugin-texts
 * @global array $tx CMSimple-texts
 * @global array $pth CMSimple-pathes
 * @global array $cf CMsimple-config-settings
 */
function page_params_view($page){
	global $sn, $su, $plugin_tx, $tx, $pth, $cf;

	$strChecked = ' checked';
	$strDisabled = ' disabled';
	$strSelected = ' selected';
	if ($cf['xhtml']['endtags'] == 'true'){
		$strChecked .= '="checked"';
		$strDisabled .= '="disabled"';
		$strSelected .= '="selected"';
	}

	$lang = $plugin_tx['page_params'];

	$view ="\n".'<form action="'.$sn.'?'.$su.'" method="post" id = "page_params" name = "page_params">';
	$view .= "\n\t".'<p><b>'.$lang['form_title'].'</b></p>';

######## heading ##########################
	$view .= "\n\t".XH_helpIcon($lang['hint_heading']);
	$view .= "\n\t".'<span class = "pp_label">'.$lang['heading'].'</span>'.tag('br');


	$checked = '';
	if($page['show_heading'] == '1'){
		$checked = $strChecked;
	}
	$js = " onclick=\"window.document.page_params.heading.disabled = false;\"";

	$view .= "\n\t\t".tag('input type="radio" name="show_heading" value="1" id = "show_heading_yes"'.$js. $checked). '<label for="show_heading_yes">'.$lang['yes'].'</label>';
	$checked = '';
	$disabled = '';
	if($page['show_heading'] !== '1'){
		$checked = $strChecked;
		$disabled = $strDisabled;
	}
	$js = " onclick=\"window.document.page_params.heading.disabled = true;\"";
    $view .= "\n\t\t".tag('input type="radio" name="show_heading" value="0" id = "show_heading_no"'.$checked .$js). '<label for="show_heading_no">'.$lang['no'].'</label>' .tag('br');
	$view .= "\n\t\t".tag('input type="text" size = "50" name="heading" id = "other_heading" value="'. str_replace('"', '&quot;', $page['heading']).'"'.$disabled).tag('br');
	$view .= "\n\t".tag('hr');

#### published #####################
	$view .= "\n\t".XH_helpIcon($lang['hint_published']);
	$view .= "\n\t".'<span class = "pp_label">'.$lang['published'] .'</span>'.tag('br');

	$checked = '';
	if($page['published'] == '1'){
		$checked = $strChecked;
		$disabled = "";
	}
//publication period
	$hjs.='<script type="text/javascript">
	/* <![CDATA[ */

	function page_params_date_check(field){
		var datearr=field.value.split(" ");
		if (datearr[0]=="" || datearr[0]==undefined)
			datearr[0]="2099-12-31";
		if (datearr[1]=="" || datearr[1]==undefined)
			datearr[1]="00:00";
		var dateformat = /^\d{4}[-](0?[1-9]|1[012])[-](0?[1-9]|[12][0-9]|3[01])$/
		var timeformat = /^([01]?[0-9]|2[0-3)])[:]([0-5]?[0-9])$/
	 	if (dateformat.test(datearr[0]) && timeformat.test(datearr[1]) ) {
	 		field.style.backgroundColor ="#FFFFFF";
		}
		else {
			field.style.backgroundColor ="#FFE4E1";
		 	alert("Date must be in format \"yyyy-d-m [h:m]\" i.e 2099-12-31 or 2099-12-31 23:59");
		}
	}
	/* ]]> */
	</script>';

	$js = " onclick=\"window.document.page_params.expires.disabled = false; window.document.page_params.publication_date.disabled = false; \"";
	$view .= "\n\t\t".tag('input type="radio" name="published" value="1" id = "published_yes"'.$checked. $js).'<label for="published_yes">'.$lang['yes'].'</label>';
	$checked = '';
	if($page['published'] == '0'){
		$checked = $strChecked;
		$disabled = $strDisabled;
	}
	$js = " onclick=\"window.document.page_params.expires.disabled = true; ";
	$js .= " window.document.page_params.publication_date.disabled = true; \"";
  $view .= "\n\t\t".tag('input type="radio" name="published" value="0" id = "published_no"'.$checked. $js). '<label for="published_no">'.$lang['no'].'</label>' .tag('br');
  $view .= "\n\t".'<a class="pl_tooltip" href="#">'.$help_icon.'<span>'.$lang['hint_publication_period'].'</span></a>';
 	$view .= "\n\t\t".$plugin_tx['page_params']['publication_period'];

	$view .= tag('input type="text" size="16" maxlength="16" name="publication_date" id = "publication_date" value="'. $page['publication_date'].'"'.$disabled.' onchange="page_params_date_check(window.document.page_params.publication_date)"');
	$view .= 	" - ";
	$view .= tag('input type="text" size="16" maxlength="16" name="expires" id = "expires" value="'. $page['expires'].'"'.$disabled.' onchange="page_params_date_check(window.document.page_params.expires)"');

	$view .= tag('br');
	//publication period end
  $view .= "\n\t".tag('hr');

#### linked to menu #####################
	$view .= "\n\t".XH_helpIcon($lang['hint_linked_to_menu']);
	$view .= "\n\t".'<span class = "pp_label">'.$lang['linked_to_menu'] .'</span>'.tag('br');

	$checked = '';
	if($page['linked_to_menu'] !== '0'){
		$checked = $strChecked;
	}
	$view .= "\n\t\t".tag('input type="radio" name="linked_to_menu" value="1" id = "linked_to_menu_yes"'.$checked).'<label for="linked_to_menu_yes">'.$lang['yes'].'</label>';
	$checked = '';
	if($page['linked_to_menu'] == '0'){
		$checked = $strChecked;
	}
    $view .= "\n\t\t".tag('input type="radio" name="linked_to_menu" value="0" id = "linked_to_menu_no"'.$checked). '<label for="linked_to_menu_no">'.$lang['no'].'</label>' .tag('br');
    $view .= "\n\t".tag('hr');

### template chooser ############
	if(isset($page['template']) && trim($page['template']) !== '') {
		$selected = '';
		$template = $page['template'];
	} else {
		$selected = $strSelected;
	}
	$handle = opendir($pth['folder']['templates']);

        $templates_select = "\n".'<select name="template">';
	$templates_select .= "\n\t".'<option value="0"'. $selected.'>'.$plugin_tx['page_params']['use_default_template'].'</option>';
	$templates = array();
	while(false !== ($file = readdir($handle))) {   // einsammeln
		if(is_dir($pth['folder']['templates'].$file) && strpos($file, '.') !== 0) {
			$templates[] = $file;
			}
		}
	natcasesort($templates);                         // sortieren
	foreach($templates as $file){                   // options schreiben
		$selected = '';
		if(isset($template) && $file == $template) {$selected = $strSelected;  }
		$templates_select .= "\n\t".'<option value="'.$file.'"'. $selected.'>'.$file.'</option>';
        }
	$templates_select .= "\n".'</select>';
	$view .= "\n"."\n\t".XH_helpIcon($lang['hint_template']);
	$view .= "\n\t".'<span class="pp_label">'.$lang['template'].'</span>'.tag('br').$templates_select .tag('br');
	$view .= "\n\t".tag('hr');

############# last edit ##################
	$view .= "\n\t".XH_helpIcon($lang['hint_last_edit']);
	$view .= "\n\t".'<span class="pp_label">'.$lang['show_last_edit'].'</span>';
	$view .=  tag('br');
	$checked = '';
	if($page['show_last_edit'] == '1'){
		$checked = $strChecked;
	}
	$view .= "\n\t\t".tag('input type="radio" name="show_last_edit" value="1" id = "last_edit_yes"'.$checked). '<label for="last_edit_yes">'.$lang['yes'].'</label>';
	$checked = '';
	if($page['show_last_edit'] !== '1'){
		$checked = $strChecked;
	}
    $view .= "\n\t\t".tag('input type="radio" name="show_last_edit" value="0" id="last_edit_no"'.$checked). '<label for="last_edit_no">'.$lang['no'].'</label>' .tag('br');

	if($page['last_edit'] !== ''){
		$view .= "\n\t\t" . '&nbsp;&nbsp;('.$lang['last_edit'] . ' ';
		$view .=  date($tx['lastupdate']['dateformat'],(int)$page['last_edit']).')';
	}
	$view .= "\n\t".tag('hr');

############# header_location ##################
	$view .= "\n\t".XH_helpIcon($lang['hint_header_location']);
	$view .= "\n\t".'<span class = "pp_label">'.$lang['header_location'].'</span>'.tag('br');

	$checked = '';
	if($page['use_header_location'] == '1'){
		$checked = $strChecked;
	}
	$js = " onclick=\"window.document.page_params.header_location.disabled = false;\"";

	$view .= "\n\t\t".tag('input type="radio" name="use_header_location" value="1" id="header_location_yes"'.$js. $checked). '<label for="header_location_yes">'.$lang['yes'].'</label>';
	$checked = '';
	$disabled = '';
	if($page['use_header_location'] !== '1'){
		$checked = $strChecked;
		$disabled = $strDisabled;
	}
	$js = " onclick=\"window.document.page_params.header_location.disabled = true;\"";
    $view .= "\n\t\t".tag('input type="radio" name="use_header_location" value="0" id="header_location_no"'.$checked .$js). '<label for="header_location_no">'.$lang['no'].'</label>' .tag('br');
	$view .= "\n\t\t".tag('input type="text" size="50" name="header_location" id="other_header_location" value="'. str_replace('"', '&quot;', $page['header_location']).'"'.$disabled).tag('br');
	$view .= "\n\t";
################################################

	$view .= "\n\t".tag('input name = "save_page_data" type = "hidden"');
	$view .= "\n\t".'<div style="text-align: right">';
	$view .= "\n\t\t".tag('input type="submit" value="'.$lang['submit'].'"').tag('br');
	$view .= "\n\t".'</div>';
	$view .= "\n".'</form>';
	return $view;
}
?>
