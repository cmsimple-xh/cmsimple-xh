<?php

/**
 * Editorhook for internal filebrowser -> Codeeditor_XH
 *
 * @version $Id$
 */

$script = <<<EOS
<script type="text/javascript">
/* <![CDATA[ */
function setLink(url) {
    window.opener.codeeditor.insertURI(url);
    window.close();
}
/* ]]> */
</script>
EOS;

?>
