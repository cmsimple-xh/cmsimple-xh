/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2008 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Scripts related to the Flash dialog window (see fck_flash.html).
 */
function Import(aSrc) {
   document.write('<scr'+'ipt type="text/javascript" src="' + aSrc + '"></sc' + 'ript>');
}

var dialog		= window.parent ;
var oEditor		= dialog.InnerDialogLoaded() ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;
var FCKTools	= oEditor.FCKTools ;

Import(FCKConfig.FullBasePath + 'dialog/common/fck_dialog_common.js');

var oListText ;
var oListValue ;

//#### Dialog Tabs

// Set the dialog tabs.
dialog.AddTab( 'Info', oEditor.FCKLang.DlgInfoTab ) ;

if ( FCKConfig.FlashUpload )
	dialog.AddTab( 'Upload', FCKLang.DlgLnkUpload ) ;

if ( !FCKConfig.FlashDlgHideAdvanced )
{
	dialog.AddTab( 'Advanced', oEditor.FCKLang.DlgAdvancedTag ) ;
	dialog.AddTab( 'Flashvars', oEditor.FCKLang.DlgFlashvars ) ;
}

// Function called when a dialog tag is selected.
function OnDialogTabChange( tabCode )
{
	ShowE('divInfo'		, ( tabCode == 'Info' ) ) ;
	ShowE('divUpload'	, ( tabCode == 'Upload' ) ) ;
	ShowE('divAdvanced'	, ( tabCode == 'Advanced' ) ) ;
	ShowE('divFlashvars'	, ( tabCode == 'Flashvars' ) ) ;
}

// Get the selected flash embed (if available).
var oFakeImage = FCK.Selection.GetSelectedElement() ;
var oParsedFlash ;
var oEmbed;

if ( oFakeImage )
{
	if ( oFakeImage.getAttribute( 'SwfObjectNumber' ) )
	{
		oParsedFlash = FCK.SwfobjectHandler.getItem( oFakeImage.getAttribute( 'SwfObjectNumber' ) );
		oParsedFlash.updateDimensions( oFakeImage );
	}
	else
	{
		if ( oFakeImage.tagName == 'IMG' && oFakeImage.getAttribute('_fckflash') )
			oEmbed = FCK.GetRealElement( oFakeImage ) ;

		oFakeImage = null ;
	}
}
if ( !oParsedFlash )
		oParsedFlash = FCK.SwfobjectHandler.createNew() ;

window.onload = function()
{
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	oListText	= document.getElementById( 'cmbText' ) ;
	oListValue	= document.getElementById( 'cmbValue' ) ;

	// Fix the lists widths. (Bug #970)
	oListText.style.width = "120px"; //oListText.offsetWidth ;
	oListValue.style.width = "120px"; //oListValue.offsetWidth ;

	// Load the selected element information (if any).
	LoadSelection() ;

	// Show/Hide the "Browse Server" button.
	GetE('tdBrowse').style.display = FCKConfig.FlashBrowser	? '' : 'none' ;

	// Set the actual uploader URL.
	if ( FCKConfig.FlashUpload )
		GetE('frmUpload').action = FCKConfig.FlashUploadURL ;

	dialog.SetAutoSize( true ) ;

	// Activate the "OK" button.
	dialog.SetOkButton( true ) ;

	if (typeof SelectField == 'function') SelectField( 'txtUrl' ) ;
}

function LoadSelection()
{
	// parse old embeds
	if (oEmbed)
	{
		oParsedFlash.file    = GetAttribute( oEmbed, 'src', '' ) ;
		oParsedFlash.width  = GetAttribute( oEmbed, 'width', '' ) ;
		oParsedFlash.height = GetAttribute( oEmbed, 'height', '' ) ;

		// Get Advances Attributes
		oParsedFlash.attributes.id		= oEmbed.id ;
		oParsedFlash.attributes.title		= oEmbed.title ;

		if ( oEditor.FCKBrowserInfo.IsIE )
		{
			oParsedFlash.attributes['class'] = oEmbed.getAttribute('className') || '' ;
			oParsedFlash.attributes.style = oEmbed.style.cssText ;
		}
		else
		{
			oParsedFlash.attributes['class'] = oEmbed.getAttribute('class',2) || '' ;
			oParsedFlash.attributes.style = oEmbed.getAttribute('style',2) || '' ;
		}

		oParsedFlash.params.play	= GetAttribute( oEmbed, 'play', 'true' ) == 'true' ;
		oParsedFlash.params.loop	= GetAttribute( oEmbed, 'loop', 'true' ) == 'true' ;
		oParsedFlash.params.menu	= GetAttribute( oEmbed, 'menu', 'true' ) == 'true' ;
		oParsedFlash.params.scale	= GetAttribute( oEmbed, 'scale', '' ).toLowerCase() ;
	}

	GetE('txtUrl').value    = oParsedFlash.file ;
	GetE('txtWidth').value  = oParsedFlash.width ;
	GetE('txtHeight').value = oParsedFlash.height ;

	// Get Advances Attributes
	GetE('txtAttId').value		= oParsedFlash.attributes.id || '' ;
	GetE('txtAttTitle').value		= oParsedFlash.attributes.title || '' ;
	GetE('txtAttClasses').value = oParsedFlash.attributes['class'] || '' ;
	GetE('txtAttStyle').value = oParsedFlash.attributes.style ;

	GetE('chkAutoPlay').checked	= oParsedFlash.params.play ;
	GetE('chkLoop').checked		= oParsedFlash.params.loop ;
	GetE('chkMenu').checked		= oParsedFlash.params.menu ;
	GetE('cmbScale').value		= oParsedFlash.params.scale || '' ;

	GetE('allowscriptaccess').value		= oParsedFlash.params.allowscriptaccess || '' ;
	GetE('wmode').value		= oParsedFlash.params.wmode || '' ;
	GetE('allowfullscreen').checked		= oParsedFlash.params.allowfullscreen ;

	// flashvars
	var opts = oParsedFlash.flashvars ;
	for ( var v in opts )
	{
		var sText	= v ;
		var sValue	= opts[v] ;

		AddComboOption( oListText, sText, sText ) ;
		AddComboOption( oListValue, sValue, sValue ) ;
	}

	UpdatePreview() ;
}

//#### The OK button was hit.
function Ok()
{
	if ( GetE('txtUrl').value.length == 0 )
	{
		dialog.SetSelectedTab( 'Info' ) ;
		GetE('txtUrl').focus() ;

		alert( oEditor.FCKLang.DlgAlertUrl ) ;

		return false ;
	}

	oEditor.FCKUndo.SaveUndoStep() ;

	updateObject(oParsedFlash) ;

	if ( !oFakeImage )
		oFakeImage = oParsedFlash.createHtmlElement() ;

	oParsedFlash.updateHTMLElement(oFakeImage);

	return true ;
}

function updateObject( e )
{
	e.file = GetE('txtUrl').value;
	e.width = GetE('txtWidth').value;
	e.height = GetE('txtHeight').value;
	if (e.width=='') e.width = '100';
	if (e.height=='') e.height = '100';

	// Advances Attributes
	e.attributes.id = GetE('txtAttId').value;
	e.attributes['class'] = GetE('txtAttClasses').value;
	e.attributes.style = GetE('txtAttStyle').value;
	e.attributes.title = GetE('txtAttTitle').value;

	e.params.scale = GetE('cmbScale').value;
	e.params.play = GetE('chkAutoPlay').checked;
	e.params.loop = GetE('chkLoop').checked;
	e.params.menu = GetE('chkMenu').checked;

	e.params.allowscriptaccess = GetE('allowscriptaccess').value;
	e.params.wmode = GetE('wmode').value;
	e.params.allowfullscreen = GetE('allowfullscreen').checked;

	// Add all available options.
	var vars = {};
	for ( var i = 0 ; i < oListText.options.length ; i++ )
	{
		var sText	= oListText.options[i].value ;
		var sValue	= oListValue.options[i].value ;
		if ( sValue.length == 0 ) sValue = sText ;
		vars[sText] = sValue ;
	}
	e.flashvars = vars;
}

var ePreview ;

function SetPreviewElement( previewEl )
{
	ePreview = previewEl ;

	if ( GetE('txtUrl').value.length > 0 )
		UpdatePreview() ;
}

function UpdatePreview()
{
	if ( !ePreview )
		return ;

	while ( ePreview.firstChild )
		ePreview.removeChild( ePreview.firstChild ) ;

	if ( GetE('txtUrl').value.length == 0 )
		ePreview.innerHTML = '&nbsp;' ;
	else
	{
		// Skip reloading the swf if it's the current preview
		var url = GetE('txtUrl').value ;
		if (ePreview.dataUrl == url)
			return ;

		// check if it isn't a swf file or some mix of html or an embedding page:
		var oData = oWebVideo.ParseHtml(url, GetE('txtWidth').value, GetE('txtHeight').value );
		GetE('txtUrl').value = oData.url ;
		GetE('txtWidth').value = oData.width ;
		GetE('txtHeight').value = oData.height ;
		ePreview.dataUrl = oData.url ;

		var oDoc	= ePreview.ownerDocument || ePreview.document ;
		var e		= oDoc.createElement( 'EMBED' ) ;

		SetAttribute( e, 'src', GetE('txtUrl').value ) ;
		SetAttribute( e, 'type', 'application/x-shockwave-flash' ) ;
		SetAttribute( e, 'width', '100%' ) ;
		SetAttribute( e, 'height', '100%' ) ;

		ePreview.appendChild( e ) ;
	}
}

// <embed id="ePreview" src="fck_flash/claims.swf" width="100%" height="100%" style="visibility:hidden" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">

function BrowseServer()
{
	OpenFileBrowser( FCKConfig.FlashBrowserURL, FCKConfig.FlashBrowserWindowWidth, FCKConfig.FlashBrowserWindowHeight ) ;
}

function SetUrl( url, width, height )
{
	GetE('txtUrl').value = url ;

	if ( width )
		GetE('txtWidth').value = width ;

	if ( height )
		GetE('txtHeight').value = height ;

	UpdatePreview() ;

	dialog.SetSelectedTab( 'Info' ) ;
}

function OnUploadCompleted( errorNumber, fileUrl, fileName, customMsg )
{
	// Remove animation
	window.parent.Throbber.Hide() ;
	GetE( 'divUpload' ).style.display  = '' ;

	switch ( errorNumber )
	{
		case 0 :	// No errors
			alert( 'Your file has been successfully uploaded' ) ;
			break ;
		case 1 :	// Custom error
			alert( customMsg ) ;
			return ;
		case 101 :	// Custom warning
			alert( customMsg ) ;
			break ;
		case 201 :
			alert( 'A file with the same name is already available. The uploaded file has been renamed to "' + fileName + '"' ) ;
			break ;
		case 202 :
			alert( 'Invalid file type' ) ;
			return ;
		case 203 :
			alert( "Security error. You probably don't have enough permissions to upload. Please check your server." ) ;
			return ;
		case 500 :
			alert( 'The connector is disabled' ) ;
			break ;
		default :
			alert( 'Error on file upload. Error number: ' + errorNumber ) ;
			return ;
	}

	SetUrl( fileUrl ) ;
	GetE('frmUpload').reset() ;
}

var oUploadAllowedExtRegex	= new RegExp( FCKConfig.FlashUploadAllowedExtensions, 'i' ) ;
var oUploadDeniedExtRegex	= new RegExp( FCKConfig.FlashUploadDeniedExtensions, 'i' ) ;

function CheckUpload()
{
	var sFile = GetE('txtUploadFile').value ;

	if ( sFile.length == 0 )
	{
		alert( 'Please select a file to upload' ) ;
		return false ;
	}

	if ( ( FCKConfig.FlashUploadAllowedExtensions.length > 0 && !oUploadAllowedExtRegex.test( sFile ) ) ||
		( FCKConfig.FlashUploadDeniedExtensions.length > 0 && oUploadDeniedExtRegex.test( sFile ) ) )
	{
		OnUploadCompleted( 202 ) ;
		return false ;
	}

	// Show animation
	window.parent.Throbber.Show( 100 ) ;
	GetE( 'divUpload' ).style.display  = 'none' ;

	return true ;
}


/* flashvars selects*/

function Select( combo )
{
	var iIndex = combo.selectedIndex ;

	oListText.selectedIndex		= iIndex ;
	oListValue.selectedIndex	= iIndex ;

	var oTxtText	= document.getElementById( "txtText" ) ;
	var oTxtValue	= document.getElementById( "txtValue" ) ;

	oTxtText.value	= oListText.value ;
	oTxtValue.value	= oListValue.value ;
}

function Add()
{
	var oTxtText	= document.getElementById( "txtText" ) ;
	var oTxtValue	= document.getElementById( "txtValue" ) ;

	AddComboOption( oListText, oTxtText.value, oTxtText.value ) ;
	AddComboOption( oListValue, oTxtValue.value, oTxtValue.value ) ;

	oListText.selectedIndex = oListText.options.length - 1 ;
	oListValue.selectedIndex = oListValue.options.length - 1 ;

	oTxtText.value	= '' ;
	oTxtValue.value	= '' ;

	oTxtText.focus() ;
}

function Modify()
{
	var iIndex = oListText.selectedIndex ;

	if ( iIndex < 0 ) return ;

	var oTxtText	= document.getElementById( "txtText" ) ;
	var oTxtValue	= document.getElementById( "txtValue" ) ;

	oListText.options[ iIndex ].innerHTML	= HTMLEncode( oTxtText.value ) ;
	oListText.options[ iIndex ].value		= oTxtText.value ;

	oListValue.options[ iIndex ].innerHTML	= HTMLEncode( oTxtValue.value ) ;
	oListValue.options[ iIndex ].value		= oTxtValue.value ;

	oTxtText.value	= '' ;
	oTxtValue.value	= '' ;

	oTxtText.focus() ;
}

function Delete()
{
	RemoveSelectedOptions( oListText ) ;
	RemoveSelectedOptions( oListValue ) ;
}

// Remove all selected options from a SELECT object
function RemoveSelectedOptions(combo)
{
	// Save the selected index
	var iSelectedIndex = combo.selectedIndex ;

	var oOptions = combo.options ;

	// Remove all selected options
	for ( var i = oOptions.length - 1 ; i >= 0 ; i-- )
	{
		if (oOptions[i].selected) combo.remove(i) ;
	}

	// Reset the selection based on the original selected index
	if ( combo.options.length > 0 )
	{
		if ( iSelectedIndex >= combo.options.length ) iSelectedIndex = combo.options.length - 1 ;
		combo.selectedIndex = iSelectedIndex ;
	}
}

// Add a new option to a SELECT object (combo or list)
function AddComboOption( combo, optionText, optionValue, documentObject, index )
{
	var oOption ;

	if ( documentObject )
		oOption = documentObject.createElement("OPTION") ;
	else
		oOption = document.createElement("OPTION") ;

	if ( index != null )
		combo.options.add( oOption, index ) ;
	else
		combo.options.add( oOption ) ;

	oOption.innerHTML = optionText.length > 0 ? HTMLEncode( optionText ) : '&nbsp;' ;
	oOption.value     = optionValue ;

	return oOption ;
}

function HTMLEncode( text )
{
	if ( !text )
		return '' ;

	text = text.replace( /&/g, '&amp;' ) ;
	text = text.replace( /</g, '&lt;' ) ;
	text = text.replace( />/g, '&gt;' ) ;

	return text ;
}


function HTMLDecode( text )
{
	if ( !text )
		return '' ;

	text = text.replace( /&gt;/g, '>' ) ;
	text = text.replace( /&lt;/g, '<' ) ;
	text = text.replace( /&amp;/g, '&' ) ;

	return text ;
}




// The object used for all Web Video operations.
var oWebVideo = {
	Objects : [ "<object width=\"(?<width>\\d+)\" height=\"(?<height>\\d+)\".* name=\"movie\"\\s+value=\"(?<url>[^\"]*)\".*<\\/object>" ,
			// youtube style only embed
					"<embed src=\"(?<url>[^\"]*)\".* width=\"(?<width>\\d+)\" height=\"(?<height>\\d+)\".*<\\/embed>" ,
			// google video
					"<embed .*width:(?<width>\\d+)px.*height:(?<height>\\d+)px.* src=\"(?<url>[^\"]*)\".*<\\/embed>" ,
			// invalid syntax but anyway...
					"<embed src=\"(?<url>[^\"]*)\".* width=\"(?<width>\\d+)px\" height=\"(?<height>\\d+)px\".*<\\/embed>"
	] ,

	webPages : [{re:/http:\/\/www\.youtube\.com\/watch\?v=([^&]*)(&.*|$)/, url:'http://www.youtube.com/v/$1', width:425, height:344} ,
							{re:/http:\/\/video\.google\.(.*)\/videoplay\?docid=([^&]*)(&.*|$)/, url:'http://video.google.$1/googleplayer.swf?docid=$2', width:400, height:326}, 
							{re:/http:\/\/www\.mtvmusic\.com\/video\/\?id=([^&]*)(&.*|$)/, url:'http://media.mtvnservices.com/mgid:uma:video:mtvmusic.com:$1', width:320, height:271} ,
							{re:/http:\/\/www\.metacafe\.com\/watch\/(.*?)\/(.*?)(\/.*|$)/, url:'http://www.metacafe.com/fplayer/$1/$2.swf', width:400, height:345} 
	],

	// Parses the suplied HTML and returns an object with the url of the video, its width and height
	ParseHtml : function( html, width, height )
	{
		// Check if it's a valid swf and skip the tests
		var swfFile = new RegExp(".*\.swf$", i) ;
		if ( swfFile.test(html) )
				return {url: html, width: width, height: height } ;

		// Generic system to work with any proposed embed by the site (as long as it matches the previous regexps
		for(var i=0; i< this.Objects.length; i++)
		{
			// Using XRegExp to work with named captures: http://stevenlevithan.com/regex/xregexp/
			var re = new XRegExp( this.Objects[i] ) ;
			var parts = re.exec( html ) ;
			if (parts)
				return {url: parts.url, width: parts.width, height: parts.height };
		}

		// Ability to paste the url of the web site and extract the correct info. It needs to be adjusted for every site.
		for(var i=0; i< this.webPages.length; i++)
		{
			var page = this.webPages[i] ;
			var oMatch = html.match( page.re ) ;
			if (oMatch)
			{
				return {url: html.replace(page.re, page.url), width: page.width, height: page.height};
			}
		}
		
		return {url: html, width: width, height: height } ;
	}
};





// XRegExp 0.5.1, <stevenlevithan.com>, MIT License
if(!window.XRegExp){(function(){var D={exec:RegExp.prototype.exec,match:String.prototype.match,replace:String.prototype.replace,split:String.prototype.split},C={part:/(?:[^\\([#\s.]+|\\(?!k<[\w$]+>)[\S\s]?|\((?=\?(?!#|<[\w$]+>)))+|(\()(?:\?(?:(#)[^)]*\)|<([$\w]+)>))?|\\k<([\w$]+)>|(\[\^?)|([\S\s])/g,replaceVar:/(?:[^$]+|\$(?![1-9$&`']|{[$\w]+}))+|\$(?:([1-9]\d*|[$&`'])|{([$\w]+)})/g,extended:/^(?:\s+|#.*)+/,quantifier:/^(?:[?*+]|{\d+(?:,\d*)?})/,classLeft:/&&\[\^?/g,classRight:/]/g},A=function(H,F,G){for(var E=G||0;E<H.length;E++){if(H[E]===F){return E}}return -1},B=/()??/.exec("")[1]!==undefined;XRegExp=function(N,H){if(N instanceof RegExp){if(H!==undefined){throw TypeError("can't supply flags when constructing one RegExp from another")}return N.addFlags()}var H=H||"",E=H.indexOf("s")>-1,J=H.indexOf("x")>-1,O=false,Q=[],G=[],F=C.part,K,I,M,L,P;F.lastIndex=0;while(K=D.exec.call(F,N)){if(K[2]){if(!C.quantifier.test(N.slice(F.lastIndex))){G.push("(?:)")}}else{if(K[1]){Q.push(K[3]||null);if(K[3]){O=true}G.push("(")}else{if(K[4]){L=A(Q,K[4]);G.push(L>-1?"\\"+(L+1)+(isNaN(N.charAt(F.lastIndex))?"":"(?:)"):K[0])}else{if(K[5]){if(N.charAt(F.lastIndex)==="]"){G.push(K[5]==="["?"(?!)":"[\\S\\s]");F.lastIndex++}else{I=XRegExp.matchRecursive("&&"+N.slice(K.index),C.classLeft,C.classRight,"",{escapeChar:"\\"})[0];G.push(K[5]+I+"]");F.lastIndex+=I.length+1}}else{if(K[6]){if(E&&K[6]==="."){G.push("[\\S\\s]")}else{if(J&&C.extended.test(K[6])){M=D.exec.call(C.extended,N.slice(F.lastIndex-1))[0].length;if(!C.quantifier.test(N.slice(F.lastIndex-1+M))){G.push("(?:)")}F.lastIndex+=M-1}else{G.push(K[6])}}}else{G.push(K[0])}}}}}}P=RegExp(G.join(""),D.replace.call(H,/[sx]+/g,""));P._x={source:N,captureNames:O?Q:null};return P};RegExp.prototype.exec=function(I){var G=D.exec.call(this,I),F,H,E;if(G){if(B&&G.length>1){E=new RegExp("^"+this.source+"$(?!\\s)",this.getNativeFlags());D.replace.call(G[0],E,function(){for(H=1;H<arguments.length-2;H++){if(arguments[H]===undefined){G[H]=undefined}}})}if(this._x&&this._x.captureNames){for(H=1;H<G.length;H++){F=this._x.captureNames[H-1];if(F){G[F]=G[H]}}}if(this.global&&this.lastIndex>(G.index+G[0].length)){this.lastIndex--}}return G};String.prototype.match=function(E){if(!(E instanceof RegExp)){E=new XRegExp(E)}if(E.global){return D.match.call(this,E)}return E.exec(this)};String.prototype.replace=function(F,G){var E=(F._x||{}).captureNames;if(!(F instanceof RegExp&&E)){return D.replace.apply(this,arguments)}if(typeof G==="function"){return D.replace.call(this,F,function(){arguments[0]=new String(arguments[0]);for(var H=0;H<E.length;H++){if(E[H]){arguments[0][E[H]]=arguments[H+1]}}return G.apply(window,arguments)})}else{return D.replace.call(this,F,function(){var H=arguments;return D.replace.call(G,C.replaceVar,function(J,I,M){if(I){switch(I){case"$":return"$";case"&":return H[0];case"`":return H[H.length-1].slice(0,H[H.length-2]);case"'":return H[H.length-1].slice(H[H.length-2]+H[0].length);default:var K="";I=+I;while(I>E.length){K=D.split.call(I,"").pop()+K;I=Math.floor(I/10)}return(I?H[I]:"$")+K}}else{if(M){var L=A(E,M);return L>-1?H[L+1]:J}else{return J}}})})}};String.prototype.split=function(J,F){if(!(J instanceof RegExp)){return D.split.apply(this,arguments)}var G=[],E=J.lastIndex,K=0,I=0,H;if(F===undefined||+F<0){F=false}else{F=Math.floor(+F);if(!F){return[]}}if(!J.global){J=J.addFlags("g")}else{J.lastIndex=0}while((!F||I++<=F)&&(H=J.exec(this))){if(J.lastIndex>K){G=G.concat(this.slice(K,H.index),(H.index===this.length?[]:H.slice(1)));K=J.lastIndex}if(!H[0].length){J.lastIndex++}}G=K===this.length?(J.test("")?G:G.concat("")):(F?G:G.concat(this.slice(K)));J.lastIndex=E;return G}})()}RegExp.prototype.getNativeFlags=function(){return(this.global?"g":"")+(this.ignoreCase?"i":"")+(this.multiline?"m":"")+(this.extended?"x":"")+(this.sticky?"y":"")};RegExp.prototype.addFlags=function(A){var B=new XRegExp(this.source,(A||"")+this.getNativeFlags());if(this._x){B._x={source:this._x.source,captureNames:this._x.captureNames?this._x.captureNames.slice(0):null}}return B};RegExp.prototype.call=function(A,B){return this.exec(B)};RegExp.prototype.apply=function(B,A){return this.exec(A[0])};XRegExp.cache=function(C,A){var B="/"+C+"/"+(A||"");return XRegExp.cache[B]||(XRegExp.cache[B]=new XRegExp(C,A))};XRegExp.escape=function(A){return A.replace(/[-[\]{}()*+?.\\^$|,#\s]/g,"\\$&")};XRegExp.matchRecursive=function(P,D,S,F,B){var B=B||{},V=B.escapeChar,K=B.valueNames,F=F||"",Q=F.indexOf("g")>-1,C=F.indexOf("i")>-1,H=F.indexOf("m")>-1,U=F.indexOf("y")>-1,F=F.replace(/y/g,""),D=D instanceof RegExp?(D.global?D:D.addFlags("g")):new XRegExp(D,"g"+F),S=S instanceof RegExp?(S.global?S:S.addFlags("g")):new XRegExp(S,"g"+F),I=[],A=0,J=0,N=0,L=0,M,E,O,R,G,T;if(V){if(V.length>1){throw SyntaxError("can't supply more than one escape character")}if(H){throw TypeError("can't supply escape character when using the multiline flag")}G=XRegExp.escape(V);T=new RegExp("^(?:"+G+"[\\S\\s]|(?:(?!"+D.source+"|"+S.source+")[^"+G+"])+)+",C?"i":"")}while(true){D.lastIndex=S.lastIndex=N+(V?(T.exec(P.slice(N))||[""])[0].length:0);O=D.exec(P);R=S.exec(P);if(O&&R){if(O.index<=R.index){R=null}else{O=null}}if(O||R){J=(O||R).index;N=(O?D:S).lastIndex}else{if(!A){break}}if(U&&!A&&J>L){break}if(O){if(!A++){M=J;E=N}}else{if(R&&A){if(!--A){if(K){if(K[0]&&M>L){I.push([K[0],P.slice(L,M),L,M])}if(K[1]){I.push([K[1],P.slice(M,E),M,E])}if(K[2]){I.push([K[2],P.slice(E,J),E,J])}if(K[3]){I.push([K[3],P.slice(J,N),J,N])}L=N}else{I.push(P.slice(E,J))}if(!Q){break}}}else{D.lastIndex=S.lastIndex=0;throw Error("subject data contains unbalanced delimiters")}}if(J===N){N++}}if(Q&&!U&&K&&K[0]&&P.length>L){I.push([K[0],P.slice(L),L,P.length])}D.lastIndex=S.lastIndex=0;return I};
