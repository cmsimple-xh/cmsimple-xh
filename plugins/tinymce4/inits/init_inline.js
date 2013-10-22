{
  selector: "%SELECTOR%",
  theme: "modern",
  inline: true,
  plugins: [
  "advlist anchor autolink autosave charmap code contextmenu emoticons fullscreen hr",
  "image importcss insertdatetime link lists media nonbreaking paste",
  "save searchreplace table textcolor visualblocks visualchars wordcount"
   ],
  toolbar: "save undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent", 
  image_advtab: true,
  file_browser_callback : "%FILEBROWSER_CALLBACK%",
  content_css: "%STYLESHEET%",
  importcss_append:true,
  importcss_selector_filter: /(?:([a-z0-9\-_]+))(\.[a-z0-9_\-\.]+)$/i,
  language: "%LANGUAGE%",
  element_format: "%ELEMENT_FORMAT%",
  block_formats: "%BLOCK_FORMATS%",
  insertdatetime_formats: ["%H:%M:%S", "%d.%m.%Y", "%I:%M:%S %p", "%D"],
  relative_urls: true,
  convert_urls: false,
  entity_encoding: "raw"  
 }