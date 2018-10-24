<?php

/**
 * The file browser view class.
 *
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace Filebrowser;

/**
 * The file browser view class.
 *
 * @author   Martin Damken <kontakt@zeichenkombinat.de>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 */
class View
{
    /**
     * HTML fragments to insert in the templates.
     *
     * @var array
     */
    public $partials = array();

    /**
     * Relative path of the filebrowser folder.
     *
     * @var string
     */
    public $browserPath = '';

    /**
     * Relative path of the CMSimple installation folder.
     *
     * @var string
     */
    public $basePath;

    /**
     * Relative path of the userfiles folder.
     *
     * @var string
     */
    public $baseDirectory;

    /**
     * The type of the files to browse ("images", "downloads", "media" or
     * "userfiles").
     *
     * @var string
     */
    public $baseLink;

    /**
     * The path of the current folder relative to the type's main folder.
     *
     * @var string
     */
    public $currentDirectory;

    /**
     * The (partial) query string.
     *
     * @var string
     */
    public $linkParams;

    /**
     * ???
     *
     * @var string
     *
     * @todo Document description.
     */
    public $linkPrefix;

    /**
     * An array of folders?
     *
     * @var array
     */
    public $folders;

    /**
     * An array of subfolders?
     *
     * @var array
     */
    public $subfolders;

    /**
     * An array of files?
     *
     * @var array
     */
    public $files;

    /**
     * The HTML content of the message area.
     *
     * @var string
     */
    public $message = '';

    /**
     * The localization of the file browser.
     *
     * @var array
     */
    private $lang = array();

    /**
     * Initializes a newly created instance.
     */
    public function __construct()
    {
        global $plugin_tx;

        $this->lang = $plugin_tx['filebrowser'];
    }

    /**
     * Returns the folder list view.
     *
     * @param array $folders An array of folders.
     *
     * @return string HTML
     */
    private function folderList(array $folders)
    {
        global $tx;

        $html = '<ul><li class="openFolder"><a href="?'
            . XH_hsc($this->linkParams) . '"><span class="fa fa-folder fa-fw fa-lg"></span> '
            . $tx['title']['userfiles'] . ' ' . $this->lang['folder'] . '</a>';
        if (!empty($folders)) {
            $html .= '<ul>';
            foreach ($folders as $data) {
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
     *
     * @todo What is this method for?
     */
    public function folderLink($folder, array $folders)
    {
        global $sn;

        $class = 'folder';
        if (substr($this->currentDirectory, 0, strlen("$folder/")) == "$folder/") {
            $class = 'openFolder';
        }
        $temp = explode('/', $folder);
        $html = '<li class="' . $class . '"><a href="' . $sn . '?'
            . XH_hsc($this->linkParams) . '&amp;subdir=' . $folder . '"><span class="fa fa-folder fa-fw fa-lg"></span> '
            . end($temp) . '</a>';
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
     * Returns the subfolder list view of the CMS browser.
     *
     * @param array $folders An array of folders.
     *
     * @return string
     */
    private function subfolderList(array $folders)
    {
        global $_XH_csrfProtection, $sn;

        $html = '';
        if (is_array($folders) && count($folders) > 0) {
            $action = $sn . '?' . XH_hsc($_SERVER['QUERY_STRING']);
            $html = '<ul>';
            foreach ($folders as $folder) {
                $name = str_replace($this->currentDirectory, '', $folder);
                $html .= '<li class="folder"><span class="fa fa-folder fa-fw fa-lg"></span>'
                    . '<form style="display: inline;" method="post" action="'
                    . $action . '" class="filebrowser_delete_folder" data-path="'
                    . XH_hsc($this->basePath) . $folder . '">'
                    . '<button class="xhButtonImg" title="'
                    . $this->translate('delete_folder') . '">'
                    .'<span class="fa fa-remove fa-lg fa-fw"></span></button>'
                    . '<input type="hidden" name="deleteFolder">'
                    . '<input type="hidden" name="folder" value="' . $folder . '">'
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
     * Returns whether a file is an image file.
     *
     * @param string $filename A file name.
     *
     * @return bool
     */
    private function isImageFile($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $exts = array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'tiff', 'ico');
        return in_array(strtolower($ext), $exts);
    }

    /**
     * Returns the file list view for the CMS browser.
     *
     * @param array $files An array of files.
     *
     * @return string
     */
    private function fileList(array $files)
    {
        global $sn, $_XH_csrfProtection, $_XH_filebrowser;

        if (empty($files)) {
            return '';
        }
        $html = '<ul>';
        $class = 'even';
        $fb = $_XH_filebrowser; // FIXME: the view shouldn't know the controller
        $imgs = $fb->usedImages();
        $base = $fb->browseBase;
        if ($base[0] == '.' && $base[1] == '/') {
            $base = substr($base, 2);
        }
        $action = $sn . '?' . XH_hsc($_SERVER['QUERY_STRING']);
        foreach ($files as $file) {
            $class = $class == 'odd' ? 'even' : 'odd';
            $html .= '<li style="white-space:nowrap;" class="' . $class . '">'
                . '<form style="display: inline;" method="post" action="'
                . $action . '" class="filebrowser_delete_file" data-path="'
                . XH_hsc($this->currentDirectory . $file) . '">'
                . '<button class="xhButtonImg" title="'
                . $this->translate('delete_file') . '">'
                .'<span class="fa fa-remove fa-lg fa-fw"></span></button>'
                . '<input type="hidden" name="deleteFile">'
                . '<input type="hidden" name="filebrowser_file" value="'
                . $file . '">'
                . $_XH_csrfProtection->tokenInput()
                . '</form>'
                . '<form method="post" style="display:inline;" action="'
                . $action . '" class="filebrowser_rename_file" data-path="'
                . XH_hsc($file) . '">'
                . '<input type="hidden" name="renameFile" value="'
                . $file . '">'
                . '<input type="hidden" name="oldName" value="' . $file . '">'
                . '<button class="xhButtonImg" title="'
                . $this->translate('rename_file') . '">'
                .'<span class="fa fa-tag fa-lg fa-fw"></span></button>'
                . $_XH_csrfProtection->tokenInput()
                . '</form>'
                . '<a style="position:relative" class="xhfbfile" href="'
                . $this->basePath . $this->currentDirectory . $file
                . '" target="_blank">' . $file;

            $ffn = $base . $fb->currentDirectory . $file;
            $usage = array_key_exists($ffn, $imgs)
                ? '<strong>' . $this->translate('image_usedin') . '</strong>'
                    . '<br>' . implode('<br>', $imgs[$ffn])
                : '';

            $path = $this->basePath . $this->currentDirectory . $file;
            if ($this->isImageFile($path) && ($image = getimagesize($path))) {
                $html .= $this->renderImage($path, $file, $image, $usage);
            }
            $html .= '</a> (' .  $this->renderFileSize($path) . ')</li>';
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
    private function fileListForEditor(array $files)
    {
        if (empty($files)) {
            return '';
        }
        $html = '<ul>';
        $dir = $this->basePath . $this->currentDirectory;
        $is_image = (int) (strpos($this->linkParams, 'type=images') === 0);
        $class = 'even';
        $prefix = $this->linkPrefix;
        if ($prefix != '?&amp;download=') {
            $prefix .= str_replace(array('../', './'), '', $this->currentDirectory);
        }
        foreach ($files as $file) {
            $class = $class == 'odd' ? 'even' : 'odd';

            $html .= '<li class="' . $class . '">';
            $html .= '<span class="xhfbfile" data-file="' . $prefix . $file
                . '" data-is-image="' . $is_image . '">' . $file;

            $path = $dir . $file;
            if (strpos($this->linkParams, 'type=images') !== false
                && $this->isImageFile($path) && ($image = getimagesize($path))
            ) {
                $html .= $this->renderImage($path, $file, $image);
            }
            $html .= '</span> (' . $this->renderFileSize($path) . ')</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Renders an image.
     *
     * @param string $path  An image path.
     * @param string $file  An image filename.
     * @param array  $image An array of image information from getimagesize.
     * @param string $usage A usage information string.
     *
     * @return string HTML
     */
    private function renderImage($path, $file, array $image, $usage = null)
    {
        list($width, $height) = $image;
        if ($width > 150) {
            $ratio = $width / $height;
            $width = 150;
            $height = $width / $ratio;
        }
        return '<span style="position: relative;  z-index: 4; ">'
            . '<span style="font-weight: normal; border: none;">'
            . $image[0] . ' x ' . $image[1] . ' px</span>' . '<br>'
            . '<img src="' . $path . '" width="' . $width . '" height="'
            . $height . '" alt="' . $file . '">'
            . (isset($usage) ? '<br>' . $usage : '')
            . '</span>';
    }

    /**
     * Renders a file size in KB.
     *
     * @param string $path A path name.
     *
     * @return string HTML
     */
    private function renderFileSize($path)
    {
        return round(filesize($path) / 1024, 1) . ' kb';
    }

    /**
     * Returns an instantiated template.
     *
     * @param string $template A template file name.
     *
     * @return string HTML
     */
    public function loadTemplate($template)
    {
        if (file_exists($template)) {
            ob_start();
            include $template;
        }
        $html = ob_get_clean();
        $this->partials['folders'] = $this->folderList($this->folders);
        $this->partials['subfolders'] = $this->subfolderList($this->subfolders);
        if (basename($template) == 'cmsbrowser.html') {
            $this->partials['files'] = $this->fileList($this->files);
        } elseif (basename($template) == 'editorbrowser.html') {
            $this->partials['files'] = $this->fileListForEditor($this->files);
        }
        $this->partials['message'] = $this->message;
        foreach ($this->partials as $placeholder => $value) {
            $html = str_replace('%' . strtoupper($placeholder) . '%', $value, $html);
        }
        $this->message = '';
        return $html;
    }

    /**
     * Appends a localized error message to the message area of the view.
     *
     * @param string $message A message key.
     * @param ?array $args    Arguments.
     *
     * @return void
     */
    public function error($message = '', array $args = null)
    {
        $this->message .= '<p class="xh_fail">'
            . $this->translate($message, $args) . '</p>';
    }

    /**
     * Appends a localized success message to the message area of the view.
     *
     * @param string $message A message key.
     * @param ?array $args    The arguments.
     *
     * @return void
     */
    public function success($message, array $args = null)
    {
        $this->message .= '<p class="xh_success">'
            . $this->translate($message, $args) . '</p>';
    }

    /**
     * Appends a localized info message to the message area of the view.
     *
     * @param string $message A message key.
     * @param ?array $args    The arguments.
     *
     * @return void
     */
    public function info($message, array $args = null)
    {
        $this->message .= '<p class="xh_info">'
            . $this->translate($message, $args) . '</p>';
    }

    /**
     * Appends a message to the message area of the view.
     *
     * @param string $message A message.
     *
     * @return void
     */
    public function message($message)
    {
        $this->message .= '<p style="width: auto;">' . $message . '</p>';
    }

    /**
     * Returns a localized message.
     *
     * @param string $string A message key.
     * @param ?array $args   A single argument or an array of arguments.
     *
     * @return string
     */
    public function translate($string = '', array $args = null)
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
        return vsprintf($html, isset($args) ? $args : array());
    }

    /**
     * Renders the JavaScript configuration script element.
     *
     * @return string HTML
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function renderJsConfig()
    {
        global $plugin_tx;

        $ptx = $plugin_tx['filebrowser'];
        $config = array(
            'confirm_delete' => $ptx['confirm_delete'],
            'confirm_upload' => $ptx['confirm_upload'],
            'prompt_rename' => $ptx['prompt_rename']
        );
        return '<script type="text/javascript">var FILEBROWSER = '
            . json_encode($config) . ';</script>';
    }
}
