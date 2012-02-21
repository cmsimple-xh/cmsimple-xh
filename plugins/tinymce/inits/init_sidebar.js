{
    // General options

    mode    : "specific_textareas",
    editor_selector : /%INIT_CLASSES%/,

    theme : "advanced",
    element_format : "%ELEMENT_FORMAT%",
//    relative_urls      : false,
//    remove_script_host : true,
//    document_base_url : "%BASE_URL%",
    language : "%LANGUAGE%",
    plugins : "autosave,pagebreak,style,layer,table,save,advimage,advlink,advhr,emotions,iespell,"
            + "insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,"
            + "noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,lists",
  /*
    style_formats : [


        {title : "Normal", block : "p", classes : true , remove : "all", exact : false},
        {title : "Teaser", block : "p", classes : "teaser", exact : true},
        {title : "Zitat",  block : "p", classes : "zitat", exact : false },

    ],
    */
    // Theme options
    theme_advanced_buttons1           : "save,fullscreen,formatselect,help",
    theme_advanced_buttons2           : "",
    theme_advanced_buttons3           : "",
    theme_advanced_toolbar_location   : "top",
    theme_advanced_toolbar_align      : "left",
    theme_advanced_statusbar_location : "bottom",
    theme_advanced_resizing           : true,
    theme_advanced_blockformats       : "h1,h2,h3,p,div,h4,h5,h6,blockquote,dt,dd,code",
    theme_advanced_font_sizes : "8px=8px, 10px=10px,12px=12px, 14px=14px, 16px=16px, 18px=18px,20px=20px,24px=24px,36px=36px",


//    height : "%EDITOR_HEIGHT%",
    content_css   : "%STYLESHEET%",

    external_image_list_url : "%TINY_FOLDER%cms_image_list.js",
    external_link_list_url  : "%TINY_FOLDER%cms_link_list.js",

    // Extra
    plugin_insertdate_dateFormat : "%d-%m-%Y",
    plugin_insertdate_timeFormat : "%H:%M:%S",
    apply_source_formatting      : true,
    relative_urls : true,
    convert_urls: false,
    entity_encoding : "raw",

//  entity_encoding : "'.$plugin_cf['tinymce']['entity_encoding'].'",


    file_browser_callback: "%FILEBROWSER_CALLBACK%" ,
    fullscreen_new_window : false ,
    fullscreen_settings : {
        theme_advanced_buttons1: "save,|,fullscreen,code,|,formatselect,fontselect,fontsizeselect,styleselect,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,cut,copy,paste,pastetext,pasteword,|,bullist,numlistoutdent,indent,blockquote,|,undo,redo",
    theme_advanced_buttons2 : "link,unlink,anchor,image,media,cleanup,|,hr,removeformat,visualaid,|,forecolor,backcolor,|,search,replace,|,charmap,emotions,|,sub,sup,tablecontrols,insertdate,inserttime,|,help",
	theme_advanced_buttons3 : ""
    }
}