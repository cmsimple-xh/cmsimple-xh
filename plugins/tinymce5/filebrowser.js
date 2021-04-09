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
    filebrowserwindow = tinymce.activeEditor.windowManager.open({
        title: "File Manager",
        size: 'medium',
        body: {
            type: "panel",
            items: [{
                type: "htmlpanel",
                html: '<iframe src="' + cmsURL + '" style="width:100%" onload="top.resizeIframe(this)"></iframe>'
            }]
        },
        buttons: []
    });
    return false;
}
function resizeIframe(obj) {
  obj.style.height = obj.contentWindow.document.body.offsetHeight + 2 + 'px'; // no idea why border is not included
}
