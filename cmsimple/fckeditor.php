<?php
/* utf8-marker = äöüß */
session_start();
//error_reporting(E_ALL);

/*******************************************************************************************
FCKeditor-Integration for CMSimple
© 2007-2009 Connie Müller-Gödecke, Holger Irmler, Klaus Treichler
This work is licensed under GNU General Public License Version 2 or later (GPL)
********************************************************************************************

Scriptname: fckeditor.php
Version:    2.5.0
Date:       2009-11-04
Author:     Holger Irmler, Klaus Treichler, Martin Damken

********************************************************************************************

This file is the bridge between CMSimple and FCKeditor.
Script location: CMSimple-folder (same folder as cms.php)

Note: The Custom-Configuration-Files must be called 
      relative to ./FCKeditor/editor/fckeditor.html

Basic Installation Instructions:
Please look at the shipped readme1st.txt

Changelog: 
Please look at fck4cmsimple_changelog.txt

*******************************************************************************************/



/*** Configuration: ***********************************************************************
*******************************************************************************************/

// It's recommended to apply the basic css - settings from your template
// to ./FCKeditor/custom_configurations/custom_fck_editorarea.css.
// You can try to use your template-css by setting $Use_Template_Css = true;

$use_Template_Css = true;

/*** No need to change something below this line ******************************************
*******************************************************************************************/



if ((!function_exists('sv')) || eregi('fckeditor.php',sv('PHP_SELF')))die('Access Denied');

$_SESSION["_VALID_FCKeditor"] = "enabled";

//catch some CMSimple-folders & files for use in FCKeditor Configuration
$sl != $cf['language']['default'] ? $repl = $sl . "/index.php" : $repl = "index.php";
$CMSimple_root_folder = str_replace($repl, "", $_SERVER['SCRIPT_NAME']);
//$upload_folder = $CMSimple_root_folder . "images/";
$upload_folder = $CMSimple_root_folder;
$_SESSION["upload_folder"] = $upload_folder;

// HI: 05.09.2009 wird an den Filebrowser übergeben: CMSimple installiert in Unterverzeichnis?
/*if ($upload_folder == "/") {
	$isInSubfolder = 0; 
	}
else {
	$isInSubfolder = 1; 
}
*/

$isInSubfolder = substr_count($upload_folder, '/');

// Catch aktive language for use in Sitelink-Plugin
$_SESSION["lang_active"] = $sl;
// End Sitelink

// Use html or Xhtml depending on CMSimple setting
$DocType = '';
if (!eregi("true", $cf['xhtml']['endtags'])) {
	$DocType = '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">' ;
}

if($cf['fckeditor']['folder']=='')$cf['fckeditor']['folder']='FCKeditor';

if(@is_dir($pth['folder']['base'].$cf['fckeditor']['folder'])){

//template-css
if ($use_Template_Css) {
	//$sl != $cf['language']['default'] ? $t = "../" : $t = "../../";
	//$css = $t.$pth['folder']['template']."stylesheet.css";
	$css = $pth['folder']['template']."stylesheet.css";
	} 
 else {
	$css = $cf['fckeditor']['folder']."/custom_configurations/custom_fck_editorarea.css";
}

######## edit M.D. 2009/07 ###########
######## wird an den Filebowser weitergegeben -> Bild-Links mit './' oder '../'
if($sl == $cf['language']['default']) {
		$isDefaultLanguage = 1;
	} else {$isDefaultLanguage = 0; }
###### end edit ######################


$onload.="init();";
$hjs.='<script type="text/javascript" src="'.$pth['folder']['base'].$cf['fckeditor']['folder'].'/fckeditor.js"></script>

<script type="text/javascript">

function init() {
	
var sBasePath = "'.$pth['folder']['base'].$cf['fckeditor']['folder'].'/" ;
var myBaseHref = "http://'.$_SERVER['HTTP_HOST'].$sn.'" ;
var EditorAreaCss = "'.$css.'";
var FCKDocType = "'.$DocType.'";

var oFCKeditor = new FCKeditor("text","100%",'.$cf['editor']['height'].') ; 
oFCKeditor.BasePath=sBasePath ;
oFCKeditor.Config["BaseHref"] = myBaseHref ;

// Load the custom configuration file
oFCKeditor.Config["CustomConfigurationsPath"] = "../custom_configurations/fckconfig_cmsimple.js" ; 

// Stylesheet of the Editor-Area
oFCKeditor.Config["EditorAreaCSS"] = EditorAreaCss ;

// Select your favourite toolbar
oFCKeditor.ToolbarSet = "CMSimple" ;

// Style-Tab settings
oFCKeditor.Config["StylesXmlPath"] = "../custom_configurations/custom_fckstyles.xml" ;

//predefined templates
oFCKeditor.Config["TemplatesXmlPath"] = "../custom_configurations/custom_fcktemplates.xml" ;

// Set DocType
oFCKeditor.Config["DocType"] = FCKDocType ;

// edit M.D. 2009/07
// Aufruf eines an CMSimple angepassten FileBrowsers: 
//  - Setzt die Bild-Links
//  - kleine Bildvorschau
//  - "Files" mit Ordner "downloads" verlinkt (s.o. Z. 70) 
//  - erzeugt DownloadLink fuer Dateien im Ordner "downloads"
//  - Loeschfunktion fuer Dateien (von bram.us)
//
// Die Pfade sollten direkt in der fckconfig_cmsimple.js gesetzt werden 

//HI 2009-10-07 var connector = "Connector=../../connectors/php/connector.php" ;
var connector = "Connector='.$CMSimple_root_folder.$cf['fckeditor']['folder'].'/editor/filemanager/connectors/php/connector.php" ;
//HI var browserPath = "'.str_replace("/".$sl."/", "/", $sn).$cf['fckeditor']['folder'].'/editor/filemanager/browser/cmsimple/browser.html?" ;
var browserPath = "'.$CMSimple_root_folder.$cf['fckeditor']['folder'].'/editor/filemanager/browser/cmsimple/browser.html?" ;
oFCKeditor.Config["LinkBrowserURL"]	= browserPath + connector + "&defaultLanguage='.$isDefaultLanguage.'" + "&isInSubfolder='.$isInSubfolder.'";
oFCKeditor.Config["ImageBrowserURL"] = browserPath + "Type=Image&" + connector + "&defaultLanguage='.$isDefaultLanguage.'" + "&isInSubfolder='.$isInSubfolder.'";
oFCKeditor.Config["FlashBrowserURL"] = browserPath + "Type=Flash&" + connector + "&defaultLanguage='.$isDefaultLanguage.'" + "&isInSubfolder='.$isInSubfolder.'";
// (Ich hab es gern etwas kleiner)
/*
oFCKeditor.Config["FlashBrowserWindowWidth"] = oFCKeditor.Config["ScreenWidth"] * 0.5 ;
oFCKeditor.Config["FlashBrowserWindowHeight"] = oFCKeditor.Config["ScreenHeight"] * 0.4 ;
oFCKeditor.Config["ImageBrowserWindowWidth"] = oFCKeditor.Config["ScreenWidth"] * 0.5 ;
oFCKeditor.Config["ImageBrowserWindowHeight"] =  oFCKeditor.Config["ScreenHeight"] * 0.4 ;
oFCKeditor.Config["LinkBrowserWindowWidth"] = oFCKeditor.Config["ScreenWidth"] * 0.5 ;
oFCKeditor.Config["LinkBrowserWindowHeight"] = oFCKeditor.Config["ScreenHeight"] * 0.4 ;
*/
// end edit M.D.
oFCKeditor.ReplaceTextarea() ;
}
// edit M.D. 2009/08
// der Rest ist fuer Speichernachfrage
var loadedContent;

function FCKeditor_OnComplete( oFCKeditor ){
    loadedContent = oFCKeditor.GetData(true);
    oFCKeditor.LinkedField.form.onsubmit = doSave;
}

function doSave(){
    oEditor = FCKeditorAPI.GetInstance("text");
    loadedContent = oEditor.GetData(true);
    return true;
}

window.onunload = function (){
  oEditor = FCKeditorAPI.GetInstance("text");
   if(loadedContent != oEditor.GetData(true)){
      if(window.confirm("!!! WARNING !!!\nIf you cancel - your changes will be lost!\n\nSave changes?\n")) {
         oEditor.LinkedField.form.submit();
         }
   }
}
// end edit M.D.
</script>';

$o.=
'<form method="post" id="ta" action="'.$sn.'">'
.tag('input type="hidden" name="selected" value="'.$u[$s].'"')
.tag('input type="hidden" name="function" value="save"')
.'<textarea name="text" id="text" rows="80" cols="80" style="width: 100%;">'
.htmlspecialchars ($c[$s])
.'</textarea></form>';

} else e('cntopen','folder',$cf['fckeditor']['folder']);

?>