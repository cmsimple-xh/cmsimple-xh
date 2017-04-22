{
  selector: "%SELECTOR%",
  theme: "modern",
  skin: "lightgray",
  toolbar_items_size: "small",
  menubar: false,
  plugins: [
    "autolink autosave link image importcss lists",
    "wordcount fullscreen",
    "save table contextmenu paste"
  ],
  toolbar1: "save | fullscreen | bold italic underline | formatselect styleselect | bullist numlist | image link unlink",
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