<?php

/**
 * @version $Id$
 */

/* utf-8 marker: äöü */

class XHFileBrowserView {

    var $partials = array();
    var $browserPath = '';
    var $basePath;
    var $baseDirectory;
    var $baseLink;
    var $currentDirectory;
    var $linkParams;
    var $linkPrefix;
    var $folders;
    var $subfolders;
    var $files;
    var $message = '';
    var $lang = array();

    function XHFileBrowserView() {
        global $sl, $pth, $plugin_tx;
        $lang = array();
        $langFile = $pth['folder']['plugins'] . basename(dirname(dirname(__FILE__))) . '/languages/';

        $langFile .= file_exists($langFile . $sl . '.php') ? $sl . '.php' : 'en.php';
        include_once $langFile;
        $this->lang = $plugin_tx['filebrowser'];
    }

    function folderList($folders) {
        global $tx, $plugin_tx;
        //     $title = $this->baseLink === 'images' ? 'Bilder' : 'Downloads';
        $title = isset($tx['title'][$this->baseLink]) ? utf8_ucfirst($tx['title'][$this->baseLink]) : ucfirst($this->baseLink . ' ' . $this->translate('folder')); // für Editorbrowser
        $html = '
           <ul>
              <li class="openFolder">
                   <a href="?' . $this->linkParams . '">' . $title . ' ' . $plugin_tx['filebrowser']['folder'] . '</a>';  // für CMSbrowser
        $html .= '            <ul>';
        foreach ($folders as $folder => $data) {
            if ($data['level'] == 2) {
                $html .= $data['linkList'];
            }
        }
        $html .='
                   </ul>
                </li>
            </ul>';
        return $html;
    }

    function folderLink($folder, $folders) {
        $link = str_replace('index.php','',$_SERVER['PHP_SELF']);
        $class = 'folder';
        if (substr($this->currentDirectory, 0, strlen($folder)) == $folder) {
            $class = 'openFolder';
        }
        $temp = explode('/', $folder);
        $html = '<li class="' . $class . '"><a href="' . $link . '?' . $this->linkParams . '&amp;subdir=' . $folder . '">' . end($temp) . '</a>';
        if (count($folders[$folder]['children']) > 0) {
            if (substr($this->currentDirectory, 0, strlen($folder)) !== $folder) {
                $class = 'unseen';
            }

            $html .= '
                        <ul class="' . $class . '">';
            foreach ($folders[$folder]['children'] as $child) {
                $html .= $this->folderLink($child, $folders);
            }
            $html .= '
                        </ul>';
        }
        $html .= '
            </li>';
        return $html;
    }

    function subfolderList($folders) {

        $html = '';
        if (is_array($folders) && count($folders) > 0) {
            $html = '<ul>';
            foreach ($folders as $folder) {
                $name = str_replace($this->currentDirectory, '', $folder);
                $html .= '<li class="folder">
                              <form style="display: inline;" method="POST" action="" onsubmit="return confirmFolderDelete(\'' . $this->translate('confirm_delete', $this->basePath . $folder) . '\');">
                                ' . tag('input type="image" src="' . $this->browserPath . 'css/icons/delete.gif" alt="delete" title="delete folder"') . '
                                ' . tag('input type="hidden" name="deleteFolder"') . '
                                ' . tag('input type="hidden" name="folder" value="' . $folder . '"') . '
                              </form>
                    <a href="?' . $this->linkParams . '&amp;subdir=' . $folder . '">' . $name . '</a></li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    function fileList($files) {
        global $tx;

        if (empty($files)) {
            return '';
        }
        $html = '<ul class="wurst">';
        $i = 0;
        $class = 'even';
        $fb = $_SESSION['xh_browser']; // FIXME: the view shouldn't know the model
        $imgs = $fb->usedImages();
        $base = $fb->browseBase;
        if ($base{0} == '.' && $base{1} == '/') {
            $base = substr($base, 2);
        }
        foreach ($files as $file) {
            if ($class == 'odd') {
                $class = 'even';
            } else {
                $class = 'odd';
            }
            $html .= '
                <li style="white-space:nowrap;" class="' . $class . '">
                    <form style="display: inline;" method="POST" action="" onsubmit="return confirmFileDelete(\'' . $this->translate('confirm_delete', $this->currentDirectory . $file) . '\');">
                        ' . tag('input type="image" src="' . $this->browserPath . 'css/icons/delete.gif" alt="delete" title="delete file"') . '
                        ' . tag('input type="hidden" name="deleteFile"') . '
                        ' . tag('input type="hidden" name="file" value="' . $file . '"') . '
                    </form>
                    <form method="POST" style="display:none;" action="" id="rename_' . $i . '">
                        ' . tag('input type="text" size="25" name="renameFile" value="' . $file . '" onmouseout="hideRenameForm(\'' . $i . '\');"') . '
                        ' . tag('input type="hidden" name="oldName" value="' . $file . '"') . '
                    </form>
                     <a style="position:relative" class="xhfbfile" href="#" onclick="return false" id="file_' . $i . '" ondblclick="showRenameForm(\'' . $i . '\', \'' . $this->translate('prompt_rename', $file) . '\');">' . $file;

            $ffn = $base . $fb->currentDirectory . $file;
            $usage = array_key_exists($ffn, $imgs)
                ? '<strong>' . $tx['images']['usedin'] . ':</strong>'
                    . tag('br') . implode(tag('br'), $imgs[$ffn])
                : '';

            if (is_array(@getimagesize($this->basePath . $this->currentDirectory . $file))) {
                $image = getimagesize($this->basePath . $this->currentDirectory . $file);
                $width = $image[0];
                $height = $image[1];
                if ($width > 100) {
                    $ratio = $width / $height;
                    $width = 100;
                    $height = $width / $ratio;
                }
                $html .= '<span style="position: relative;  z-index: 4; ">
                    <span style="font-weight: normal; border: none;">' . $image[0] . ' x ' . $image[1] . ' px</span>' . tag('br') . '
                    ' . tag('img src="' . $this->basePath . $this->currentDirectory . $file . '" width="' . $width . 'px" height="' . $height . '" alt="' . $file . '"') . tag('br') . $usage . '</span>';
            }
            $html .= '</a> (' . round(filesize($this->basePath . $this->currentDirectory . $file) / 1024, 1) . ' kb)
            </li>';
            $i++;
        }
        $html .= '</ul>';
        return $html;
    }

    function fileListForEditor($files) {

        $html = '<ul>';
        $dir = $this->basePath . $this->currentDirectory;
        $is_image = (int) (strpos($this->linkParams, 'type=images') === 0);
        $class = 'even';
        foreach ($files as $file) {
            $class = $class == 'odd' ? 'even' : 'odd';


            $html .= '
                <li class="' . $class . '">';
            $prefix = $this->linkPrefix;

            if ($prefix != '?&amp;download=') {
                $prefix .= str_replace(array('../', './'), '', $this->currentDirectory);
            }
            //     $html .= '<a href="#" class="xhfbfile" onclick="window.setLink(\''.$prefix.  $file.'\'); return false;">'.$file;
            $html .= '<a href="#" class="xhfbfile" onclick="window.setLink(\'' . $prefix . $file . '\',' . $is_image . '); return false;">' . $file;

            if (strpos($this->linkParams, 'type=images') !== false && getimagesize($dir . $file)) {
                $image = getimagesize($dir . $file);
                $width = $image[0];
                $height = $image[1];
                if ($width > 150) {
                    $ratio = $width / $height;
                    $width = 150;
                    $height = $width / $ratio;
                }

                $html .= '<span style="position: relative; z-index: 4;">
                    <span style="font-weight: normal; border: none;">' . $image[0] . ' x ' . $image[1] . ' px</span>' . tag('br') . '
                    ' . tag('img src="' . $this->basePath . $this->currentDirectory . $file . '" width="' . $width . 'px" height="' . $height . '"') . '</span>';
            }
            $html .= '</a> (' . round(filesize($dir . $file) / 1024, 1) . ' kb)
            </li>';
        }
        $html .= '</ul>';
        return $html;
    }

    function loadTemplate($template) {
        if (file_exists($template)) {
            ob_start();
            global $tx;
            include $template;
        }
        $html = ob_get_clean();
        $this->partials['folders'] = $this->folderList($this->folders);
        $this->partials['subfolders'] = $this->subFolderList($this->subfolders);
        if (basename($template) == 'cmsbrowser.html') {
            $this->partials['files'] = $this->fileList($this->files);
        }
        if (basename($template) == 'editorbrowser.html') {
            $this->partials['files'] = $this->fileListForEditor($this->files);
        }
        $this->partials['message'] = $this->message;
        foreach ($this->partials as $placeholder => $value) {
            $html = str_replace('%' . strtoupper($placeholder) . '%', $value, $html);
        }
        $this->message = '';
        return $html;
    }

    function error($message ='', $args = null) {
        global $tx;
        $this->message .= '<p style="width: auto;" class="cmsimplecore_warning">' . $this->translate($message, $args) . '</p>';
    }

    function success($message, $args = null) {
        global $tx;
        $this->message .= '<p style="width: auto;">' . $this->translate($message, $args) . '</p>';
    }

    function message($message) {
        $this->message .= '<p style="width: auto;">' . $message . '</p>';
    }

    function translate($string = '', $args = null) {
        if (strlen($string) === 0) {
            return '';
        }
        $html = '';
        if (!isset($this->lang[$string])) {
            $html = '{' . $string . '}';
        } else {
            $html = $this->lang[$string];
        }
//
        if (is_array($args)) {

            array_unshift($args, $html);


            return call_user_func_array('sprintf', $args);
        }
        if (is_string($args)) {
            $html = sprintf($html, $args);
            return $html;
        }
        return $html;
    }

}