<?php

/**
 * Backend-functionality of Pagemanager_XH.
 * Copyright (c) 2011 Christoph M. Becker (see license.txt)
 */
 

// utf-8 marker: äöüß

if (!isset($plugin))
    die('Direct access forbidden!');
   

define('PAGEMANAGER_VERSION', '1beta2');


// check requirements
if (version_compare(PHP_VERSION, '4.3.0') < 0)
    $e .= '<li>'.$plugin_tx['pagemanager']['error_phpversion'].'</li>'."\n";
foreach (array('pcre', 'xml') as $ext) { 
    if (!extension_loaded($ext))
	$e .= '<li>'.sprintf($plugin_tx['pagemanager']['error_extension'], $ext).'</li>'."\n";
}
if (!file_exists($pth['folder']['plugins'].'jquery/jquery.inc.php'))
    $e .= '<li>'.$plugin_tx['pagemanager']['error_jquery'].'</li>'."\n";


/**
 * Reads content.htm and sets $pagemanager_h.
 *
 * The function was copied from CMSimple_XH 1.4's cms.php and modified
 * to just set the global $pagemanager_h with the unmodified page titles.
 *
 * @return void
 */
function pagemanager_rfc() {
    global $pth, $tx, $cf, $pagemanager_h;

    $c = array();
    $pagemanager_h = array();
    $u = array();
    $l = array();
    $empty = 0;
    $duplicate = 0;

    $content = file_get_contents($pth['file']['content']);
    $stop = $cf['menu']['levels'];
    $split_token = '#@CMSIMPLE_SPLIT@#';


    $content = preg_split('~</body>~i', $content);
    $content = preg_replace('~<h[1-' . $stop . ']~i', $split_token . '$0', $content[0]);
    $content = explode($split_token, $content);
    array_shift($content);

    foreach ($content as $page) {
        $c[] = $page;
        preg_match('~<h([1-' . $stop . ']).*>(.*)</h~isU', $page, $temp);
        $l[] = $temp[1];
        $temp_h[] = trim(strip_tags($temp[2]));
    }

    $cl = count($c);
    $s = -1;

    if ($cl == 0) {
        $c[] = '<h1>' . $tx['toc']['newpage'] . '</h1>';
        $pagemanager_h[] = trim(strip_tags($tx['toc']['newpage']));
        $u[] = uenc($pagemanager_h[0]);
        $l[] = 1;
        $s = 0;
        return;
    }

    foreach ($temp_h as $i => $pagemanager_heading) {
        $temp = trim(strip_tags($pagemanager_heading));
        if ($temp == '') {
            $empty++;
            $temp = $tx['toc']['empty'] . ' ' . $empty;
        }
        $pagemanager_h[] = $temp;
    }
}


/**
 * Returns plugin version information.
 *
 * @return string
 */
function pagemanager_version() {
    return tag('br').tag('hr').'<p><strong>Pagemanager_XH</strong></p>'.tag('hr')."\n"
	    .'<p>Version: '.PAGEMANAGER_VERSION.tag('br')."\n"
	    .'<p>Pagemanager_XH is powered by '
	    .'<a href="http://www.cmsimple-xh.com/wiki/doku.php/plugins:jquery4cmsimple" target="_blank">'
	    .'jQuery4CMSimple</a>'
	    .' and <a href="http://www.jstree.com/" target="_blank">jsTree</a>.</p>'."\n"
	    .'Copyright &copy; 2011 Christoph M. Becker</p>'."\n"
	    .'<p>This program is free software: you can redistribute it and/or modify'
	    .' it under the terms of the GNU General Public License as published by'
	    .' the Free Software Foundation, either version 3 of the License, or'
	    .' (at your option) any later version.</p>'."\n"
	    .'<p>This program is distributed in the hope that it will be useful,'
	    .' but WITHOUT ANY WARRANTY; without even the implied warranty of'
	    .' MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the'
	    .' GNU General Public License for more details.</p>'."\n"
	    .'<p>You should have received a copy of the GNU General Public License'
	    .' along with this program.  If not, see'
	    .' <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.'."\n";
}


/**
 * Returns the toolbar.
 *
 * @param  string $image_ext  The image extension (.gif or .png).
 * @param  string $save_js    The js code for onclick.
 * @return string	      The (x)html.
 */
function pagemanager_toolbar($image_ext, $save_js) {
    global $pth, $plugin_cf, $plugin_tx, $tx;
    
    $imgdir = $pth['folder']['plugins'].'pagemanager/images/';
    $horizontal = strtoupper($plugin_cf['pagemanager']['toolbar_vertical']) != "YES";
    $res = '<div id="pagemanager-toolbar" class="'.($horizontal ? 'horizontal' : 'vertical').'">'."\n";
    $toolbar = array('save', 'separator', 'expand', 'collapse', 'separator', 'create',
	    'create_after', 'rename', 'delete', 'separator', 'cut', 'copy',
	    'paste', 'paste_after', 'separator', 'help');
    foreach ($toolbar as $tool) {
	$link = ($tool != 'help' ? 'href="#"'
		: 'href="'.$pth['file']['plugin_help'].'" target="_blank"');
	$img = $imgdir.$tool.($tool != 'separator' || !$horizontal ? '' : '_v').$image_ext;
	$class = $tool == 'separator' ? 'separator' : 'tool';
	$res .= ($tool != 'separator' ? '<a '.$link.' class="pl_tooltip">' : '')
		.tag('img class="'.$class.'" src="'.$img.'"'
		    .($tool != 'help' ? ' onclick="pagemanager_do(\''.$tool.'\'); return false;"' : ''))
		.($tool != 'separator'
		    ? '<span>'.($tool == 'save' ? ucfirst($tx['action']['save'])
			    : $plugin_tx['pagemanager']['op_'.$tool]).'</span></a>'
		    : '')
		.($horizontal ? '' : tag('br'))."\n";
    }
    $res .= '</div>'."\n";
    return $res;
}


/**
 * Instanciate the pagemanager.js template.
 *
 * @param  string $image_ext  The image extension (.gif or .png).
 * @return string  	      The (x)html.
 */
function pagemanager_instanciateJS($image_ext) {
    global $pth, $plugin_cf, $plugin_tx, $cf, $tx;
    
    $js = rf($pth['folder']['plugins'].'pagemanager/pagemanager.js');
    
    preg_match_all('/<<<PC_(.*)>>>/', $js, $options);
    foreach ($options[1] as $opt) {
	$pagemanager_cf[$opt] = addcslashes($plugin_cf['pagemanager'][$opt],
		"\0'\"\\\f\n\r\t\v");
    }
    preg_match_all('/<<<PT_(.*)>>>/', $js, $options);
    foreach ($options[1] as $opt)
	$pagemanager_tx[$opt] = addcslashes($plugin_tx['pagemanager'][$opt],
		"\0'\"\\\f\n\r\t\v");
    
    $js = preg_replace('/<<<PC_(.*)>>>/e', '$pagemanager_cf["$1"]', $js);
    $js = preg_replace('/<<<PT_(.*)>>>/e', '$pagemanager_tx["$1"]', $js);
    $js = str_replace('<<<MENU_LEVELS>>>', $cf['menu']['levels'], $js);
    $js = str_replace('<<<TOC_DUPL>>>', $tx['toc']['dupl'], $js);
    $js = str_replace('<<<IMAGE_EXT>>>', $image_ext, $js);
    $js = str_replace('<<<IMAGE_DIR>>>', $pth['folder']['plugins'].'pagemanager/images/', $js);
    
    return '<!-- initialize jstree -->'."\n"
	    .'<script type="text/javascript">'."\n"
	    .'/* <![CDATA[ */'.$js.'/* ]]> */'."\n"
	    .'</script>'."\n";
}


/**
 * Emits the page administration (X)HTML.
 *
 * @return void
 */
function pagemanager_edit() {
    global $hjs, $pth, $o, $sn, $h, $l, $plugin, $plugin_cf, $tx, $plugin_tx,
	$u, $pagemanager_h, $pd_router;

    include_once($pth['folder']['plugins'].'jquery/jquery.inc.php');
    include_jQuery();
    include_jQueryUI();
    include_jQueryPlugin('jsTree', $pth['folder']['plugins']
	    .'pagemanager/jstree/jquery.jstree.js');
    
    $image_ext = (file_exists($pth['folder']['plugins'].'pagemanager/images/help.png'))
	    ? '.png' : '.gif';
    
    pagemanager_rfc();
    
    $bo = '';
    
    $swo = '<div id="pagemanager-structure-warning" class="cmsimplecore_warning"><p>'
	    .$plugin_tx['pagemanager']['error_structure_warning']
	    .'</p><p><a href="javascript:pagemanager_confirmStructureWarning();">'
	    .$plugin_tx['pagemanager']['error_structure_confirmation']
	    .'</a></div>'."\n";
	    
    
    $save_js = 'jQuery(\'#pagemanager-xml\')[0].value ='
	    .' jQuery(\'#pagemanager\').jstree(\'get_xml\', \'nest\', -1,
		new Array(\'id\', \'title\', \'pdattr\'))';
    $bo .= '<form id="pagemanager-form" action="'.$sn.'?&amp;pagemanager&amp;edit" method="post">'."\n";
    $bo .= strtoupper($plugin_cf['pagemanager']['toolbar_show']) != 'NO'
	    ? pagemanager_toolbar($image_ext, $save_js) : '';
    
    // output the treeview of the page structure
    // uses ugly hack to clean up irregular page structure
    $irregular = FALSE;
    $pd = $pd_router->find_page(0);
    
    $bo .= '<!-- page structure -->'."\n"
	    .'<div id="pagemanager" ondblclick="jQuery(\'#pagemanager\').jstree(\'toggle_node\');">'."\n"
    	    .'<ul>'."\n".'<li id="pagemanager-0" title="'.$pagemanager_h[0].'"'
	    .' pdattr="'.($pd[$plugin_cf['pagemanager']['pagedata_attribute']] == ''
		? '1' : $pd[$plugin_cf['pagemanager']['pagedata_attribute']])
	    .'"><a href="#">'.$pagemanager_h[0].'</a>';
    $stack = array();
    for ($i = 1; $i < count($h); $i++) {
	$ldiff = $l[$i] - $l[$i-1];
	if ($ldiff <= 0) { // same level or decreasing
	    $bo .= '</li>'."\n";
	    if ($ldiff != 0 && count($stack) > 0) {
		$jdiff = array_pop($stack);
		if ($jdiff + $ldiff > 0) {
		    array_push($stack, $jdiff + $ldiff);
		    $ldiff = 0;
		} else {
		    $ldiff += $jdiff - 1;
		}
	    }
	    for ($j = $ldiff; $j < 0; $j++)
		$bo .= '</ul></li>'."\n";
	} else { // level increasing
	    if ($ldiff > 1) {
		array_push($stack, $ldiff);
		$irregular = TRUE;
	    }
	    $bo .= "\n".'<ul>'."\n";
	}
	$pd = $pd_router->find_page($i);
	$bo .= '<li id="pagemanager-'.$i.'"'
		.' title="'.$pagemanager_h[$i].'"'
		.' pdattr="'.($pd[$plugin_cf['pagemanager']['pagedata_attribute']] == ''
		    ? '1' : $pd[$plugin_cf['pagemanager']['pagedata_attribute']]).'">'
		.'<a href="#">'.$pagemanager_h[$i].'</a>';
    }
    $bo .= '</ul></div>'."\n";

    if ($irregular)
	$o .= $swo;
    
    $o .= $bo;
    
    $o .= pagemanager_instanciateJS($image_ext);
    
    // HACK?: send 'edit' as query param to prevent the last if clause in
    //		rfc() to insert #CMSimple hide#
    $o .= tag('input type="hidden" name="admin" value=""')."\n"
	    .tag('input type="hidden" name="action" value="plugin_save"')."\n"
	    .tag('input type="hidden" name="xml" id="pagemanager-xml" value=""')."\n"
	    .tag('input id="pagemanager-submit" type="submit" class="submit" value="'
		.ucfirst($tx['action']['save']).'"'
		.' onclick="'.$save_js.'"'
		.' style="'.($irregular ? 'display: none' : '').'"')."\n"
	    .'</form>'."\n"
	    .'<div id="pagemanager-footer">&nbsp;</div>'."\n";
		
    $o .= '<div id="pagemanager-confirmation" title=\''
	    .tag('img src="'.$pth['folder']['plugins'].'pagemanager/images/question'.$image_ext.'"')
	    .'&nbsp;'.$plugin_tx['pagemanager']['message_confirm']
	    .'\'></div>'."\n"
	    .'<div id="pagemanager-alert" title=\''
	    .tag('img src="'.$pth['folder']['plugins'].'pagemanager/images/problem'.$image_ext.'"')
	    .'&nbsp;'.$plugin_tx['pagemanager']['message_information'].'\'></div>'."\n";
}


/**
 * Handles start elements of jsTree's xml result.
 *
 * @return void
 */
function pagemanager_start_element_handler($parser, $name, $attribs) {
    global $o, $pagemanager_state;
    if ($name == 'ITEM') {
	$pagemanager_state['level']++;
	$pagemanager_state['id'] = $attribs['ID'] == ''
		? '' : preg_replace('/(copy_)?pagemanager-([0-9]*)/', '$2', $attribs['ID']);
	$pagemanager_state['title'] = $attribs['TITLE'];
	$pagemanager_state['pdattr'] = $attribs['PDATTR'];
	$pagemanager_state['num']++;
    }
}


/**
 * Handles end elements of jsTree's xml result.
 *
 * @return void
 */
function pagemanager_end_element_handler($parser, $name) {
    global $pagemanager_state;
    if ($name == 'ITEM')
	$pagemanager_state['level']--;
}


/**
 * Handles character data of jsTree's xml result.
 *
 * @return void
 */
function pagemanager_cdata_handler($parser, $data) {
    global $c, $h, $cf, $pagemanager_fp, $pagemanager_state, $pagemanager_pd,
	    $pd_router, $plugin_cf;
    $data = htmlspecialchars($data);
    fwrite($pagemanager_fp, '<h'.$pagemanager_state['level'].'>'.$pagemanager_state['title']
	    .'</h'.$pagemanager_state['level'].'>');
    if (isset($c[$pagemanager_state['id']])) {
	$cnt = $c[$pagemanager_state['id']];
	$cnt = preg_replace('/<h[1-'.$cf['menu']['levels'].'][^>]*>[^>]*<\/h[1-'
		.$cf['menu']['levels'].']>/i', '', $cnt);
	fwrite($pagemanager_fp, rmnl($cnt."\n"));
    } else {
	fwrite($pagemanager_fp, "\n");
    }
    
    if ($pagemanager_state['id'] == '') {
	$pd = $pd_router->new_page(array());
    } else {
	$pd = $pd_router->find_page($pagemanager_state['id']);
    }
    $pd['url'] = uenc($pagemanager_state['title']);
    $pd[$plugin_cf['pagemanager']['pagedata_attribute']] = $pagemanager_state['pdattr'];
    $pagemanager_pd[] = $pd;
}


/**
 * Saves content.htm manually and
 * pagedata.php via $pd_router->refresh_from_menu_manager().
 *
 * @return void
 */
function pagemanager_save($xml) {
    global $pth, $tx, $pd_router, $pagemanager_state, $pagemanager_fp, $pagemanager_pd;
    $pagemanager_pd = array();
    $parser = xml_parser_create('UTF-8');
    xml_set_element_handler($parser, 'pagemanager_start_element_handler',
	    'pagemanager_end_element_handler');
    xml_set_character_data_handler($parser, 'pagemanager_cdata_handler');
    $pagemanager_state['level'] = 0;
    $pagemanager_state['num'] = -1;
    if ($pagemanager_fp = fopen($pth['file']['content'], 'w')) {
	fputs($pagemanager_fp, '<html><head>'.head().'</head><body>'."\n");
	xml_parse($parser, $xml, TRUE);
	fputs($pagemanager_fp, '</body></html>');
	fclose($pagemanager_fp);
	$pd_router->model->refresh($pagemanager_pd);
    } else
	e('cntwriteto', 'content', $pth['file']['content']);
    rfc(); // is neccessary, if relocation fails!
}


/**
 * Plugin administration
 */
if (isset($pagemanager)) {
    initvar('admin');
    initvar('action');
    
    $o .= print_plugin_admin('on');
    
    switch ($admin) {
	case '':
	    if ($action == 'plugin_save') {
		pagemanager_save(stsl($_POST['xml']));
		@header('Location: '.$sn.'?&pagemanager&normal&admin=plugin_main');
	    } else {
		$o .= pagemanager_version();
	    }
	    break;
	case 'plugin_main':
	    pagemanager_edit();
	    break;
	default:
	    $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>
