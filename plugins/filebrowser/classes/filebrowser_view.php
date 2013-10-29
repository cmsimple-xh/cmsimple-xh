<?php

/**
 * Internal Filebrowser -- filebrowser_view.php
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Filebrowser
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * The file browser view.
 *
 * @category CMSimple_XH
 * @package  Filebrowser
 * @author   Martin Damken <kontakt@zeichenkombinat.de>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 *
 * @todo Document meaning of properties.
 * @todo Document @access for members.
 */
class XHFileBrowserView
{
    /**
     * @var array $partials
     */
    var $partials = array();

    /**
     * @var string $browserPath
     */
    var $browserPath = '';

    /**
     * @var string $basePath
     */
    var $basePath;

    /**
     * @var string $baseDirectory
     */
    var $baseDirectory;

    /**
     * @var string $baseLink
     */
    var $baseLink;

    /**
     * @var string $currentDirectory
     */
    var $currentDirectory;

    /**
     * @var string $linkParams
     */
    var $linkParams;

    /**
     * @var string $linkPrefix
     */
    var $linkPrefix;

    /**
     * @var array $folders
     */
    var $folders;

    /**
     * @var array $subfolders
     */
    var $subfolders;

    /**
     * @var array $files
     */
    var $files;

    /**
     * @var string $message
     */
    var $message = '';

    /**
     * The localization of the file browser.
     *
     * @var array $lang
     */
    var $lang = array();

    /**
     * Constructs an instance.
     *
     * @global string The current language.
     * @global array  The paths of system files and folders.
     * @global array  The localization of the plugins.
     */
    function XHFileBrowserView()
    {
        global $sl, $pth, $plugin_tx;

        $lang = array();
        $langFile = $pth['folder']['plugins']
            . basename(dirname(dirname(__FILE__))) . '/languages/';
        $langFile .= file_exists($langFile . $sl . '.php')
            ? $sl . '.php' : 'en.php';
        include_once $langFile;
        $this->lang = $plugin_tx['filebrowser'];
    }

    /**
     * Returns the folder list view for the file browser.
     *
     * @param array $folders An array of folders.
     *
     * @return string
     *
     * @global array The localization of the core.
     * @global array The localization of the plugins.
     *
     * @todo Remove global $plugin_tx; use $this->lang instead.
     */
    function folderList($folders)
    {
        global $tx, $plugin_tx;

        $title = isset($tx['title']['userfiles'])
            ? utf8_ucfirst($tx['title']['userfiles'])
            : ucfirst('Userfiles ' . $this->translate('folder'));
        $html = '<ul><li class="openFolder"><a href="?'
            . htmlspecialchars($this->linkParams, ENT_QUOTES, 'UTF-8') . '">'
            . $title . ' ' . $plugin_tx['filebrowser']['folder'] . '</a>';
        if (!empty($folders)) {
            $html .= '<ul>';
            foreach ($folders as $folder => $data) {
                if ($data['level'] == 2) {
                    $html .= $data['linkList'];
                }
            }
            $html .='</ul>';
        }
        $html .= '</li></ul>';
        return $html;
    }

    /**
     * ???
     *
     * @param string $folder  The folder name.
     * @param array  $folders An array of folders.
     *
     * @return string
     */
    function folderLink($folder, $folders)
    {
        // TODO: Do we need PHP_SELF here; might allow for XSS.
        $link = str_replace('index.php', '', $_SERVER['PHP_SELF']);
        $class = 'folder';
        if (substr($this->currentDirectory, 0, strlen($folder)) == $folder) {
            $class = 'openFolder';
        }
        $temp = explode('/', $folder);
        $html = '<li class="' . $class . '"><a href="' . $link . '?'
            . htmlspecialchars($this->linkParams, ENT_QUOTES, 'UTF-8')
            . '&amp;subdir=' . $folder . '">' . end($temp) . '</a>';
        if (count($folders[$folder]['children']) > 0) {
            if (substr($this->currentDirectory, 0, strlen($folder)) !== $folder) {
                $class = 'unseen';
            }

            $html .= '<ul class="' . $class . '">';
            foreach ($folders[$folder]['children'] as $child) {
                $html .= $this->folderLink($child, $folders);
            }
            $html .= '</ul>';
        }
        $html .= '</li>';
        return $html;
    }

    /**
     * ???
     *
     * @param array $folders An array of folders.
     *
     * @return string
     *
     * @global object The CRSF protection object.
     */
    function subfolderList($folders)
    {
        global $_XH_csrfProtection;

        $html = '';
        if (is_array($folders) && count($folders) > 0) {
            $html = '<ul>';
            foreach ($folders as $folder) {
                $name = str_replace($this->currentDirectory, '', $folder);
                $html .= '<li class="folder">'
                    . '<form style="display: inline;" method="post" action="#"'
                    . ' onsubmit="return FILEBROWSER.confirmFolderDelete(\''
                    . $this->translate('confirm_delete', $this->basePath . $folder)
                    . '\');">'
                    . tag(
                        'input type="image" src="' . $this->browserPath
                        . 'css/icons/delete.png" alt="delete" title="'
                        . $this->translate('delete_folder') . '"'
                    )
                    . tag('input type="hidden" name="deleteFolder"')
                    . tag(
                        'input type="hidden" name="folder" value="' . $folder . '"'
                    )
                    . $_XH_csrfProtection->tokenInput()
                    . '</form>'
                    . '<a href="?' . $this->linkParams . '&amp;subdir=' . $folder
                    . '">' . $name . '</a></li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    /**
     * ???
     *
     * @param array $folders An array of folders.
     *
     * @return string
     */
    function subfolderListForEditor($folders)
    {
        $html = '';
        if (is_array($folders) && count($folders) > 0) {
            $html = '<ul>';
            foreach ($folders as $folder) {
                $name = str_replace($this->currentDirectory, '', $folder);
                $html .= '<li class="folder">'
                    . '<form style="display: inline;" method="post" action="#"'
                    . ' onsubmit="return FILEBROWSER.confirmFolderDelete(\''
                    . $this->translate('confirm_delete', $this->basePath . $folder)
                    . '\');">'
                    . '<input type="image" src="' . $this->browserPath
                    . 'css/icons/delete.png" alt="delete" title="'
                    . $this->translate('delete_folder') . '" />'
                    . '<input type="hidden" name="deleteFolder" />'
                    . '<input type="hidden" name="folder" value="' . $folder
                    . '" />'
                    . '</form>'
                    . '<a href="?' . $this->linkParams . '&amp;subdir=' . $folder
                    . '">' . $name . '</a></li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    /**
     * Returns whether a file is an image file.
     *
     * @param string $filename A file name.
     *
     * @return bool
     */
    function isImageFile($filename)
    {
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME);
            $mimeType = $finfo->file($filename);
            return strpos($mimeType, 'image/') === 0;
        } elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filename);
            return strpos($mimeType, 'image/') === 0;
        } else {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $exts = array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'tiff', 'ico');
            return in_array(strtolower($ext), $exts);
        }
    }

    /**
     * Returns the file list view for the CMS browser.
     *
     * @param array $files An array of files.
     *
     * @return string
     *
     * @global array  The localization of the core.
     * @global object The CRSF protection object.
     */
    function fileList($files)
    {
        global $tx, $_XH_csrfProtection;

        if (empty($files)) {
            return '';
        }
        $html = '<ul>';
        $i = 0;
        $class = 'even';
        $fb = $_SESSION['xh_browser']; // FIXME: the view shouldn't know the model
        $imgs = $fb->usedImages();
        $base = $fb->browseBase;
        if ($base{0} == '.' && $base{1} == '/') {
            $base = substr($base, 2);
        }
        foreach ($files as $file) {
            $class = $class == 'odd' ? 'even' : 'odd';
            $html .= '<li style="white-space:nowrap;" class="' . $class . '">'
                . '<form style="display: inline;" method="post" action="#"'
                . ' onsubmit="return FILEBROWSER.confirmFileDelete(\''
                . $this->translate('confirm_delete', $this->currentDirectory . $file)
                . '\');">'
                . tag(
                    'input type="image" src="' . $this->browserPath
                    . 'css/icons/delete.png" alt="delete" title="'
                    . $this->translate('delete_file')
                    . '" style="width: 16px; height: 16px"'
                )
                . tag('input type="hidden" name="deleteFile"')
                . tag(
                    'input type="hidden" name="filebrowser_file" value="'
                    . $file . '"'
                )
                . $_XH_csrfProtection->tokenInput()
                . '</form>'
                . '<form method="post" style="display:none;" action="#"'
                . ' id="rename_' . $i . '">'
                . tag(
                    'input type="text" size="25" name="renameFile" value="'
                    . $file . '" onmouseout="FILEBROWSER.hideRenameForm(\''
                    . $i . '\');"'
                )
                . tag('input type="hidden" name="oldName" value="' . $file . '"')
                . $_XH_csrfProtection->tokenInput()
                . '</form>'
                . tag(
                    'img src="' . $this->browserPath . 'css/icons/rename.png"'
                    . ' alt="' . $this->translate('rename_file') . '" title="'
                    . $this->translate('rename_file')
                    . '" style="width: 16px; height: 16px; cursor: pointer"'
                    . ' onclick="FILEBROWSER.showRenameForm(\'' . $i . '\', \''
                    . $this->translate('prompt_rename', $file) . '\');"'
                )
                . '<a style="position:relative" class="xhfbfile" href="'
                . $this->currentDirectory . $file . '" target="_blank">' . $file;

            $ffn = $base . $fb->currentDirectory . $file;
            $usage = array_key_exists($ffn, $imgs)
                ? '<strong>' . $this->translate('image_usedin') . '</strong>'
                    . tag('br') . implode(tag('br'), $imgs[$ffn])
                : '';

            $path = $this->basePath . $this->currentDirectory . $file;
            if ($this->isImageFile($path) && ($image = getimagesize($path))) {
                list($width, $height) = $image;
                if ($width > 100) {
                    $ratio = $width / $height;
                    $width = 100;
                    $height = $width / $ratio;
                }
                $html .= '<span style="position: relative;  z-index: 4; ">'
                    . '<span style="font-weight: normal; border: none;">'
                    . $width . ' x ' . $height . ' px</span>' . tag('br')
                    . tag(
                        'img src="' . $path . '" width="' . $width . '" height="'
                        . $height . '" alt="' . $file . '"'
                    ) . tag('br') . $usage . '</span>';
            }
            $size = round(filesize($path) / 1024, 1);
            $html .= '</a> (' . $size . ' kb)</li>';
            $i++;
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Returns the file list view for the editor browser.
     *
     * @param array $files An array of files.
     *
     * @return string
     */
    function fileListForEditor($files)
    {
        if (empty($files)) {
            return '';
        }
        $html = '<ul>';
        $dir = $this->basePath . $this->currentDirectory;
        $is_image = (int) (strpos($this->linkParams, 'type=images') === 0);
        $class = 'even';
        foreach ($files as $file) {
            $class = $class == 'odd' ? 'even' : 'odd';

            $html .= '<li class="' . $class . '">';
            $prefix = $this->linkPrefix;

            if ($prefix != '?&amp;download=') {
                $prefix .= str_replace(
                    array('../', './'), '', $this->currentDirectory
                );
            }
            $html .= '<span class="xhfbfile" onclick="window.setLink(\''
                . $prefix . $file . '\',' . $is_image . ');">'
                . $file;

            $path = $dir . $file;
            if (strpos($this->linkParams, 'type=images') !== false
                && $this->isImageFile($path) && ($image = getimagesize($path))
            ) {
                list($width, $height) = $image;
                if ($width > 150) {
                    $ratio = $width / $height;
                    $width = 150;
                    $height = $width / $ratio;
                }
                $src = $this->basePath . $this->currentDirectory . $file;
                $html .= <<<HTM
<span style="position: relative; z-index: 4;">
<span style="font-weight: normal; border: none;">$width x $height px</span>
<br /><img src="$src" width="$width" height="$height" alt="$file"/></span>
HTM;
            }
            $html .= '</span> (' . round(filesize($path) / 1024, 1)
                . ' kb)</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Returns an instantiated template.
     *
     * @param string $template A template file name.
     *
     * @return string
     *
     * @global array The localization of the core.
     */
    function loadTemplate($template)
    {
        global $tx;

        if (file_exists($template)) {
            ob_start();
            include $template;
        }
        $html = ob_get_clean();
        $this->partials['folders'] = $this->folderList($this->folders);
        if (basename($template) == 'cmsbrowser.html') {
            $this->partials['subfolders']
                = $this->subFolderList($this->subfolders);
            $this->partials['files'] = $this->fileList($this->files);
        } elseif (basename($template) == 'editorbrowser.html') {
            $this->partials['subfolders']
                = $this->subFolderListForEditor($this->subfolders);
            $this->partials['files'] = $this->fileListForEditor($this->files);
        }
        $this->partials['message'] = $this->message;
        foreach ($this->partials as $placeholder => $value) {
            $html = str_replace(
                '%' . strtoupper($placeholder) . '%', $value, $html
            );
        }
        $this->message = '';
        return $html;
    }

    /**
     * Appends a localized error message to the message area of the view.
     *
     * @param string $message A message key.
     * @param array  $args    The arguments.
     *
     * @return void
     */
    function error($message ='', $args = null)
    {
        $this->message .= '<p class="cmsimplecore_fail">'
            . $this->translate($message, $args) . '</p>';
    }

    /**
     * Appends a localized success message to the message area of the view.
     *
     * @param string $message A message key.
     * @param array  $args    The arguments.
     *
     * @return void
     */
    function success($message, $args = null)
    {
        $this->message .= '<p class="cmsimplecore_success">'
            . $this->translate($message, $args) . '</p>';
    }

    /**
     * Appends a localized info message to the message area of the view.
     *
     * @param string $message A message key.
     * @param array  $args    The arguments.
     *
     * @return void
     */
    function info($message, $args = null)
    {
        $this->message .= '<p class="cmsimplecore_info">'
            . $this->translate($message, $args) . '</p>';
    }

    /**
     * Appends a message to the message area of the view.
     *
     * @param string $message A message.
     *
     * @return void
     *
     * @todo Deprecate? All messages should be localized.
     */
    function message($message)
    {
        $this->message .= '<p style="width: auto;">' . $message . '</p>';
    }

    /**
     * Returns a localized message.
     *
     * @param string $string A message key.
     * @param mixed  $args   A single argument or an array of arguments.
     *
     * @return string
     */
    function translate($string = '', $args = null)
    {
        if (strlen($string) === 0) {
            return '';
        }
        $html = '';
        if (!isset($this->lang[$string])) {
            $html = '{' . $string . '}';
        } else {
            $html = $this->lang[$string];
        }
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

?>
