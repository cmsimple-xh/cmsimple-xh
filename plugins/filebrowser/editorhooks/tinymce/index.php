<?php
/* utf-8 marker: äöü */
if($s < 0){  return '';}
$script = file_get_contents(dirname(__FILE__) . '/tinymce.js');
$base = CMSIMPLE_ROOT . 'plugins/';
$prefix = CMSIMPLE_BASE;
$script = str_replace('%URL%',  $base . 'filebrowser/editorbrowser.php?editor=tinymce&prefix='. $prefix .'&base=./&level=' . $l[$s], $script);

return $script;
/*
 * end of plugins/wr_filebrowser/tinymce.php
 */
?>