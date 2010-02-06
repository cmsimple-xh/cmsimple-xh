/*
 *
 * File Name: fckplugin.js
 * 	Plugin to add flash files using SwfObject 2
 * 
 * File Authors:
 * 		Alfonso Martínez de Lizarrondo
 *
 * Developed for InControlSolutions
 *
 * Version: 1.5
 */


/**
	FCKCommentsProcessor
	---------------------------
	It's run after a document has been loaded, it detects all the protected source elements

	In order to use it, you add your comment parser with 
	FCKCommentsProcessor.AddParser( function )
*/
if (typeof FCKCommentsProcessor === 'undefined')
{
	var FCKCommentsProcessor = FCKDocumentProcessor.AppendNew() ;
	FCKCommentsProcessor.ProcessDocument = function( oDoc )
	{
		if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
			return ;

		if ( !oDoc )
			return ;

	//Find all the comments: <!--{PS..0}-->
	//try to choose the best approach according to the browser:
		if ( oDoc.evaluate )
			this.findCommentsXPath( oDoc );
		else
		{
			if (oDoc.all)
				this.findCommentsIE( oDoc.body ) ;
			else
				this.findComments( oDoc.body ) ;
		}

	}

	FCKCommentsProcessor.findCommentsXPath = function(oDoc) {
		var nodesSnapshot = oDoc.evaluate('//body//comment()', oDoc.body, null, XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null );

		for ( var i=0 ; i < nodesSnapshot.snapshotLength; i++ )
		{
			this.parseComment( nodesSnapshot.snapshotItem(i) ) ;
		}
	}

	FCKCommentsProcessor.findCommentsIE = function(oNode) {
		var aComments = oNode.getElementsByTagName( '!' );
		for(var i=aComments.length-1; i >=0 ; i--)
		{
			var comment = aComments[i] ;
			if (comment.nodeType == 8 ) // oNode.COMMENT_NODE) 
				this.parseComment( comment ) ;
		}
	}

	// Fallback function, iterate all the nodes and its children searching for comments.
	FCKCommentsProcessor.findComments = function( oNode ) 
	{
		if (oNode.nodeType == 8 ) // oNode.COMMENT_NODE) 
		{
			this.parseComment( oNode ) ;
		}
		else 
		{
			if (oNode.hasChildNodes()) 
			{
				var children = oNode.childNodes ;
				for (var i = children.length-1; i >=0 ; i--) 
					this.findComments( children[ i ] );
			}
		}
	}

	// We get a comment node
	// Check that it's one that we are interested on:
	FCKCommentsProcessor.parseComment = function( oNode )
	{
		var value = oNode.nodeValue ;

		// Difference between 2.4.3 and 2.5
		var prefix = ( FCKConfig.ProtectedSource._CodeTag || 'PS\\.\\.' ) ;

		var regex = new RegExp( "\\{" + prefix + "(\\d+)\\}", "g" ) ;

		if ( regex.test( value ) ) 
		{
			var index = RegExp.$1 ;
			var content = FCKTempBin.Elements[ index ] ;

			// Now call the registered parser handlers.
			var oCalls = this.ParserHandlers ;
			if ( oCalls )
			{
				for ( var i = 0 ; i < oCalls.length ; i++ )
					oCalls[ i ]( oNode, content, index ) ;

			}

		}
	}

	/**
		The users of the object will add a parser here, the callback function gets two parameters:
			oNode: it's the node in the editorDocument that holds the position of our content
			oContent: it's the node (removed from the document) that holds the original contents
			index: the reference in the FCKTempBin of our content
	*/
	FCKCommentsProcessor.AddParser = function( handlerFunction )
	{
		if ( !this.ParserHandlers )
			this.ParserHandlers = [ handlerFunction ] ;
		else
		{
			// Check that the event handler isn't already registered with the same listener
			// It doesn't detect function pointers belonging to an object (at least in Gecko)
			if ( this.ParserHandlers.IndexOf( handlerFunction ) == -1 )
				this.ParserHandlers.push( handlerFunction ) ;
		}
	}
}
/**
	END of FCKCommentsProcessor
	---------------------------
*/

/**
  @desc  inject the function
  @author  Aimingoo&Riceball
*/
function Inject( aOrgFunc, aBeforeExec, aAtferExec ) {
  return function() {
    if (typeof(aBeforeExec) == 'function') arguments = aBeforeExec.apply(this, arguments) || arguments;
    //convert arguments object to array
    var Result, args = [].slice.call(arguments); 
    args.push(aOrgFunc.apply(this, args));
    if (typeof(aAtferExec) == 'function') Result = aAtferExec.apply(this, args);
    return (typeof(Result) != 'undefined')?Result:args.pop();
  } ;
}

// If it hasn't been set, then use a version hosted by Google.
if (typeof FCKConfig.swfObjectPath === 'undefined')
	FCKConfig.swfObjectPath = "http://ajax.googleapis.com/ajax/libs/swfobject/2.1/swfobject.js" ;


// Replace the default flash dialog:
FCKCommands.LoadedCommands[ 'Flash' ] = new FCKDialogCommand( 'Flash', FCKLang.DlgFlashTitle, FCKPlugins.Items['swfobject'].Path + 'dialog/fck_flash.html', 450, 390 ) ;

// Check if the comment it's one of our scripts:
FCKCommentsProcessor.AddParser(  function( oNode, oContent, index)
{
		if ( FCK.SwfobjectHandler.detectScript( oContent ) )
		{
			var oSWF = FCK.SwfobjectHandler.createNew() ;
			oSWF.parse( oContent ) ;
			oSWF.createHtmlElement( oNode, index ) ;
		}
		else
		{
			if ( FCK.SwfobjectHandler.detectSwfObjectScript( oContent ) )
				oNode.parentNode.removeChild( oNode );
		}
} );

// Context menu
FCK.ContextMenu.RegisterListener( {
	AddItems : function( menu, tag, tagName )
	{
		// under what circumstances do we display this option
		if ( tagName == 'IMG' && tag.getAttribute( 'swfobjectnumber' ) )
		{
//			menu.AddSeparator() ;
			// No other options:
			menu.RemoveAllItems() ;
			// the command needs the registered command name, the title for the context menu, and the icon path
			menu.AddItem( 'Flash', FCKLang.FlashProperties, 38 ) ;
		}
	}}
);

// Double click
FCK.RegisterDoubleClickHandler( function( oNode )
{
	if ( !oNode.getAttribute( 'swfobjectnumber' ))
		return ;

	FCK.Commands.GetCommand( 'Flash' ).Execute() ;
}, 'IMG' ) ;


// Object that handles the common functions about all the players
FCK.SwfobjectHandler = {
	// Object to store a reference to each player
	Items: {},

	getItem: function(id){
		return this.Items[id];
	},

	// Verify that the node is a script generated by this plugin.
	detectScript: function( script )
	{
		// We only know about version 1:
		if ( !(/FCK swfobject v1\.(\d+)/.test(script)) )
			return false;

		return true ;
	},

	// Detects both the google script as well as our ending block
	// both must be removed and then added later only if neccesary
	detectSwfObjectScript: function( script )
	{

		if ( this.SwfObjectScript.Trim() != script )
			return false;

		return ( true ) ;
	},

	SwfObjectScript : function()
	{		
		return (FCKConfig.swfObjectPath === '' ? '' : '\r\n<script src="' + FCKConfig.swfObjectPath + '" type="text/javascript">//swfobject plugin<\/script>'	) ;
	}() ,

	// This can be called from the dialog
	createNew: function()
	{
		var item = new swfobject() ;
		this.Items[ item.number ] = item;
		return item;
	},

	// We will use this to track the number of maps that are generated
	// This way we know if we must add the Google Script or not.
	// We store their names so they are called properly from BuildEndingScript
	CreatedItemsNames : [],

	// Function that will be injected into the normal core
	GetXHTMLAfter: function( node, includeNode, format, Result )
	{
/*
		if (FCK.SwfobjectHandler.CreatedItemsNames.length > 0)
		{
			Result += FCK.SwfobjectHandler.BuildEndingScript() ;
		}
*/
		// Reset the counter each time the GetXHTML function is called
		FCK.SwfobjectHandler.CreatedItemsNames = [];

		return Result ;
	},

	// Store any previous processor so nothing breaks
	previousProcessor: FCKXHtml.TagProcessors[ 'img' ] 
}





// Our object that will handle parsing of the script and creating the new one.
var swfobject = function() 
{
	var now = new Date() ;
	this.number = '' + now.getFullYear() + now.getMonth() + now.getDate() + now.getHours() + now.getMinutes() + now.getSeconds() ;

	this.file = '';
	this.width = FCKConfig.swfobject_Width || '';
	this.height = FCKConfig.swfobject_Height || '';
	this.version = FCKConfig.swfObject_FlashVersion || '7.0.0' ;
	this.expressInstall = FCKConfig.swfObject_ExpressInstall || false ;
	if (this.expressInstall) this.expressInstall = '"' + this.expressInstall + '"' ;

	this.flashvars = {};
	this.params = {scale:'',play:true,menu:true,loop:true,allowfullscreen:false,wmode:'',allowscriptaccess:''};
	this.attributes = {id:'',"class":'',style:'',title:''};

	this.WrapperClass = FCKConfig.swfobject_WrapperClass || '' ;
}


swfobject.prototype.createHtmlElement = function( oReplacedNode, index)
{
	var oFakeNode = FCK.EditorDocument.createElement( 'IMG' ) ;

	// Are we creating a new map?
	if ( !oReplacedNode )
	{
    index = FCKTempBin.AddElement( this.BuildScript() ) ;
		var prefix = ( FCKConfig.ProtectedSource._CodeTag || 'PS..' ) ;
		oReplacedNode = FCK.EditorDocument.createComment( '{' + prefix + index + '}' ) ;
		FCK.InsertElement(oReplacedNode);
	}
//	oFakeNode.contentEditable = false ;
//	oFakeNode.setAttribute( '_fckfakelement', 'true', 0 ) ;

	oFakeNode.setAttribute( '_fckrealelement', FCKTempBin.AddElement( oReplacedNode ), 0 ) ;
	oFakeNode.setAttribute( '_fckBinNode', index, 0 ) ;

	oFakeNode.src = FCKConfig.FullBasePath + 'images/spacer.gif' ;
	oFakeNode.style.display = 'block' ;
	oFakeNode.style.border = '1px solid black' ;
	oFakeNode.style.background = 'white center center url("' + FCKPlugins.Items['swfobject'].Path + 'images/preview.png' + '") no-repeat' ;

	oFakeNode.setAttribute("SwfObjectNumber", this.number, 0) ;

	oReplacedNode.parentNode.insertBefore( oFakeNode, oReplacedNode ) ;
	oReplacedNode.parentNode.removeChild( oReplacedNode ) ;

	// dimensions
	this.updateHTMLElement( oFakeNode );

	return oFakeNode ;
}

swfobject.prototype.updateScript = function( oFakeNode )
{
	this.updateDimensions( oFakeNode ) ;

	var index = oFakeNode.getAttribute( '_fckBinNode' );
	FCKTempBin.Elements[ index ] =  this.BuildScript() ;
}

swfobject.prototype.updateHTMLElement = function( oFakeNode )
{
	oFakeNode.width = this.width ;
	oFakeNode.height = this.height ;

	// The wrapper class is applied to the IMG not to a wrapping DIV !!!
	if ( this.WrapperClass !== '')
		oFakeNode.className = this.WrapperClass ;
}

// Read the dimensions back from the fake node (the user might have manually resized it)
swfobject.prototype.updateDimensions = function( oFakeNode )
{
	var iWidth, iHeight ;
	var regexSize = /^\s*(\d+)px\s*$/i ;

	if ( oFakeNode.style.width )
	{
		var aMatchW  = oFakeNode.style.width.match( regexSize ) ;
		if ( aMatchW )
		{
			iWidth = aMatchW[1] ;
			oFakeNode.style.width = '' ;
			oFakeNode.width = iWidth ;
		}
	}

	if ( oFakeNode.style.height )
	{
		var aMatchH  = oFakeNode.style.height.match( regexSize ) ;
		if ( aMatchH )
		{
			iHeight = aMatchH[1] ;
			oFakeNode.style.height = '' ;
			oFakeNode.height = iHeight ;	
		}
	}

	this.width	= iWidth ? iWidth : oFakeNode.width ;
	this.height	= iHeight ? iHeight : oFakeNode.height ;
}

swfobject.prototype.parse = function( script )
{
	function parseValue(value)
	{
		if (value==="true")
			return true;
		if (value==="false")
			return false;
		return value ;
	}

	// We only know about version 1:
	if ( !(/FCK swfobject v1\.(\d+)/.test(script)) )
		return false;

	var version = parseInt(RegExp.$1, 10) ;

	// dimensions:
	var regexpDimensions = /<div id="flash(\d+)" style="width\:\s*(\d+)px; height\:\s*(\d+)px;">/ ;
	if (regexpDimensions.test( script ) )
	{
		delete FCK.SwfobjectHandler.Items[this.number] ;
		this.number = RegExp.$1 ;
		FCK.SwfobjectHandler.Items[this.number] = this ;

		this.width = RegExp.$2 ;
		this.height = RegExp.$3 ;
	}

	var regexpWrapper = /<div class=("|')(.*)\1.*\/\/wrapper/ ;
	if (regexpWrapper.test( script ) )
		this.WrapperClass = RegExp.$2 ;
	else
		this.WrapperClass = '' ;

// swfobject.embedSWF("/userfiles/flash/gridlock.swf", "flash200881416419", 300, 100, "7.0.0", "", flashvars, params, attributes);
	var regexpFile = /swfobject\.embedSWF\("(.*?)",/ ;
	if (regexpFile.test( script ) )
		this.file = RegExp.$1 ;

	// parse automatically all the variables.
	var regexpParams = /params\["(.*)"\]="(.*)"/g ;
	var regexpAttributes = /attributes\["(.*)"\]="(.*)"/g ;
	var regexpFlashvars = /flashvars\["(.*)"\]="(.*)"/g ;
	if (version<3)
	{
		regexpParams = /params.(.*)="(.*)"/g ;
		regexpAttributes = /attributes.(.*)="(.*)"/g ;
		regexpFlashvars = /flashvars.(.*)="(.*)"/g ;
	}

	while( (result = regexpParams.exec(script)) )
	{
		this.params[result[1]] = parseValue(result[2]) ;
	}

	while( (result = regexpAttributes.exec(script)) )
	{
		this.attributes[result[1]] = parseValue(result[2]) ;
	}

	while( (result = regexpFlashvars.exec(script)) )
	{
		this.flashvars[result[1]] = parseValue(result[2]) ;
	}

	return true;
}

swfobject.prototype.BuildScript = function()
{
	var versionMarker = '/* FCK swfobject v1.5 */' ;

	var v, aScript = [] ;
	aScript.push('\r\n<script type="text/javascript">') ;
	aScript.push('/*<![CDATA[*/');
	aScript.push( versionMarker ) ;

	if ( this.WrapperClass !== '')
		aScript.push('document.write(\'<div class="' + this.WrapperClass + '">\'); //wrapper');

	aScript.push('document.write(\'<div id="flash' + this.number + '" style="width:' + this.width + 'px; height:' + this.height + 'px;"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player<\\\/a> to see this player.<\\\/div>\');');

	if ( this.WrapperClass !== '')
		aScript.push('document.write(\'<\\\/div>\'); ');


	aScript.push('var params={};');
	aScript.push('var attributes={};');
	aScript.push('var flashvars = {};');

	for(v in this.params)
	{
		if (this.params[v]!=='')
			aScript.push('	params["' + v + '"]="' + this.params[v] + '";');
	}

	for(v in this.attributes)
	{
		if (this.attributes[v]!=='')
			aScript.push('	attributes["' + v + '"]="' + this.attributes[v] + '";');
	}

	for(v in this.flashvars)
	{
		if (this.flashvars[v]!=='')
			aScript.push('	flashvars["' + v + '"]="' + this.flashvars[v] + '";');
	}

	aScript.push('swfobject.embedSWF("' + this.file + '", "flash' + this.number + '", ' + this.width + ', ' + this.height + ', "' + this.version + '", ' + this.expressInstall + ', flashvars, params, attributes);') ;

	aScript.push('/*]]>*/');
	aScript.push('</script>');

	return aScript.join('\r\n');
}




// Modifications of the core routines of FCKeditor:

FCKXHtml.GetXHTML = Inject(FCKXHtml.GetXHTML, null, FCK.SwfobjectHandler.GetXHTMLAfter ) ;

FCKXHtml.TagProcessors.img = function( node, htmlNode, xmlNode )
{
	if ( htmlNode.getAttribute( 'SwfObjectNumber' ) )
	{
		var oMap = FCK.SwfobjectHandler.getItem( htmlNode.getAttribute( 'SwfObjectNumber' ) ) ;
		FCK.SwfobjectHandler.CreatedItemsNames.push( oMap.number ) ;

		oMap.updateScript( htmlNode );
		node = FCK.GetRealElement( htmlNode ) ;
		if ( FCK.SwfobjectHandler.CreatedItemsNames.length == 1 )
		{
			// If it is the first map, insert the google maps script
			var index = FCKTempBin.AddElement( FCK.SwfobjectHandler.SwfObjectScript ) ;
			var prefix = ( FCKConfig.ProtectedSource._CodeTag || 'PS..' ) ;
			oScriptCommentNode = xmlNode.ownerDocument.createComment( '{' + prefix + index + '}' ) ;
			xmlNode.appendChild( oScriptCommentNode ) ;
		}

		return xmlNode.ownerDocument.createComment( node.nodeValue ) ;
	}

	if (typeof FCK.SwfobjectHandler.previousProcessor == 'function') 
		node = FCK.SwfobjectHandler.previousProcessor( node, htmlNode, xmlNode ) ;
	else
		node = FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

	return node ;
};


// Returns an array with the available classes defined in the Styles
function GetAvailableClasses( nodeName ) 
{
	var styles = FCK.Styles.GetStyles() ;
	var aClasses = [{name:'', classname:''}];

	for ( var styleName in styles )
	{
		var style = styles[styleName] ;
		if (style.IsCore)
			continue;

		if (style.Element == nodeName)
		{
			if (style._StyleDesc.Attributes && style._StyleDesc.Attributes['class'] ) 
				aClasses.push( {name:styleName, classname:style._StyleDesc.Attributes['class']} ) ;
		}
	}

	return aClasses ;
}