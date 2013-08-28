{
  "selector": "textarea#text",
  "theme": "modern",
  "height": 600,
  "plugins": [
       "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak spellchecker",
       "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
       "save table contextmenu directionality emoticons template paste textcolor"
   ],
  "image_advtab": true,

  "toolbar": "|", 
  "style_formats": [
    {"title": "Bold text", "inline": "b"},
    {"title": "Red text", "inline": "span", "styles": {"color": "#ff0000"}},
    {"title": "Red header", "block": "h1", "styles": {"color": "#ff0000"}},
    {"title": "Example 1", "inline": "span", "classes": "example1"},
    {"title": "Example 2", "inline": "span", "classes": "example2"},
    {"title": "Table styles"},
    {"title": "Table row 1", "selector": "tr", "classes": "tablerow1"}
  ],
  "menu": [
    {"title":"File", "items":"print"},
    {"title":"Edit", "items":"undo redo | cut copy paste pastetext | selectall | searchreplace"},
    {"title":"Insert", "items":"media link image | charmap hr anchor pagebreak insertdatetime nonbreaking template"},
    {"title":"View", "items":"visualchars visualblocks visualaid | preview fullscreen | code"},
    {"title":"Format", "items":"bold italic underline strikethrough superscript subscript | formats | removeformat"},
    {"title":"Table", "items":"inserttable tableprops | cell row column"}
  ],
  "file_browser": true, 
  "image_list": true,
  "link_list": true,
  "insertdate_formats": ["%H:%M:%S", "%d.%m.%Y", "%I:%M:%S %p", "%D"],
  "relative_urls" : true,
  "convert_urls" : false,
  "entity_encoding" : "raw"
 }