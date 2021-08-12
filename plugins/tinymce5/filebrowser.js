function wrFilebrowser (callback, value, meta) {
    var cmsURL = "%URL%";
    var type = meta.filetype;

    if (type == "file") {
        type = "downloads"
    };

    if (cmsURL.indexOf("?") < 0) {
        cmsURL = cmsURL + "?type="+ type;
    } else {
        cmsURL = cmsURL + "&type=" + type;
    }

    // FIXME: avoid the following two global variables!
    filebrowsercallback = callback;
    filebrowserwindow = tinymce.activeEditor.windowManager.openUrl({
        title: "Filebrowser",
        width: 800,
        url: cmsURL
    });
    return false;
}
