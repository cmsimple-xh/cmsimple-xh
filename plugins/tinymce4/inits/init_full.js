{
  selector: "%SELECTOR%",
  theme: "modern",
  skin: "lightgray",
  toolbar_items_size: "small",
  menubar:false,
  plugins: [
    "advlist anchor autolink autosave charmap code colorpicker contextmenu emoticons fullscreen  help hr",
    "image importcss insertdatetime link lists media nonbreaking noneditable paste",
    "save searchreplace table textcolor visualblocks visualchars wordcount xhplugincall"
  ],
  toolbar1: "save | fullscreen code formatselect fontselect fontsizeselect styleselect",
  toolbar2: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify cut copy paste pastetext | bullist numlist outdent indent blockquote",
  toolbar3: "undo redo | link unlink anchor image media | hr nonbreaking removeformat visualblocks visualchars | forecolor backcolor | searchreplace | charmap",
  toolbar4: "emoticons subscript superscript | table insertdatetime help | xhplugincall",
  image_advtab: true,
  image_title: true,
  file_browser_callback: "%FILEBROWSER_CALLBACK%",
  content_css: "%STYLESHEET%",
  importcss_append:true,
//inserted 30.11.2016  
  style_formats_autohide: true,
  importcss_selector_filter: /(?:([a-z0-9\-_]+))(\.[a-z0-9_\-\.]+)$/i,
  %LANGUAGE%
  element_format: "%ELEMENT_FORMAT%",
  block_formats: "%HEADERS%;p=p;div=div;code=code;pre=pre;dt=dt;dd=dd",
  "insertdatetime_formats": ["%H:%M:%S", "%d.%m.%Y", "%I:%M:%S %p", "%D"],
  relative_urls: true,
  convert_urls: false,
  entity_encoding: "raw",
  noneditable_noneditable_class: 'mceNonEditable'
 }