<?php

/* script.php build: 2011012801 */
$script = '
<script>
 function setLink(link){

        //window.opener.CKEDITOR.tools.callFunction( 2, link );
		window.opener.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', link );

         window.close();
    }
</script>
';
