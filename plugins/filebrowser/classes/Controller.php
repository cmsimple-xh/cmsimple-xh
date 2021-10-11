<?php

/**
 * The file browser controller.
 *
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace Filebrowser;

/**
 * The file browser controller class.
 *
 * @author   Martin Damken <kontakt@zeichenkombinat.de>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 *
 * @todo Document meaning of properties.
 */
class Controller
{
    /**
     * The link prefix.
     *
     * @var string $linkPrefix
     */
    private $linkPrefix = '';

    /**
     * The browse base.
     *
     * @var string $browseBase
     */
    public $browseBase = '';

    /**
     * The base directory.
     *
     * @var string $baseDirectory
     */
    public $baseDirectory;

    /**
     * The current directory.
     *
     * @var string $currentDirectory
     */
    public $currentDirectory;

    /**
     *  The current type of allowed file extensions.
     *
     *  @var string
     */
    private $currentType;

    /**
     * The link type.
     *
     * @var string $linkType
     */
    public $linkType;

    /**
     * The folders.
     *
     * @var array $folders
     */
    private $folders = array();

    /**
     * The files.
     *
     * @var array $files
     */
    private $files = array();

    /**
     * The base directories.
     *
     * @var array baseDirectories
     */
    public $baseDirectories = array();

    /**
     * The allowed extensions.
     *
     * @var array $allowedExtensions
     */
    private $allowedExtensions = array();

    /**
     * The maximum filesizes.
     *
     * @var array $maxFilesizes
     */
    private $maxFilesizes = array();

    /**
     * The view.
     *
     * @var View $view
     */
    public $view;

    /**
     * The message.
     *
     * @var string $message
     */
    private $message = '';

    /**
     * The brower path.
     *
     * @var string $browserPath
     */
    public $browserPath = '';

    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        global $pth, $plugin_cf;

        $this->browserPath = $pth['folder']['plugins']
            . basename(dirname(dirname(__FILE__))) . '/';
        $this->view = new View();
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
     * Determines the current type of the allowed file extensions.
     *
     * @return void
     */
    public function determineCurrentType()
    {
        $this->currentType = $this->linkType;
        if (count(array_unique($this->baseDirectories)) != 4) {
            // If any of the directories are identical, we can't reliably detect
            // the current type, so we bail out.
            return;
        }
        $types = array('images', 'downloads', 'media', 'userfiles');
        foreach ($types as $type) {
            $pos = strpos($this->currentDirectory, $this->baseDirectories[$type]);
            if ($pos === 0) {
                $this->currentType = $type;
                break;
            }
        }
    }

    /**
     * Returns an array of A elements linking to the pages
     * where <var>$file</var> is used.
     *
     * @param string $file A file name.
     *
     * @return array|false
     */
    private function fileIsLinked($file)
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
     */
    public function usedImages()
    {
        global $c, $h, $cl;

        $images = array();
        for ($i = 0; $i < $cl; $i++) {
            preg_match_all('/<img.*?src=(["\'])(.*?)\\1.*?>/is', $c[$i], $m);
            foreach ($m[2] as $fn) {
                if ($fn[0] == '.' && $fn[1] == '/') {
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
    public function readDirectory()
    {
        $dir = $this->browseBase . $this->currentDirectory;
        $this->files = array();

        if (is_dir($dir) && ($handle = opendir($dir))) {
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
            sort($this->folders, SORT_NATURAL | SORT_FLAG_CASE);
            sort($this->files, SORT_NATURAL | SORT_FLAG_CASE);
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
    private function getFolders($directory)
    {
        $folders = array();
        if (is_dir($directory) && ($handle = opendir($directory))) {
            while (false !== ($file = readdir($handle))) {
                if (strpos($file, '.') === 0) {
                    continue;
                }
                if (is_dir($directory . $file)) {
                    $folders[] = str_replace($this->browseBase, '', $directory . $file);
                    $subfolders = $this->getFolders($directory . $file . '/');
                    foreach ($subfolders as $subfolder) {
                        $folders[] = $subfolder;
                    }
                }
            }
            closedir($handle);
            sort($folders, SORT_NATURAL | SORT_FLAG_CASE);
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
    private function isAllowedFile($file)
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($extension == $file) {
            return false;
        }
        if (!in_array($extension, $this->allowedExtensions[$this->currentType])
            && !in_array('*', $this->allowedExtensions[$this->currentType])
        ) {
            return false;
        }
        return true;
    }

    /**
     * Returns an array of folders.
     *
     * @return array
     *
     * @todo Document the details.
     */
    private function foldersArray()
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
    private function gatherChildren($parent, array $folders)
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
    public function deleteFile($file)
    {
        $file = $this->browseBase . $this->currentDirectory . basename($file);
        $pages = $this->fileIsLinked($file);
        if (is_array($pages)) {
            $this->view->error('error_not_deleted', array($file));
            $this->view->message .= '<div class="xh_info">'
                . $this->view->translate('error_file_is_used', array($file))
                . '<ul>';
            foreach ($pages as $page) {
                $this->view->message .= '<li>' . $page . '</li>';
            }
            $this->view->message .= '</ul></div>';
            return;
        }
        if (unlink($file)) {
            $this->view->success('success_deleted', array($file));
        } else {
            $this->view->error('error_not_deleted', array($file));
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
    private function newFilename($filename)
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
    public function uploadFile()
    {
        $file = $_FILES['fbupload'];

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
                $this->view->error(
                    'error_file_too_big_php',
                    array(ini_get('upload_max_filesize'), 'upload_max_filesize')
                );
                $this->view->info('error_not_uploaded', array($file['name']));
                return;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->view->error('error_missing_temp_folder');
                $this->view->info('error_not_uploaded', array($file['name']));
                return;
            default:
                $this->view->error('error_unknown', array((string) $file['error']));
                $this->view->info('error_not_uploaded', array($file['name']));
                return;
        }

        // alternatively the following might be used:
        // $type = $this->linkType == 'images' ? 'images' : 'downloads';
        $type = getimagesize($file['tmp_name']) !== false
            ? 'images' : 'downloads';
        if (isset($this->maxFilesizes[$type])) {
            if ($file['size'] > $this->maxFilesizes[$type]) {
                $this->view->error('error_not_uploaded', array($file['name']));
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
            $this->view->error('error_not_uploaded', array($file['name']));
            $this->view->info(
                'error_no_proper_extension',
                array(pathinfo($file['name'], PATHINFO_EXTENSION))
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
                $this->view->error('error_not_uploaded', array($file['name']));
                $this->view->info('error_file_already_exists', array($filename));
                return;
            }
        }

        if (move_uploaded_file($_FILES['fbupload']['tmp_name'], $filename)) {
            chmod($filename, 0644);
            $this->view->success('success_uploaded', array($file['name']));
            return;
        }

        $this->view->error('error_not_uploaded', array($file['name']));
    }

    /**
     * Handles creation of a folder.
     *
     * @return void
     */
    public function createFolder()
    {
        $folder = basename($_POST['createFolder']);
        $folder = str_replace(array(':', '*', '?', '"', '<', '>', '|', ' '), '', $folder);
        $folder = $this->browseBase . $this->currentDirectory . $folder;
        if (is_dir($folder)) {
            $this->view->error('error_folder_already_exists', array(basename($folder)));
            return;
        }
        if (!mkdir($folder)) {
            $this->view->error('error_cant_create_folder');
            return;
        }
        chmod($folder, 0777);
        $this->view->success('success_folder_created', array(basename($folder)));
    }

    /**
     * Handles deletion of a folder.
     *
     * @return void
     */
    public function deleteFolder()
    {
        $folder = $this->browseBase . $this->currentDirectory
            . basename($_POST['folder']);
        if (!$this->isEmpty($folder)) {
            $this->view->error('error_folder_not_empty');
            $this->view->info('error_not_deleted', array(basename($folder)));
            return;
        } else {
            if (!rmdir($folder)) {
                $this->view->error('error_not_deleted', array(basename($folder)));
                return;
            }
        }
        $this->view->success('success_deleted', array(basename($folder)));
    }

    /**
     * Returns whether a folder is empty.
     *
     * @param string $folder A folder name.
     *
     * @return bool
     */
    private function isEmpty($folder)
    {
        $isEmpty = true;
        if (is_dir($folder) && ($dir = opendir($folder))) {
            while (($entry = readdir($dir)) !== false) {
                if ($entry != '.' && $entry != '..') {
                    $isEmpty = false;
                    break;
                }
            }
        }
        return $isEmpty;
    }

    /**
     * Handles renaming of a file
     *
     * @return void
     *
     * @todo Add i18n for errors.
     */
    public function renameFile()
    {
        $newName = str_replace(array('..', '<', '>', ':', '?'), '', basename($_POST['renameFile']));
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
            $this->view->error('error_file_already_exists', array($newName));
            return;
        }
        $pages = $this->fileIsLinked($oldName);
        if (is_array($pages)) {
            $this->view->error('error_cant_rename', array($oldName));
            $this->view->message .= '<div class="xh_info">'
                . $this->view->translate('error_file_is_used', array($oldName))
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
        $this->view->error('error_cant_rename', array($oldName));
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
    public function render($template)
    {
        $template = str_replace(array('.', '/', '\\', '<', ' '), '', $template);
        if (!file_exists($this->browserPath . 'tpl/' . $template . '.html')) {
            return '<p>Filebrowser\\Controller::render() - Template not found: '
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
    public function setLinkParams($paramsString)
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
    public function setLinkPrefix($prefix)
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
    public function setBrowseBase($path)
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
    public function setBrowserPath($path)
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
    public function setMaxFileSize($folder, $bytes)
    {
        if (key_exists($folder, $this->baseDirectories)) {
            $this->maxFilesizes[$folder] = (int) $bytes;
        }
    }
}
