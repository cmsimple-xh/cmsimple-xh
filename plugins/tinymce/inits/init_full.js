{
    // General options
    theme : "advanced",
    element_format : "%ELEMENT_FORMAT%",
    language : "%LANGUAGE%",
    plugins : "advimage,advlink,autosave,contextmenu,emotions,fullscreen,insertdatetime,lists,media,paste,save,searchreplace,table,wordcount",

    /*
    style_formats : [
        {title : "Normal", block : "p", classes : true , remove : "all", exact : false},
        {title : "Teaser", block : "p", classes : "teaser", exact : true},
        {title : "Zitat",  block : "p", classes : "zitat", exact : false },
    ],
    */

    // Theme options
    theme_advanced_buttons1 : "save,|,fullscreen,code,formatselect,fontselect,fontsizeselect,styleselect",
    theme_advanced_buttons2 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,cut,copy,paste,pastetext,pasteword,|,bullist,numlist,outdent,indent,blockquote",
    theme_advanced_buttons3 : "undo,redo,|,link,unlink,anchor,image,media,cleanup,|,hr,removeformat,visualaid,|,forecolor,backcolor,|,search,replace,|,charmap",
    theme_advanced_buttons4 : "emotions,sub,sup,|,tablecontrols,insertdate,inserttime,help",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_statusbar_location : "bottom",
    theme_advanced_resizing : true,

    // %PAGEHEADERS% = h1...hx for new pages, %NAMED_PAGEHEADERS% =  1. Level pageheader=h1 ...hx, %HEADERS% = remaining hy...h6
    theme_advanced_blockformats : "%HEADERS%,p=p,div=div,%PAGEHEADERS%,code=code,pre=pre,dt=dt,dd=dd",
    theme_advanced_font_sizes : "8px=8px,10px=10px,12px=12px,14px=14px,16px=16px,18px=18px,20px=20px,24px=24px,36px=36px",

    content_css : "%STYLESHEET%",

    //link and image list
    external_image_list_url : "%TINY_FOLDER%cms_image_list.js",
    external_link_list_url : "%TINY_FOLDER%cms_link_list.js",

    // Extra
    plugin_insertdate_dateFormat: "%d-%m-%Y",
    plugin_insertdate_timeFormat: "%H:%M:%S",
    inline_styles : true,
    apply_source_formatting : true,
    relative_urls : true,
    convert_urls : false,
    entity_encoding : "raw",

    file_browser_callback : "%FILEBROWSER_CALLBACK%",
    fullscreen_new_window : false ,
    fullscreen_settings : {
	theme_advanced_buttons1 : "save,|,fullscreen,code,|,formatselect,fontselect,fontsizeselect,styleselect,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,cut,copy,paste,pastetext,pasteword,|,bullist,numlist,outdent,indent,blockquote",
	theme_advanced_buttons2  : "undo,redo,|,link,unlink,anchor,image,media,cleanup,|,hr,removeformat,visualaid,|,forecolor,backcolor,|,search,replace,|,charmap,emotions,|,sub,sup,|,tablecontrols,insertdate,inserttime,|,help",
	theme_advanced_buttons3 : "",
	theme_advanced_buttons4 : ""
    }
}
