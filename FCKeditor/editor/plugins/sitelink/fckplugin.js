/***********************************************/
/**  FCKeditor - Plugin Sitelink for CMSimple **/
/**          by Klaus Treichler © 2008        **/
/**           http://www.treichler.at         **/
/**             klaus@treichler.at            **/
/**     Scriptversion: 1.0 - 2008-06-16       **/
/***********************************************/

// Register the Sitelink Plugin
var dialogPath = FCKConfig.PluginsPath + 'sitelink/sitelink.php';
var sitelinkDialogCmd = new FCKDialogCommand( FCKLang["sitelinkTitle"], FCKLang["sitelinkTitle"], dialogPath, 500, 500 );
FCKCommands.RegisterCommand( 'sitelink', sitelinkDialogCmd );

// Creating button in the FCKeditor - Toolbar
var ositelinkItem = new FCKToolbarButton( 'sitelink', FCKLang['sitelinkTitle'] ) ;
ositelinkItem.IconPath = FCKPlugins.Items['sitelink'].Path + 'fck_sitelink.gif' ;
FCKToolbarItems.RegisterItem( 'sitelink', ositelinkItem ) ;