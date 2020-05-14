<?php

/**
 * Editor Links Server Functions -- links.php
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Tinymce4
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/**
 * Get Images List.
 *
 * @param string $directory Path to image directory.
 *
 * @return JSON structured images list
 */
function get_images($directory) 
{

    $files = array();
    $handle = opendir($directory);
    if (!$handle) {
        return ''; 
    }

    $i = 0;

    while (false !== ($file = readdir($handle))) {
        if (preg_match("/(\.jpg$|\.gif$|\.png$|\.jpeg$)/ui", $file)) {
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

/**
 * Get Internal Link List to Images/Downloads.
 *
 * @param array  $h              headings array.
 * @param array  $u              URL array.
 * @param array  $l              menulevel array.
 * @param string $downloads_path downloads folder path.
 *
 * @return JSON structured images / downloads list
 */
function get_internal_links($h, $u, $l, $downloads_path)
{
    $list = array();
    for ($i = 0; $i < count($h); $i++) {
        $spacer = '';
        if ($l[$i] > 1) {
            $spacer = str_repeat('__', $l[$i] - 1);  // just for
                                                     // indenting lower level "pages"
        }
        $list[] = array('title' => 
            $spacer . 
            html_entity_decode(addcslashes($h[$i], "\n\r\t\"\\")) ,
                'value' =>'?' . $u[$i]);
    }
    if (is_dir($downloads_path)) {
        $list[] = array('title' => 'DOWNLOADS:' , 'value' => " ");
        $fs = sortdir($downloads_path);
        foreach ($fs as $p) {
            if (preg_match("/.+\..+$/u", $p)) {
                $list[] = array('title' => '__' . utf8_substr($p, 0, 25) . 
                ' (' . 
                intval(filesize($downloads_path . '/' . $p) / 1024) . 
                ' KB)' , 'value' => './?download=' . $p);
            }
        }
    }
    return(XH_encodeJSON($list));
}
