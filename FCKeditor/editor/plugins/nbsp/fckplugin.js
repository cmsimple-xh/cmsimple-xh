/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2006 Frederico Caldeira Knabben
 *
 * Non-breaking Space Plugin
 * Copyright (c) 2006 Bartosz Rogozinski [REGE]
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 *    http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 *    http://www.fckeditor.net/
 * 
 * "Support Open Source software. What about a donation today?"
 * 
 * File Name: fckplugin.js
 *    Insert non-breaking space
 * 
 * Version 1.0.1, 13-04-2006
 * 
 * File Authors:
 *    Bartosz Rogozinski [REGE] (rege-tech@wsm24.com) - original release
 */

var My_FCKNbspCommand = function()
{

}

My_FCKNbspCommand.prototype.Execute = function()
{
    FCK.InsertHtml('&nbsp;');
}

My_FCKNbspCommand.prototype.GetState = function()
{
    return FCK_TRISTATE_OFF; 
}

// Register the related command.
FCKCommands.RegisterCommand('nbsp', new My_FCKNbspCommand());

// Create the "nbsp" toolbar button.
var nbspItem = new FCKToolbarButton("nbsp", FCKLang.NbspButton);
nbspItem.IconPath = FCKConfig.PluginsPath + 'nbsp/nbsp.gif';

// 'nbsp' is the name used in the Toolbar config.
FCKToolbarItems.RegisterItem('nbsp', nbspItem);