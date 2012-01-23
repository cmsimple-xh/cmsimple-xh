<?php
/* utf-8 marker: äöü */
$script = '
<script type="text/javascript" src="./../tinymce/tiny_mce/tiny_mce_popup.js">
</script>

<script language="javascript" type="text/javascript">

var FileBrowserDialogue = {
    
    init : function () {
        // Nothing to do
    },

   
    submit : function (url) {
        var URL = url;
        var win = tinyMCEPopup.getWindowArg("window");

        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;


        if (typeof(win.ImageDialog) != "undefined")
        {
            if (win.ImageDialog.getImageData) win.ImageDialog.getImageData();
            if (win.ImageDialog.showPreviewImage) win.ImageDialog.showPreviewImage(URL);
        }
       tinyMCEPopup.close();
    }
}

tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);

function setLink(link){

    FileBrowserDialogue.submit(link);
    return true;
}

</script>';
?>
