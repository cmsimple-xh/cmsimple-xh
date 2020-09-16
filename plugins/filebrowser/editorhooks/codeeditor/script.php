<?php

/**
 * Editorhook for internal filebrowser -> Codeeditor_XH
 */

$script = <<<EOS
<script type="text/javascript">
function setLink(url) {
    window.opener.codeeditor.insertURI(url);
    window.close();
}
</script>
EOS;

?>
