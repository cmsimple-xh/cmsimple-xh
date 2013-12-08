<?php

/**
 * The file browser controller.
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
 * The file browser controller class.
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
class Filebrowser_Controller
{
    /**
     * @var string $linkPrefix
     */
    var $linkPrefix = '';

    /**
     * @var string $browseBase
     */
    var $browseBase = '';

    /**
     * @var string $baseDirectory
     */
    var $baseDirectory;

    /**
     * @var string $currentDirectory
     */
    var $currentDirectory;

    /**
     * @var string $linkType
     */
    var $linkType;

    /**
     * @var array $folders
     */
    var $folders = array();

    /**
     * @var array $files
     */
    var $files = array();

    /**
     * @var array baseDirectories
     */
    var $baseDirectories = array();

    /**
     * @var array $allowedExtensions
     */
    var $allowedExtensions = array();

    /**
     * @var array $maxFilesizes
     */
    var $maxFilesizes = array();

    /**
     * @var object $view
     */
    var $view;

    /**
     * @var string $message
     */
    var $message = '';

    /**
     * @var string $browserPath
     */
    var $browserPath = '';

    /**
     * Constructs an instance.
     *
     * @global array The paths of system files and folders.
     * @global array The configuration of the plugins.
     */
    function Filebrowser_Controller()
    {
        global $pth, $plugin_cf;

        $this->browserPath = $pth['folder']['plugins']
            . basename(dirname(dirname(__FILE__))) . '/';
        $this->view = new Filebrowser_View();
        foreach (array('images', 'downloads', 'userfiles', 'media') as $type) {
            $this->baseDirectories[$type] = ltrim($pth['folder'][$type], './');
            $this->allowedExtensions[$type] = array();
            $temp = explode(',', $plugin_cf['filebrowser']['extensions_' . $type]);
            foreach ($temp as $ext) {
                $extension = trim($ext, ' ./');
                if ((bool) $extension) {
                    $this->allowedExtensions[$type][] = strtolower($extension);
                }
            }
        }
    }

    /**
     * Returns an array of A elements linking to the pages
     * where <var>$file</var> is used.
     *
     * @param string $file A file name.
     *
     * @global array The headings of the pages.
     * @global array The content of the pages.
     * @global array The URLs of the pages.
     *
     * @return array
     */
    function fileIsLinked($file)
    {
        global $h, $c, $u;

        $i = 0;
        $usages = array();
        // TODO: improve regex for better performance
        $regex = '#<.*(?:src|href|download)=(["\']).*' . preg_quote($file, '#')
            . '\\1.*>#is';

        foreach ($c as $page) {
            if (preg_match($regex, $page) > 0) {
                $usages[] = '<a href="?' . $u[$i] . '">' . $h[$i] . '</a>';
            }
            $i++;
        }
        $usages = array_unique($usages);
        if (count($usages) > 0) {
            return $usages;
        }
        return false;
    }

    /**
     * Returns an associative array mapping from file names to page headings,
     * where the images are used.
     *
     * @return array
     *
     * @global array The content of the pages.
     * @global array The headings of the pages.
     * @global int   The number of pages.
     */
    function usedImages()
    {
        global $c, $h, $cl;

        $images = array();
        for ($i = 0; $i < $cl; $i++) {
            preg_match_all('/<img.*?src=(["\'])(.*?)\\1.*?>/is', $c[$i], $m);
            foreach ($m[2] as $fn) {
                if ($fn{0} == '.' && $fn{1} == '/') {
                    $fn = substr($fn, 2);
                }
                if (array_key_exists($fn, $images)) {
                    if (!in_array($h[$i], $images[$fn])) {
                        $images[$fn][] = $h[$i];
                    }
                } else {
                    $images[$fn] = array($h[$i]);
                }
            }
        }
        return $images;
    }

    /**
     * Reads the current directory and fills <var>$this->folders</var> and
     * <var>$this->files</var> with the results.
     *
     * @return void
     */
    function readDirectory()
    {
        $dir = $this->browseBase . $this->currentDirectory;
        $this->files = array();

        $handle = opendir($dir);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if (strpos($file, '.') === 0) {
                    continue;
                }
                if (is_dir($dir . $file)) {
                    $this->folders[] = $this->currentDirectory . $file;
                    continue;
                }
                if ($this->isAllowedFile($file)) {
                    $this->files[] = $file;
                }
            }
            closedir($handle);
            natcasesort($this->folders);
            natcasesort($this->files);
        }
    }

    /**
     * Returns an array of subfolders of <var>$directory</var>.
     * Recurses down the subfolders.
     *
     * @param string $directory A directory.
     *
     * @return array
     */
    function getFolders($directory)
    {
        $folders = array();
        $handle = opendir($directory);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if (strpos($file, '.') === 0) {
                    continue;
                }
                if (is_dir($directory . $file)) {
                    $folders[] = str_replace(
                        $this->browseBase, '', $directory . $file
                    );
                    $subfolders = $this->getFolders($directory . $file . '/');
                    foreach ($subfolders as $subfolder) {
                        $folders[] = $subfolder;
                    }
                }
            }
            closedir($handle);
            natcasesort($folders);
        }
        return $folders;
    }

    /**
     * Whether <var>$file</var> is allowed to be handled by the file browser.
     *
     * @param string $file A file name.
     *
     * @return bool
     */
    function isAllowedFile($file)
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($extension == $file) {
            return false;
        }
        if (!in_array($extension, $this->allowedExtensions[$this->linkType])
            && !in_array('*', $this->allowedExtensions[$this->linkType])
        ) {
            return false;
        }
        return true;
    }

    /**
     * Returns an array of folders.
     *
     * @param bool $all ???
     *
     * @return array
     *
     * @todo Document the details.
     */
    function foldersArray($all = true)
    {
        $folders = array();

        $temp = $this->getFolders($this->browseBase . $this->baseDirectory);
        $baseDepth = count(explode('/', $this->baseDirectory)) - 2;
        foreach ($temp as $i => $folder) {
            $ar = explode('/', $folder);
            $level = count($ar);
            $parent = '';
            for ($i = 0; $i < $level - 1; $i++) {
                $parent .= '/' . $ar[$i];
            }
            $parent = substr($parent, 1);
            $folders[$folder]['level'] = count($ar) - $baseDepth;
            $folders[$folder]['parent'] = $parent;
            $folders[$folder]['children'] = array();
            $linkList = '';
        }
        foreach ($folders as $folder => $data) {
            $folders[$folder]['children']
                = $this->gatherChildren($folder, $folders);
        }

        $this->view->currentDirectory = $this->currentDirectory;
        foreach ($folders as $folder => $data) {
            $folders[$folder]['linkList']
                = $this->view->folderLink($folder, $folders);
        }
        return $folders;
    }

    /**
     * Returns an array.
     *
     * @param string $parent  ???
     * @param array  $folders ???
     *
     * @return array
     *
     * @todo Document the details.
     */
    function gatherChildren($parent, $folders)
    {
        $children = array();
        foreach ($folders as $key => $folder) {
            if ($folder['parent'] == $parent) {
                $children[] = $key;
            }
        }
        return $children;
    }

    /**
     * Deletes a file.
     *
     * @param string $file A file name.
     *
     * @return void
     */
    function deleteFile($file)
    {
        $file = $this->browseBase . $this->currentDirectory . basename($file);
        $pages = $this->fileIsLinked($file);
        if (is_array($pages)) {
            $this->view->error('error_not_deleted', $file);
            $this->view->message .= '<div class="xh_info">'
                . $this->view->translate('error_file_is_used', $file)
                . '<ul>';
            foreach ($pages as $page) {
                $this->view->message .= '<li>' . $page . '</li>';
            }
            $this->view->message .= '</ul></div>';
            return;
        }
        if (unlink($file)) {
            $this->view->success('success_deleted', $file);
        } else {
            $this->view->error('error_not_deleted', $file);
        }
    }

    /**
     * Returns a new unique filename.
     *
     * @param string $filename A filename.
     *
     * @return string
     *
     * @since 1.6
     */
    function newFilename($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $base = substr($filename, 0, -(strlen($ext) + 1));
        $i = 1;
        while (true) {
            $res = $base . '_' . $i . '.' . $ext;
            if (!file_exists($res)) {
                break;
            }
            $i++;
        }
        return $res;
    }

    /**
     * Handles a file upload.
     *
     * @return void
     */
    function uploadFile()
    {
        $file = $_FILES['fbupload'];

        if ($file['error'] != 0) {
            switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $this->view->error('error_not_uploaded', $file['name']);
                $this->view->info(
                    'error_file_too_big_php',
                    array(ini_get('upload_max_filesize'), 'upload_max_filesize')
                );
                return;
            default:
                $this->view->error('error_not_uploaded', $file['name']);
                return;
            }
        }

        // alternatively the following might be used:
        // $type = $this->linkType == 'images' ? 'images' : 'downloads';
        $type = getimagesize($file['tmp_name']) !== false
            ? 'images' : 'downloads';
        if (isset($this->maxFilesizes[$type])) {
            if ($file['size'] > $this->maxFilesizes[$type]) {
                $this->view->error('error_not_uploaded', $file['name']);
                $this->view->info(
                    'error_file_too_big',
                    array(
                        number_format($file['size']/1000, 2),
                        number_format($this->maxFilesizes[$type]/1000, 2) . ' kb'
                    )
                );
                return;
            }
        }

        if ($this->isAllowedFile($file['name']) == false) {
            $this->view->error('error_not_uploaded', $file['name']);
            $this->view->info(
                'error_no_proper_extension',
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );
            return;
        }

        $filename = $this->browseBase . $this->currentDirectory
            . basename($file['name']);
        if (file_exists($filename)) {
            $newFilename = $this->newFilename($filename);
            if (rename($filename, $newFilename)) {
                $this->view->info(
                    'success_renamed',
                    array(basename($filename), basename($newFilename))
                );
            } else {
                $this->view->error('error_not_uploaded', $file['name']);
                $this->view->info('error_file_already_exists', $filename);
                return;
            }
        }

        if (move_uploaded_file($_FILES['fbupload']['tmp_name'], $filename)) {
            chmod($filename, 0644);
            $this->view->success('success_uploaded', $file['name']);
            return;
        }

        $this->view->error('error_not_uploaded', $file['name']);
    }

    /**
     * Handles creation of a folder.
     *
     * @return void
     */
    function createFolder()
    {
        $folder = basename($_POST['createFolder']);
        $folder = str_replace(
            array(':', '*', '?', '"', '<', '>', '|', ' '), '', $folder
        );
        $folder = $this->browseBase . $this->currentDirectory . $folder;
        if (is_dir($folder)) {
            $this->view->error('error_folder_already_exists', basename($folder));
            return;
        }
        if (!mkdir($folder)) {
            $this->view->error('error_unknown');
        }
        $this->view->success('success_folder_created', basename($folder));
    }

    /**
     * Handles deletion of a folder.
     *
     * @return void
     */
    function deleteFolder()
    {
        $folder = $this->browseBase . $this->currentDirectory
            . basename($_POST['folder']);
        if (!rmdir($folder)) {
            $this->view->error('error_not_deleted', basename($folder));
            return;
        }
        $this->view->success('success_deleted', basename($folder));
    }

    /**
     * Handles renaming of a file
     *
     * @return void
     *
     * @todo Add i18n for errors.
     */
    function renameFile()
    {
        $newName = str_replace(
            array('..', '<', '>', ':', '?', ' '), '', basename($_POST['renameFile'])
        );
        $oldName = $_POST['oldName'];
        if ($oldName == $newName) {
            return;
        }
        $newExtension = pathinfo($newName, PATHINFO_EXTENSION);
        $oldExtension = pathinfo($oldName, PATHINFO_EXTENSION);
        if ($newExtension !== $oldExtension) {
            $this->view->error('error_cant_change_extension');
            return;
        }
        $newPath = $this->browseBase . $this->currentDirectory . '/' . $newName;
        $oldPath = $this->browseBase . $this->currentDirectory . '/' . $oldName;
        if (file_exists($newPath)) {
            $this->view->error('error_file_already_exists', $newName);
            return;
        }
        $pages = $this->fileIsLinked($oldName);
        if (is_array($pages)) {
            $this->view->error('error_cant_rename', $oldName);
            $this->view->message .= '<div class="xh_info">'
                . $this->view->translate('error_file_is_used', $oldName)
                . '<ul>';
            foreach ($pages as $page) {
                $this->view->message .= '<li>' . $page . '</li>';
            }
            $this->view->message .= '</ul></div>';
            return;
        }
        if (rename($oldPath, $newPath)) {
            $this->view->success('success_renamed', array($oldName, $newName));
            return;
        }
        $this->view->error('error_cant_rename', $oldName);
        return;
    }

    /**
     * Returns the instantiated <var>$template</var>.
     *
     * @param string $template A template file name.
     *
     * @return string
     *
     * @todo i18n of error message (or probably make it an error)
     */
    function render($template)
    {
        $template = str_replace(array('.', '/', '\\', '<', ' '), '', $template);
        if (!file_exists($this->browserPath . 'tpl/' . $template . '.html')) {
            return '<p>Filebrowser_Controller::render() - Template not found: '
                . "{$this->browserPath}tpl/$template.html'</p>";
        }
        $this->view->baseDirectory = $this->baseDirectory;
        $this->view->baseLink = $this->linkType;
        $this->view->folders = $this->foldersArray();
        $this->view->subfolders = $this->folders;
        $this->view->files = $this->files;

        return $this->view->loadTemplate(
            $this->browserPath . 'tpl/' . $template . '.html'
        );
    }

    /**
     * Sets <var>$this->linkParams</var>.
     *
     * @param string $paramsString ???
     *
     * @return void
     */
    function setLinkParams($paramsString)
    {
        $this->view->linkParams = $paramsString;
    }

    /**
     * Sets <var>$this->linkPrefix</var>.
     *
     * @param string $prefix ???
     *
     * @return void
     */
    function setLinkPrefix($prefix)
    {
        $this->view->linkPrefix = $prefix;
    }

    /**
     * Sets the browse path.
     *
     * @param string $path The new browse path.
     *
     * @return void
     */
    function setBrowseBase($path)
    {

        $this->browseBase = $path;
        $this->view->basePath = $path;
    }

    /**
     * Sets the browser path.
     *
     * @param string $path The new browser path.
     *
     * @return void
     */
    function setBrowserPath($path)
    {
        $this->view->browserPath = $path;
    }

    /**
     * Sets the maximum file size.
     *
     * @param string $folder ???
     * @param int    $bytes  The maximum file size in bytes.
     *
     * @return void
     */
    function setMaxFileSize($folder, $bytes)
    {
        if (key_exists($folder, $this->baseDirectories)) {
            $this->maxFilesizes[$folder] = (int) $bytes;
        }
    }
}

?>
