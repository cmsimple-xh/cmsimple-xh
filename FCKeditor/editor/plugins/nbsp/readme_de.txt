Non-breaking Space Plugin v1.0.1
For FCKEditor v2.2

Bartosz Rogozinski [REGE] (rege-tech@wsm24.com)
13-04-2006
License: LGPL



Description:
-------------
Dieses Plugin ermöglicht, einen geschützten Leerschritt in Text einzufügen.


Installation:
--------------
1. Datei entpacken
2. Das entpackte Verzeichnis in den .../editor/plugins Ordner kopieren
3. Das PlugIn hinzufügen: aktivieren Sie es indem Sie die nachstehende Zeile 
   entweder i fckconfig.js oder in einer anderen Konfigurations-Datei eintragen:

   FCKConfig.Plugins.Add('nbsp', 'de,en');

4. Fügen Sie 'nbsp' dem ToolbarSet hinzu, z.B.:

   FCKConfig.ToolbarSets["Basic"] = [
     ['nbsp','Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink','-','About']
   ];