{
  selector: "%SELECTOR%",
  menubar:false,
  plugins: [
    "advlist anchor autolink autosave charmap code codesample emoticons fontawesome fullscreen help hr",
    "image imagetools importcss insertdatetime link lists media nonbreaking paste preview",
    "save searchreplace table template visualblocks visualchars wordcount noneditable xhplugincall"
  ],
  external_plugins: {
    "fontawesome": "%CMSIMPLE_ROOT%plugins/fa/editors/tinymce5/fontawesome/plugin.min.js",
    "xhplugincall":"%CMSIMPLE_ROOT%plugins/tinymce5/tinymce/plugins/xhplugincall/plugin.min.js"
  },
  toolbar: "save | fullscreen code undo redo styleselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify cut copy paste pastetext | bullist numlist outdent indent blockquote | link unlink anchor image media | hr nonbreaking removeformat visualblocks visualchars | forecolor backcolor | searchreplace | charmap emoticons subscript superscript | table insertdatetime help | xhplugincall",
  toolbar_mode: 'sliding',
  table_advtab: true,
  table_cell_advtab: true,
  table_style_by_css: true,
  autosave_interval: "20s",
  image_advtab: true,
  image_title: true,
  file_picker_callback: "%FILEBROWSER_CALLBACK%",
  content_css: "%STYLESHEET%,%CMSIMPLE_ROOT%plugins/fa/css/font-awesome.min.css",
  importcss_append:true,
  style_formats_autohide: true,
  importcss_selector_filter: /(?:([a-z0-9\-_]+))(\.[a-z0-9_\-\.]+)$/i,
  %LANGUAGE%
  element_format: "html",
  "insertdatetime_formats": ["%H:%M:%S", "%d.%m.%Y", "%I:%M:%S %p", "%D"],
  relative_urls: true,
  convert_urls: false,
  entity_encoding: "raw",
  noneditable_noneditable_class: 'fa',
  noneditable_noneditable_class: 'mceNonEditable',  
  extended_valid_elements: 'span[*]',
  paste_data_images: true,
  images_upload_url : "./?tinymce5=imageuploader",
//  images_upload_base_path :"",
  images_upload_credentials: true,
  automatic_uploads: true,
  images_reuse_filename: false
}
