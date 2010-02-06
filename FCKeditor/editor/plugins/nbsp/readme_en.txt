Non-breaking Space Plugin v1.0.1
For FCKEditor v2.2

Bartosz Rogozinski [REGE] (rege-tech@wsm24.com)
13-04-2006
License: LGPL



Description:
-------------
This plugin allows you to insert a non-breaking space in the editor.


Installation:
--------------
1. Unzip the file
2. Copy the 'nbsp' folder to your .../editor/plugins folder
3. Add the plugin by placing it of the following line in either fckconfig.js or your custom configuration file:

   FCKConfig.Plugins.Add('nbsp', 'en,pl');

4. Add 'nbsp' to your ToolbarSet e.g.:

   FCKConfig.ToolbarSets["Basic"] = [
     ['nbsp','Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink','-','About']
   ];