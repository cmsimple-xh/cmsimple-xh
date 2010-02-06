/***********************************************/
/**  FCKeditor - Plugin Sitelink for CMSimple **/
/**          by Klaus Treichler © 2008        **/
/**           http://www.treichler.at         **/
/**             klaus@treichler.at            **/
/**     Scriptversion: 1.0 - 2008-06-16       **/
/***********************************************/

var sitelink = function(){
  this.aLinks = new Array();
  this.bDebuggerOn = false;
  this.sUri = '';
  this.sTitle = '';
  this.sInnerHtml = '';
  this.bError = false;
}

sitelink.prototype.showMessage = function(sMessage,bShowAlways){
  if(this.bDebuggerOn || bShowAlways){
    alert(sMessage);
	}
} 

sitelink.prototype.setUri = function(uri){
  if ( uri.length == 0 ){
		throw 'no_uri';
	}

  this.sUri = uri;

  this.showMessage('setUri(): ' + this.sUri);
}

sitelink.prototype.setTitle = function(title){
  this.sTitle = title.replace(/"/,'\'');
  this.showMessage('setTitle(): ' + this.sTitle);
}

sitelink.prototype.checkSelection = function(){
  if(this.sLinks.length < 1){
    throw 'no_selection';
  }
  this.showMessage('checkSelection()');
}

sitelink.prototype.createLink = function(){

  this.sLinks = oLink ? [ oLink ] : oEditor.FCK.CreateLink( this.sUri ) ;
	this.showMessage('createLink(): ' + this.sLinks[0]);
}

sitelink.prototype.perform = function(){
  oLink = this.sLinks[0];
  oLink.href = this.sUri ;
	SetAttribute( oLink, '_fcksavedurl', this.sUri ) ;
  this.showMessage('set HREF' + ' | ' + this.sUri);
  this.showMessage('get HREF' + ' | ' + oLink.href);
	SetAttribute(oLink , 'title' , this.sTitle );
	this.showMessage('set TITLE');
	oEditor.FCKSelection.SelectNode(oLink);
	this.showMessage('replace LINK');
}

sitelink.prototype.setLink = function(url,title){
  try{
    this.setUri(url);
    this.setTitle(title);
    this.createLink();
    this.checkSelection();
    this.perform();
  	return true ;
  }
  catch (e){
    if(e == 'no_selection'){
      this.showMessage(oFCKLang.sitelinkNotselected, true);
      return false;
    }
  }
}

var LinkObject;
var oEditor = window.parent.InnerDialogLoaded() ;
var oFCK = oEditor.FCK ;
var oFCKLang = oEditor.FCKLang ;
oEditor.FCKLanguageManager.TranslatePage(document) ;
var oLink = oFCK.Selection.MoveToAncestorNode( 'A' ) ;

if ( oLink ){
  oFCK.Selection.SelectNode( oLink ) ;
  }

window.onload = function(){
  LinkObject = new sitelink();
}

function Ok(url,title){
  return LinkObject.setLink(url,title);
  }