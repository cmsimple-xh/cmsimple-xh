**********************************************************************************************

FCKeditor-Integration for CMSimple, FCKeditor4CMSimple
©2007-2009 Connie Müller-Gödecke, Holger Irmler
This work is licensed under GNU General Public License Version 2 or later (GPL)

**********************************************************************************************

*** Filename: readme1st.txt
*** Version:  1.5
*** Date:     2009-07-15
*** Author:   Holger Irmler
*** Support:  http://www.CMSimpleForum.com

**********************************************************************************************

Three steps to quick - install FCKeditor4CMSimple

1. Unzip the archive to a local folder and keep the folder structure
2. Make a backup of your files at the server, if you have an older version, 
   then upload the files to your webserver:
   /cmsimple/fckeditor.php -> goes to CMSimple-folder (same folder as cms.php)
   /FCKeditor/*.* -> goes to the root of your CMSimple installation (same folder as index.php)

   your folderstructure should now be like the following:
    
    .index.php
    |--2lang
    |--cmsimple
    |-----fckeditor.php
    |--content
    |--downloads
    |--FCKeditor
    |--images
    |--plugins
    |--templates


3. Login to CMSimple -> Settings -> Edit configuration -> set the value for editor_external
   to "fckeditor" and save your settings

Enjoy!

****

Note: 
On some servers the filebrowser will not work if there is an index.xxx -file in the 
resource folders (from version 2.4.0 by default ./downloads and ./images).
These files are thought to prevent directory listing of this folders which is turned 
off by many servers. Anyway these files are present in the downloads of the CMSimple package.
If the filebrowser returns an empty window without links to your files,
try to solve the problem by deleting these index.xxx files.


If you run into trouble:
Please check the detailed PDF-Manual which is available at http://www.webdeerns.de:
fckeditor_integration_in_CMSimple.pdf

***

Customize FCKeditor for CMSimple:

Please notice that from now on all configurations will be stored 
in "/FCKeditor/custom_configurations/"!

For easier updates, please apply all your configuration changes 
to /FCKeditor/custom_configurations/fckconfig_cmsimple.js 
and/or move other changed configuration files to this folder

To adjust the editarea to your website style, it's recommended to apply the 
basic css - settings of your template to 
/FCKeditor/custom_configurations/custom_fck_editorarea.css.

Anyway, you can try to use your template-css by setting "$Use_Template_Css = true;" 
in /cmsimple/fckeditor.php

To apply your styles to the editors style-dropdownlist edit the file
/FCKeditor/custom_configurations/custom_fckstyles.xml

To apply templates to FCKeditors template-button edit the file
/FCKeditor/custom_configurations/custom_fcktemplates.xml


All other changes should be made in 
/FCKeditor/custom_configurations/fckconfig_cmsimple.js

!!! Remember to clear the browser cache on configuration-changes !!!

****
Read the enclosed Manual, which describes the installation and the fine-tuning of 
FCKEditor4CMSimple more detailled. 
Find there information how to add plugins, how to customize the configuration and 
many info more.. 
Check http://www.webdeerns.de to get the detailed manual

***

How to update the editor:
If you want to update the editor BY YOURSELF, please backup and keep the
following list of files with the configuration scripts and plugins:

/cmsimple/fckeditor.php

/FCKeditor/custom_configurations/*.*
/FCKeditor/editor/plugins/sitelink/*.* (and all your user-installed plugins)

Check this files on updates with a diff:
/FCKeditor/editor/filemanager/connectors/php/config.php
/FCKeditor/editor/filemanager/connector/php/connector.php ( added: case 'DeleteFile' )
/FCKeditor/editor/filemanager/connector/php/commands.php  ( added: function DeleteFile() )

CMSimple-Filebrowser:
FCKeditor/editor/filemanager/browser/cmsimple/*.*
***

