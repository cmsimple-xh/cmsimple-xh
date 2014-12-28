<?php

/**
 * The file browser view class.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Filebrowser
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * The file browser view class.
 *
 * @category CMSimple_XH
 * @package  Filebrowser
 * @author   Martin Damken <kontakt@zeichenkombinat.de>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class Filebrowser_View
{
    /**
     * (X)HTML fragments to insert in the templates.
     *
     * @var array
     *
     * @access public
     */
    var $partials = array();

    /**
     * Relative path of the filebrowser folder.
     *
     * @var string
     *
     * @access public
     */
    var $browserPath = '';

    /**
     * Relative path of the CMSimple installation folder.
     *
     * @var string
     *
     * @access public
     */
    var $basePath;

    /**
     * Relative path of the userfiles folder.
     *
     * @var string
     *
     * @access public
     */
    var $baseDirectory;

    /**
     * The type of the files to browse ("images", "downloads", "media" or
     * "userfiles").
     *
     * @var string
     *
     * @access public
     */
    var $baseLink;

    /**
     * The path of the current folder relative to the type's main folder.
     *
     * @var string
     *
     * @access public
     */
    var $currentDirectory;

    /**
     * The (partial) query string.
     *
     * @var string
     *
     * @access public
     */
    var $linkParams;

    /**
     * ???
     *
     * @var string
     *
     * @access public
     *
     * @todo Document description.
     */
    var $linkPrefix;

    /**
     * An array of folders?
     *
     * @var array
     *
     * @access public
     */
    var $folders;

    /**
     * An array of subfolders?
     *
     * @var array
     *
     * @access public
     */
    var $subfolders;

    /**
     * An array of files?
     *
     * @var array
     *
     * @access public
     */
    var $files;

    /**
     * The (X)HTML content of the message area.
     *
     * @var string
     *
     * @access protected
     */
    var $message = '';

    /**
     * The localization of the file browser.
     *
     * @var array
     *
     * @access protected
     */
    var $lang = array();

    /**
     * The CSRF token.
     *
     * @var string
     *
     * @access protected
     */
    var $csrfToken;

    /**
     * Initializes a newly created instance.
     *
     * @return void
     *
     * @global array  The localization of the plugins.
     *
     * @access public
     */
    function Filebrowser_View()
    {
        global $plugin_tx;

        $this->lang = $plugin_tx['filebrowser'];
    }

    /**
     * Returns the folder list view.
     *
     * @param array $folders An array of folders.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the core.
     *
     * @access protected
     */
    function folderList($folders)
    {
        global $tx;

        $title = isset($tx['title']['userfiles'])
            ? utf8_ucfirst($tx['title']['userfiles'])
            : ucfirst('Userfiles ' . $this->translate('folder'));
        $html = '<ul><li class="openFolder"><a href="?'
            . htmlspecialchars($this->linkParams, ENT_QUOTES, 'UTF-8') . '">'
            . $title . ' ' . $this->lang['folder'] . '</a>';
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
     *
     * @access public
     *
     * @todo What is this method for?
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
     * Returns the subfolder list view of the CMS browser.
     *
     * @param array $folders An array of folders.
     *
     * @return string
     *
     * @global object The CRSF protection object.
     * @global string The script name.
     *
     * @access protected
     */
    function subfolderList($folders)
    {
        global $_XH_csrfProtection, $sn;

        $html = '';
        if (is_array($folders) && count($folders) > 0) {
            $action = $sn . '?' . XH_hsc($_SERVER['QUERY_STRING']);
            $html = '<ul>';
            foreach ($folders as $folder) {
                $name = str_replace($this->currentDirectory, '', $folder);
                $html .= '<li class="folder">'
                    . '<form style="display: inline;" method="post" action="'
                    . $action . '"'
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
     * Returns the subfolder list view of the editor browser.
     *
     * @param array $folders An array of folders.
     *
     * @return string
     *
     * @global string The script name.
     *
     * @access protected
     */
    function subfolderListForEditor($folders)
    {
        global $sn;

        $html = '';
        if (is_array($folders) && count($folders) > 0) {
            $action = $sn . '?'
                . htmlspecialchars(
                    $_SERVER['QUERY_STRING'], ENT_COMPAT, 'UTF-8'
                );
            $html = '<ul>';
            foreach ($folders as $folder) {
                $name = str_replace($this->currentDirectory, '', $folder);
                $html .= '<li class="folder">'
                    . '<form style="display: inline;" method="post" action="'
                    . $action . '"'
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
     *
     * @access protected
     */
    function isImageFile($filename)
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
     *
     * @global string The script name.
     * @global array  The localization of the core.
     * @global object The CRSF protection object.
     *
     * @access protected
     */
    function fileList($files)
    {
        global $sn, $tx, $_XH_csrfProtection;

        if (empty($files)) {
            return '';
        }
        $html = '<ul>';
        $class = 'even';
        $fb = $_SESSION['xh_browser']; // FIXME: the view shouldn't know the model
        $imgs = $fb->usedImages();
        $base = $fb->browseBase;
        if ($base{0} == '.' && $base{1} == '/') {
            $base = substr($base, 2);
        }
        $action = $sn . '?' . XH_hsc($_SERVER['QUERY_STRING']);
        foreach ($files as $file) {
            $class = $class == 'odd' ? 'even' : 'odd';
            $html .= '<li style="white-space:nowrap;" class="' . $class . '">'
                . '<form style="display: inline;" method="post" action="'
                . $action . '"'
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
                . '<form method="post" style="display:inline;" action="'
                . $action . '"'
                . ' onsubmit="return FILEBROWSER.promptNewName(this, \''
                . $this->translate('prompt_rename', $file) . '\');"'
                . '>'
                . tag(
                    'input type="hidden" name="renameFile" value="'
                    . $file . '"'
                )
                . tag('input type="hidden" name="oldName" value="' . $file . '"')
                . tag(
                    'input type="image" src="' . $this->browserPath
                    . 'css/icons/rename.png"' . ' alt="'
                    . $this->translate('rename_file') . '" title="'
                    . $this->translate('rename_file')
                    . '" style="width: 16px; height: 16px"'
                )
                . $_XH_csrfProtection->tokenInput()
                . '</form>'
                . '<a style="position:relative" class="xhfbfile" href="'
                . $this->basePath . $this->currentDirectory . $file . '" target="_blank">' . $file;

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
                    . $image[0] . ' x ' . $image[1] . ' px</span>' . tag('br')
                    . tag(
                        'img src="' . $path . '" width="' . $width . '" height="'
                        . $height . '" alt="' . $file . '"'
                    ) . tag('br') . $usage . '</span>';
            }
            $size = round(filesize($path) / 1024, 1);
            $html .= '</a> (' . $size . ' kb)</li>';
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
     *
     * @access protected
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
<span style="font-weight: normal; border: none;">$image[0] x $image[1] px</span>
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
     * Returns a CSRF token and stores it in the session.
     *
     * @return string
     *
     * @access protected
     */
    function getCSRFToken()
    {
        if (!isset($this->token)) {
            $this->token = md5(uniqid(rand()));
            $_SESSION['filebrowser_csrf_token'] = $this->token;
        }
        return $this->token;
    }

    /**
     * Checks the submitted CSRF token against the one stored in the session.
     * Exits the script with 403, if that failed.
     *
     * @return void
     *
     * @access public
     */
    function checkCSRFToken()
    {
        $key = 'filebrowser_csrf_token';
        $submittedToken = isset($_POST[$key]) ? $_POST[$key] : '';
        $ok = isset($_SESSION[$key]) && $_SESSION[$key] === $_POST[$key];
        if (!$ok) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Invalid CSRF token!';
            // the following should be exit/die, but that would break unit tests
            trigger_error('Invalid CSRF token!', E_USER_ERROR);
        }
    }

    /**
     * Returns an instantiated template.
     *
     * @param string $template A template file name.
     *
     * @return string (X)HTML.
     *
     * @access public
     */
    function loadTemplate($template)
    {
        if (file_exists($template)) {
            ob_start();
            include $template;
        }
        $html = ob_get_clean();
        $this->partials['folders'] = $this->folderList($this->folders);
        if (basename($template) == 'cmsbrowser.html') {
            $this->partials['subfolders']
                = $this->subfolderList($this->subfolders);
            $this->partials['files'] = $this->fileList($this->files);
        } elseif (basename($template) == 'editorbrowser.html') {
            $this->partials['subfolders']
                = $this->subfolderListForEditor($this->subfolders);
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
     * @param array  $args    Arguments.
     *
     * @return void
     *
     * @access public
     */
    function error($message ='', $args = null)
    {
        $this->message .= '<p class="xh_fail">'
            . $this->translate($message, $args) . '</p>';
    }

    /**
     * Appends a localized success message to the message area of the view.
     *
     * @param string $message A message key.
     * @param array  $args    The arguments.
     *
     * @return void
     *
     * @access public
     */
    function success($message, $args = null)
    {
        $this->message .= '<p class="xh_success">'
            . $this->translate($message, $args) . '</p>';
    }

    /**
     * Appends a localized info message to the message area of the view.
     *
     * @param string $message A message key.
     * @param array  $args    The arguments.
     *
     * @return void
     *
     * @access public
     */
    function info($message, $args = null)
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
     *
     * @access public
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
     *
     * @access public
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
