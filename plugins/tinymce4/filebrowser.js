
function wrFilebrowser (field_name, url, type, win) {
  poppedUpWin = win;
  inputField = field_name;
  if (type == "file") {type = "downloads"};
    var cmsURL = "%URL%";    

    if (cmsURL.indexOf("?") < 0) {
        cmsURL = cmsURL + "?type="+ type ;
    }
    else {
        cmsURL = cmsURL + "&type="+type ;
    }

    tinymce.activeEditor.windowManager.open(
        {
            title: 'File Manager',
            url  : cmsURL,
            width : 800,
            height : 600,
//            resizable : "yes",
            inline : 1,
//            close_previous : "no",
//            popup_css : false,
//            scrollbars : "yes",
          },
          {
            window : win,
            input : field_name
          }
    );
    return false;
  }

  