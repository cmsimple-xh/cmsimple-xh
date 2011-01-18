/*******************************************************************************************
FCKeditor-Integration for CMSimple
© 2007-2009 Connie Müller-Gödecke, Holger Irmler, Klaus Treichler
This work is licensed under GNU General Public License Version 2 or later (GPL),
********************************************************************************************

Scriptname: fckconfig_cmsimple.js
Version:    1.6
Date:       2009-10-31
Author:     Holger Irmler

********************************************************************************************
This script is the custom configuration file of FCKeditor-Integration for CMSimple.
For easier updates, all changed settings for the integration should applied in this script.

Script location: FCKeditor-folder (same folder as fckconfig.js)

Basic Installation Instructions:
Please look at the shipped readme1st.txt

Changelog: 
Please look at fck4cmsimple_changelog.txt
*******************************************************************************************/

// For easier updates, move all customized configuration files to a special folder
// and add them to the list below
// Remember to clear the browser cache on changes!



// Name and path to your custom EditorAreaCSS file
// Here you can precisely simulate the output of your site inside FCKeditor, including background colors,
// font styles, sizes and your custom CSS definitions
// Remember to comment out / remove the line with the include of your template-stylesheet css in ./cmsimple/fckeditor.php!
// FCKConfig.EditorAreaCSS = '' ;

// ToolbarComboPreviewCSS makes it possible to point the Style and Format toolbar combos to 
// a different CSS, avoiding conflicts with the editor area CSS.
// Example:
// FCKConfig.ToolbarComboPreviewCSS = '/mycssstyles/toolbar.css' ;
// FCKConfig.ToolbarComboPreviewCSS = '' ;

// This option sets the DOCTYPE to be used in the editable area. The actual rendering depends on the value set here.
// For example, to make the editor rendering engine work under the XHTML 1.0 Transitional:
// FCKConfig.DocType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' ;
// As of FCKeditor 2.6.1, if the DocType setting is explicetlly set to the HTML4 doctype, the editor will not produce tags like <br /> but <br> instead. 
// FCKConfig.DocType = '' ;

// Name and path to your custom fckstyles.xml file for the "Style" toolbar:
// Here you can offer a complete set of predefined formatting definitions to the end-user (writer) 
// so the text can be well designed without messing up the HTML
// FCKConfig.StylesXmlPath = '../custom_configurations/custom_fckstyles.xml' ;
// Or you can define a list of custom styles like below
//
// FCKConfig.CustomStyles =
// {
// 	'Red Title'	: { Element : 'h3', Styles : { 'color' : 'Red' } }
// };
// FCKConfig.CustomStyles = '' ;


// FCKConfig.TemplatesXmlPath	= '' ;

FCKConfig.EnableMoreFontColors = true ;
FCKConfig.FontColors = '000000,993300,333300,003300,003366,000080,333399,333333,800000,FF6600,808000,808080,008080,0000FF,666699,808080,FF0000,FF9900,99CC00,339966,33CCCC,3366FF,800080,999999,FF00FF,FFCC00,FFFF00,00FF00,00FFFF,00CCFF,993366,C0C0C0,FF99CC,FFCC99,FFFF99,CCFFCC,CCFFFF,99CCFF,CC99FF,FFFFFF' ;

FCKConfig.FontFormats	= 'p;h1;h2;h3;h4;h5;h6;pre;address;div' ;
FCKConfig.FontNames		= 'Arial;Comic Sans MS;Courier New;Tahoma;Times New Roman;Verdana' ;
FCKConfig.FontSizes		= 'smaller;larger;xx-small;x-small;small;medium;large;x-large;xx-large' ;

// define which Skin you want to use, more skins can be downloaded at: 
// http://sourceforge.net/tracker/?group_id=75348&atid=740153
FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/silver/' ;
// FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/default/' ;
// FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/office2003/' ;

FCKConfig.ProcessHTMLEntities	= false ;
FCKConfig.IncludeLatinEntities	= false ;
FCKConfig.IncludeGreekEntities	= false ;

FCKConfig.ProcessNumericEntities = false ;

FCKConfig.AdditionalNumericEntities = ''  ;		// Single Quote: "'"

FCKConfig.FillEmptyBlocks	= true ;

FCKConfig.FormatSource		= true ;
FCKConfig.FormatOutput		= true ;
FCKConfig.FormatIndentator	= '    ' ;

FCKConfig.EMailProtection = 'none' ; // none | encode | function
FCKConfig.EMailProtectionFunction = 'mt(NAME,DOMAIN,SUBJECT,BODY)' ;

FCKConfig.PluginsPath = FCKConfig.BasePath + 'plugins/' ;
// PlugIns can be activated by commenting / uncommenting them
FCKConfig.Plugins.Add('dragresizetable' );
FCKConfig.Plugins.Add('nbsp','de,en');
FCKConfig.Plugins.Add('tablecommands');
//FCKConfig.Plugins.Add('flvPlayer','en');
FCKConfig.Plugins.Add('sitelink','de,en');
//FCKConfig.Plugins.Add('swfobject', 'en,es');

// Load Toolbars after plugins 
FCKConfig.ToolbarSets["CMSimpleOld"] = [
	['Save','Source','-','Preview','-','Templates','Cut','Copy','Paste','PasteText','PasteWord'],
	['SpellCheck','-','Undo','Redo','Find','Replace','-','SelectAll','RemoveFormat'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript','-','nbsp'],
	['TextColor','BGColor','-','Rule','PageBreak'],
	['OrderedList','UnorderedList','-','Outdent','Indent','CreateDiv','Blockquote'],
	'/',
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-','Blockquote','-','Image','flvPlayer','Flash','-','Link','Unlink','sitelink','Anchor','-','Smiley','SpecialChar','-','FitWindow'],
	'/',
	['Table','-','TableInsertRowAfter','TableDeleteRows','TableDeleteColumns','TableInsertCellAfter','TableDeleteCells','TableMergeCells','TableHorizontalSplitCell','TableCellProp','About'] ,
	'/',	
	['FontFormat','FontName','FontSize','Style']			// No comma for the last row.
] ;

FCKConfig.ToolbarSets["CMSimple"] = [
	['Save','Source','FitWindow','ShowBlocks','-','Preview','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Undo','Redo'],['Find','Replace'],['SelectAll','RemoveFormat'],
	['OrderedList','UnorderedList'],['Outdent','Indent','Blockquote','CreateDiv'],
	['Link','Unlink','sitelink','Anchor'],['TextColor','BGColor'],
	['Image'],['Smiley','SpecialChar','Rule','PageBreak'],
	['Table','-','TableInsertRowAfter','TableDeleteRows','TableDeleteColumns','TableInsertCellAfter','TableDeleteCells','TableMergeCells','TableHorizontalSplitCell','TableCellProp'] ,
	['FontFormat'],['FontName'],['FontSize'],['Style'],
	['About']		// No comma for the last row.
] ;

FCKConfig.ProtectedSource.Add( /<\?[\s\S]*?\?>/g ) ;	// PHP style server side code

FCKConfig.EnterMode = 'p' ;			// p | div | br
FCKConfig.ShiftEnterMode = 'br' ;	// p | div | br

FCKConfig.AutoDetectLanguage	= true ;
FCKConfig.DefaultLanguage		= 'en' ;
FCKConfig.ContentLangDirection	= 'ltr' ;

// The following value defines which File Browser connector and Quick Upload
// "uploader" to use. It is valid for the default implementaion and it is here
// just to make this configuration file cleaner.
// It is not possible to change this value using an external file or even
// inline when creating the editor instance. In that cases you must set the
// values of LinkBrowserURL, ImageBrowserURL and so on.
// Custom implementations should just ignore it.
var _FileBrowserLanguage	= 'php' ;	// asp | aspx | cfm | lasso | perl | php | py
var _QuickUploadLanguage	= 'php' ;	// asp | aspx | cfm | lasso | perl | php | py

// HI: FileBrowser-Settings moved to FCKeditor.php with Version 2.4.0

FCKConfig.LinkUpload 					= false ; //Deactivate QuickUpload-Tab
FCKConfig.LinkUploadURL 				= FCKConfig.BasePath + 'filemanager/connectors/' + _QuickUploadLanguage + '/upload.' + _QuickUploadExtension ;
FCKConfig.LinkUploadAllowedExtensions	= ".(7z|aiff|asf|avi|bmp|csv|doc|fla|flv|gif|gz|gzip|jpeg|jpg|mid|mov|mp3|mp4|mpc|mpeg|mpg|ods|odt|pdf|png|ppt|pxd|qt|ram|rar|rm|rmi|rmvb|rtf|sdc|sitd|swf|sxc|sxw|tar|tgz|tif|tiff|txt|vsd|wav|wma|wmv|xls|xml|zip)$" ;			// empty for all
FCKConfig.LinkUploadDeniedExtensions	= "" ;	// empty for no one

FCKConfig.ImageUpload 					= false ; //Deactivate QuickUpload-Tab
FCKConfig.ImageUploadURL 				= FCKConfig.BasePath + 'filemanager/connectors/' + _QuickUploadLanguage + '/upload.' + _QuickUploadExtension + '?Type=Image' ;
FCKConfig.ImageUploadAllowedExtensions	= ".(jpg|gif|jpeg|png)$" ;		// empty for all
FCKConfig.ImageUploadDeniedExtensions	= "" ;							// empty for no one

FCKConfig.FlashUpload 					= false ; //Deactivate QuickUpload-Tab
FCKConfig.FlashUploadURL 				= FCKConfig.BasePath + 'filemanager/connectors/' + _QuickUploadLanguage + '/upload.' + _QuickUploadExtension + '?Type=Flash' ;
FCKConfig.FlashUploadAllowedExtensions	= ".(swf|flv|mp3)$" ;		// empty for all
FCKConfig.FlashUploadDeniedExtensions	= "" ;					// empty for no one

FCKConfig.SmileyPath					= FCKConfig.BasePath + 'images/smiley/msn/' ;
FCKConfig.SmileyImages					= ['regular_smile.gif','sad_smile.gif','wink_smile.gif','teeth_smile.gif','confused_smile.gif','tounge_smile.gif','embaressed_smile.gif','omg_smile.gif','whatchutalkingabout_smile.gif','angry_smile.gif','angel_smile.gif','shades_smile.gif','devil_smile.gif','cry_smile.gif','lightbulb.gif','thumbs_down.gif','thumbs_up.gif','heart.gif','broken_heart.gif','kiss.gif','envelope.gif'] ;
FCKConfig.SmileyColumns 				= 8 ;
FCKConfig.SmileyWindowWidth				= 320 ;
FCKConfig.SmileyWindowHeight			= 240 ;