<?php

function get_images($directory) {

    $files = array();
    $handle = opendir($directory);
    if(!$handle) { return ''; }

    $i = 0;

        while (false !== ($file = readdir($handle))) {
            if (preg_match("/(\.jpg$|\.gif$|\.png$|\.jpeg$)/i", $file)) {
                $files[$i]['name'] = $file;
                $files[$i]['path'] = $directory . "" . $file;
                $i++;
            }
        }


    closedir($handle);

    sort($files);
    $list = array();
    foreach ($files as $i) {
        $list[] = array('title'=> $i['name'], 'value'=>$i['path']);
    }
    return(XH_encodeJSON($list));
}

function get_internal_links($h, $u, $l, $sn, $downloads_path) {
    $list = array();
    for ($i = 0; $i < count($h); $i++) {
        $spacer = '';
        if ($l[$i] > 1) {
            $spacer = str_repeat('__', $l[$i] - 1);  // just for indenting lower level "pages"
        }
        $list[] = array('title' => $spacer . html_entity_decode(addcslashes($h[$i], "\n\r\t\"\\")) , 'value' =>'?' . $u[$i]);
    }
    if (is_dir($downloads_path)) {
        $list[] = array('title' => 'DOWNLOADS:' , 'value' => " ");
        $fs = sortdir($downloads_path);
        foreach ($fs as $p) {
            if (preg_match("/.+\..+$/", $p)) {
                $list[] = array('title' => '__' . substr($p, 0, 25) . ' (' . (round((filesize($downloads_path . '/' . $p)) / 102.4) / 10) . ' KB)' , 'value' => './?download=' . $p);
            }
        }
    }
    return(XH_encodeJSON($list));
}
