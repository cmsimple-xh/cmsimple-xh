var oEditor = window.parent.InnerDialogLoaded() ;
var FCK		= oEditor.FCK ;

// Set the language direction.
window.document.dir = oEditor.FCKLang.Dir ;

// Set the Skin CSS.
document.write( '<link href="' + oEditor.FCKConfig.SkinPath + 'fck_dialog.css" type="text/css" rel="stylesheet">' ) ;

var sAgent = navigator.userAgent.toLowerCase() ;

var is_ie = (sAgent.indexOf("msie") != -1); // FCKBrowserInfo.IsIE
var is_gecko = !is_ie; // FCKBrowserInfo.IsGecko

var oMedia = null;


function window_onload()
{
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	// Load the selected element information (if any).
	LoadSelection() ;

	// Show/Hide the "Browse Server" button.
	GetE('tdBrowse').style.display = oEditor.FCKConfig.FlashBrowser ? '' : 'none' ;

	// Activate the "OK" button.
	window.parent.SetOkButton( true ) ;
}


function getSelectedMovie(){
	var oSel = null;

	// explorer..
	if (is_ie){
		oSel = FCK.Selection.GetSelectedElement( 'OBJECT' );
	}
	
	// gecko
	else if (is_gecko){
		var o = FCK.EditorWindow.getSelection() ;

		if ((o != null) && (o.anchorNode.tagName == 'OBJECT')){
			oSel = o.anchorNode;
		}
	}
	
	// other
	else {
		alert ("Browser Not Supported");
	}

	return oSel;
}


function LoadSelection()
{
	oMedia = new Media();
	oMedia.setObjectElement(getSelectedMovie());
	//alert('test');
/*	
	alert (
		"id: " + oMedia.id +
		"\nUrl: " + oMedia.url + 
		"\nWidth: " + oMedia.width +
		"\nHeight: " + oMedia.height +
		"\nQuality: " + oMedia.quality +
		"\nScale: " + oMedia.scale +
		"\nVSpace: " + oMedia.vspace +
		"\nHSpace: " + oMedia.hspace +
		"\nAlign: " + oMedia.align +
		"\nBgcolor: " + oMedia.bgcolor +
		"\nLoop: " + oMedia.loop +
		"\nPlay: " + oMedia.play
	);
*/
	GetE('txtURL').value    		= oMedia.url;
	GetE('txtImgURL').value    		= oMedia.iurl;
	GetE('txtWidth').value			= oMedia.width;
	GetE('txtHeight').value			= oMedia.height;
	GetE('chkLoop').value			= oMedia.loop;
	GetE('chkAutoplay').value		= oMedia.play;
	GetE('txtBgColor').value		= oMedia.bgcolor;
	GetE('txtToolbarColor').value		= oMedia.toolcolor;
	GetE('txtToolbarTxtColor').value	= oMedia.tooltcolor;
	GetE('txtToolbarTxtRColor').value	= oMedia.tooltrcolor;


	//updatePreview();
}

//#### The OK button was hit.
function Ok()
{
	if ( GetE('txtURL').value.length == 0 )
	{
		GetE('txtURL').focus() ;	

		alert( oEditor.FCKLang.DlgFLVPlayerAlertUrl ) ;
		return false ;
	}


	if ( GetE('txtWidth').value.length == 0 )
	{
		GetE('txtWidth').focus() ;	

		alert( oEditor.FCKLang.DlgFLVPlayerAlertWidth ) ;
		return false ;
	}

	if ( GetE('txtHeight').value.length == 0 )
	{
		GetE('txtHeight').focus() ;	

		alert( oEditor.FCKLang.DlgFLVPlayerAlertHeight ) ;
		return false ;
	}


	var e = (oMedia || new Media()) ;

	updateMovie(e) ;

	FCK.InsertHtml(e.getInnerHTML()) ;

	return true ;
}


function updateMovie(e){
	e.url = GetE('txtURL').value;
	e.iurl = GetE('txtImgURL').value;
	e.bgcolor = GetE('txtBgColor').value;
	e.toolcolor = GetE('txtToolbarColor').value;
	e.tooltcolor = GetE('txtToolbarTxtColor').value;
	e.tooltrcolor = GetE('txtToolbarTxtRColor').value;
	e.width = (isNaN(GetE('txtWidth').value)) ? 0 : parseInt(GetE('txtWidth').value);
	e.height = (isNaN(GetE('txtHeight').value)) ? 0 : parseInt(GetE('txtHeight').value);
	e.loop = (GetE('chkLoop').checked) ? 'always' : 'none';
	e.play = (GetE('chkAutoplay').checked) ? 'true' : 'false';
}


function BrowseServer()
{
	OpenServerBrowser(
		'flv',
		oEditor.FCKConfig.FlashBrowserURL,
		oEditor.FCKConfig.FlashBrowserWindowWidth,
		oEditor.FCKConfig.FlashBrowserWindowHeight ) ;
}


function LnkBrowseServer()
{
	OpenServerBrowser(
		'Link',
		oEditor.FCKConfig.LinkBrowserURL,
		oEditor.FCKConfig.LinkBrowserWindowWidth,
		oEditor.FCKConfig.LinkBrowserWindowHeight ) ;
}


function img1BrowseServer()
{
	OpenServerBrowser(
		'img1',
		oEditor.FCKConfig.ImageBrowserURL,
		oEditor.FCKConfig.ImageBrowserWindowWidth,
		oEditor.FCKConfig.ImageBrowserWindowHeight ) ;
}


function OpenServerBrowser( type, url, width, height )
{
	sActualBrowser = type ;
	OpenFileBrowser( url, width, height ) ;
}

var sActualBrowser ;


function SetUrl( url ) {
	if ( sActualBrowser == 'flv' ) {
		document.getElementById('txtURL').value = url ;
		GetE('txtHeight').value = GetE('txtWidth').value = '' ;
	} else if ( sActualBrowser == 'img1' ) {
		document.getElementById('txtImgURL').value = url ;
	}
}




var Media = function (o){
	this.url = '';
	this.iurl = '';
	this.width = '';
	this.height = '';
	this.loop = '';
	this.play = '';
	this.bgcolor = '111111';
	this.toolcolor = '333333';
	this.tooltcolor = 'ffffff';
	this.tooltrcolor = 'ffcc66';

	if (o) 
		this.setObjectElement(o);
};

Media.prototype.setObjectElement = function (e){
	if (!e) return ;
	this.width = GetAttribute( e, 'width', this.width );
	this.height = GetAttribute( e, 'height', this.height );
};


Media.prototype.getInnerHTML = function (objectId){
	var s = "";
	s+= '<object id="flowplayer" border="1" width="' + this.width + '" height="' + this.height + '" data="FCKeditor/editor/plugins/flvPlayer/flowplayer.swf" type="application/x-shockwave-flash"><param name="movie" value="FCKeditor/editor/plugins/flvPlayer/flowplayer.swf" /><param name="allowfullscreen" value="true" /><param name="bgcolor" value="' + this.bgcolor + '" /><param name="flashvars" value=\'config={"clip":{"url":"' + this.url + '", "autoPlay":' + this.play + ',"autoBuffering":' + this.loop + '},"plugins":{"controls":{"timeColor":"#ffffff","borderRadius":"0px","bufferGradient":"none","slowForward":true,"backgroundColor":"#' + this.toolcolor + '","volumeSliderGradient":"none","slowBackward":false,"timeBorderRadius":20,"progressGradient":"none","time":true,"height":26,"volumeColor":"#4599ff","tooltips":{"stop":"Stop","slowMotionFBwd":"Fast backward","previous":"Previous","next":"Next","play":"Play","buttons":true,"slowMotionFwd":"Slow forward","pause":"Pause","unmute":"Unmute","fullscreen":"Fullscreen","marginBottom":5,"slowMotionFFwd":"Fast forward","fullscreenExit":"Exit fullscreen","scrubber":true,"volume":false,"slowMotionBwd":"Slow backward","mute":"Mute"},"opacity":1,"fastBackward":false,"timeFontSize":12,"border":"0px","volumeSliderColor":"#ffffff","bufferColor":"#a3a3a3","buttonColor":"#ffffff","mute":true,"autoHide":{"enabled":true,"hideDelay":500,"hideStyle":"fade","mouseOutDelay":500,"hideDuration":400,"fullscreenOnly":false},"backgroundGradient":"none","width":"100pct","sliderBorder":"1px solid rgba(128, 128, 128, 0.7)","display":"block","buttonOverColor":"#ffffff","fullscreen":true,"timeBgColor":"rgb(0, 0, 0, 0)","scrubberBarHeightRatio":0.2,"bottom":0,"stop":false,"zIndex":1,"sliderColor":"#000000","scrubberHeightRatio":0.6,"tooltipTextColor":"#' + this.tooltcolor + '","sliderGradient":"none","spacing":{"time":6,"volume":8,"all":2},"timeBgHeightRatio":0.8,"volumeSliderHeightRatio":0.6,"timeSeparator":" ","name":"controls","volumeBarHeightRatio":0.2,"left":"50pct","tooltipColor":"#' + this.tooltrcolor + '","playlist":false,"durationColor":"#b8d9ff","play":true,"fastForward":true,"progressColor":"#4599ff","timeBorder":"0px solid rgba(0, 0, 0, 0.3)","scrubber":true,"volume":true,"builtIn":false,"volumeBorder":"1px solid rgba(128, 128, 128, 0.7)"}}}\' /></object>';
	s=s.replace(/#/g,"");

	return s;
};


function SelectColor1()
{
	oEditor.FCKDialog.OpenDialog( 'FCKDialog_Color', oEditor.FCKLang.DlgColorTitle, 'dialog/fck_colorselector.html', 400, 330, SelectBackColor, window ) ;
}

function SelectColor2()
{
	oEditor.FCKDialog.OpenDialog( 'FCKDialog_Color', oEditor.FCKLang.DlgColorTitle, 'dialog/fck_colorselector.html', 400, 330, SelectToolColor, window ) ;
}

function SelectColor3()
{
	oEditor.FCKDialog.OpenDialog( 'FCKDialog_Color', oEditor.FCKLang.DlgColorTitle, 'dialog/fck_colorselector.html', 400, 330, SelectToolTextColor, window ) ;
}

function SelectColor4()
{
	oEditor.FCKDialog.OpenDialog( 'FCKDialog_Color', oEditor.FCKLang.DlgColorTitle, 'dialog/fck_colorselector.html', 400, 330, SelectToolTextRColor, window ) ;
}

function SelectBackColor( color )
{
	if ( color && color.length > 0 ) {
		GetE('txtBgColor').value = color ;
		//updatePreview()
	}
}

function SelectToolColor( color )
{
	if ( color && color.length > 0 ) {
		GetE('txtToolbarColor').value = color ;
		//updatePreview()
	}
}

function SelectToolTextColor( color )
{
	if ( color && color.length > 0 ) {
		GetE('txtToolbarTxtColor').value = color ;
		//updatePreview()
	}
}

function SelectToolTextRColor( color )
{
	if ( color && color.length > 0 ) {
		GetE('txtToolbarTxtRColor').value = color ;
		//updatePreview()
	}
}