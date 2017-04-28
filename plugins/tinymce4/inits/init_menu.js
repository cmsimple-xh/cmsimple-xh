{
  selector: "%SELECTOR%",
  theme: "modern",
  plugins: [
    "advlist anchor autolink autosave charmap code colorpicker contextmenu emoticons fullscreen hr",
    "image importcss insertdatetime link lists media nonbreaking paste",
    "save searchreplace table textcolor visualblocks visualchars wordcount"
   ],
  toolbar1: "save undo redo | formatselect fontselect fontsizeselect unlink",
  menu: [
    {"title":"Edit", "items":"undo redo | cut copy paste pastetext | selectall | searchreplace"},
    {"title":"Insert", "items":"media link image | charmap hr anchor pagebreak insertdatetime nonbreaking template"},
    {"title":"View", "items":"visualchars visualblocks visualaid | preview fullscreen | code"},
    {"title":"Format", "items":"bold italic underline strikethrough superscript subscript | formats | removeformat"},
    {"title":"Table", "items":"inserttable tableprops | cell row column"}
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