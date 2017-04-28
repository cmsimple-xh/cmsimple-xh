{
  selector: "%SELECTOR%",
  theme: "modern",
  menubar:false,
  plugins: [
  "autolink autosave contextmenu fullscreen",
  "image link lists nonbreaking",
  "save table wordcount"
  ],
  toolbar: "save fullscreen styleselect bold italic", 
  "style_formats": [
      {"title": "Bold text", "inline": "b"},
      {"title": "Red text", "inline": "span", "styles": {"color": "#ff0000"}},
      {"title": "Red header", "block": "h1", "styles": {"color": "#ff0000"}},
      {"title": "Example 1", "inline": "span", "classes": "example1"},
      {"title": "Example 2", "inline": "span", "classes": "example2"},
      {"title": "Table styles"},
      {"title": "Table row 1", "selector": "tr", "classes": "tablerow1"}
  ],
  image_advtab: true,
  image_title: true,
  file_browser_callback : "%FILEBROWSER_CALLBACK%",
  content_css: "%STYLESHEET%",
  importcss_append:true,
  importcss_selector_filter: /(?:([a-z0-9\-_]+))(\.[a-z0-9_\-\.]+)$/i,
  %LANGUAGE%
  element_format: "%ELEMENT_FORMAT%",
  block_formats: "%HEADERS%;p=p;div=div;code=code;pre=pre;dt=dt;dd=dd",
  insertdatetime_formats: ["%H:%M:%S", "%d.%m.%Y", "%I:%M:%S %p", "%D"],
  relative_urls: true,
  convert_urls: false,
  entity_encoding: "raw"
 }
