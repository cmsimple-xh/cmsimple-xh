<?php
$plugin_mcf['tinymce4']['init']="function:tinymce_getInits";
$plugin_mcf['tinymce4']['utf8_marker']="hidden";
if (TINYMCE4_VARIANT != 'CDN') $plugin_mcf['tinymce4']['CDN_alt_src']="hidden";
$plugin_mcf['tinymce4']['CDN_src']="hidden"; //obsolete
?>