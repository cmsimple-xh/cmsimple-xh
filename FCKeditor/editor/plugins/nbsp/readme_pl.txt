Non-breaking Space Plugin v1.0.1
For FCKEditor v2.2

Bartosz Rogozinski [REGE] (rege-tech@wsm24.com)
13-04-2006
License: LGPL



Opis:
------
Wtyczka ta umo¿liwia wstawianie do edytora tzw. "twardej spacji".


Instalacja:
------------
1. Rozpakuj plik
2. Skopiuj folder 'nbsp' do folderu .../editor/plugins
3. Dodaj wtyczkê do edytora wpisuj¹c odpowiedni fragment kodu do pliku fckconfig.js lub Twojego w³asnego pliku konfiguracyjnego:

   FCKConfig.Plugins.Add('nbsp', 'en,pl');

4. Dodaj wtyczkê 'nbsp' do paska narzêdzi np.:

   FCKConfig.ToolbarSets["Basic"] = [
     ['nbsp','Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink','-','About']
   ];