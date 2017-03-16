<?php

$script = '
<script language="javascript" type="text/javascript">


var FileBrowserDialogue = {

    init : function () {
        // Nothing to do
    },


    submit : function (url) {
        var URL = url;
        var args = top.tinymce.activeEditor.windowManager.getParams();
        var win = args.window;
        var input = win.document.getElementById(args.input);

        input.value = URL;
        if (input.onchange) input.onchange();   //??? falls noch ein anderer trigger ???
        top.tinymce.activeEditor.windowManager.close();
    }
}


function setLink(link){

    FileBrowserDialogue.submit(link);
    return true;
}

</script>';
?>
