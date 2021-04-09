<?php

/**
 * The image uploader.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Tinymce5
 * @author    manu <info@pixolution.ch>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: Controller.php 1553 2015-04-21 20:30:40Z cmb69 $
 * @link      http://cmsimple-xh.org/
 */

namespace Tinymce5;

/**
 * The image uploader controller class.
 *
 * @category CMSimple_XH
 * @package  tinymce4
 */
class Uploader
{
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
    protected $currentType;

    /**
     * The link type.
     *
     * @var string $linkType
     */
    public $linkType;

    /**
     * The base directories.
     *
     * @var array baseDirectories
     */
    public $baseDirectories = array();
    
    /**
     * The file written after succussfull upload.
     *
     * @var string fileWritten
     */
    public $fileWritten;
    
    /**
     * The error message array for a failed upload.
     *
     * @var array errMsg['errMsg key','details']
     */
    public $errMsg = array();

    /**
     * The allowed extensions.
     *
     * @var array $allowedExtensions
     */
    protected $allowedExtensions = array();

    /**
     * The maximum filesizes.
     *
     * @var array $maxFilesizes
     */
    protected $maxFilesizes = array();

    /**
     * Constructs an instance.
     *
     * @global array The paths of system files and folders.
     * @global array The configuration of the plugins.
     */
    public function __construct()
    {
        global $pth, $plugin_cf;

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
     * Whether <var>$file</var> is allowed to be handled by the file browser.
     *
     * @param string $file A file name.
     *
     * @return bool
     */
    protected function isAllowedFile($file)
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
     * Returns a new unique filename.
     *
     * @param string $filename A filename.
     *
     * @return string
     *
     * @since 1.6
     */
    protected function newFilename($filename)
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
    public function uploadFile($file = '')
    {   //EM~
        if ($file == '') $file = $_FILES['fbupload'];
        switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
            $this->errMsg['error_file_too_big_php'] = ini_get('upload_max_filesize') . ' upload_max_filesize';
            return;
        case UPLOAD_ERR_NO_TMP_DIR:
            $this->errMsg['error_missing_temp_folder'] = '';
            return;
        default:
            $this->errMsg['error_unknown'] = $file['error'] . ' ' .  $file['name'];
            return;
        }

        // alternatively the following might be used:
        // $type = $this->linkType == 'images' ? 'images' : 'downloads';
        $type = getimagesize($file['tmp_name']) !== false
            ? 'images' : 'downloads';
        if (isset($this->maxFilesizes[$type])) {
            if ($file['size'] > $this->maxFilesizes[$type]) {
                $this->errMsg['error_file_too_big'] = $file['name'];
                return;
            }
        }

        if ($this->isAllowedFile($file['name']) == false) {
                $this->errMsg['error_no_proper_extension'] = $file['name'];
            return;
        }

        $filename = $this->browseBase . $this->currentDirectory
            . basename($file['name']);
        if (file_exists($filename)) {
            $filename = $this->newFilename($filename);  //EM~
/* EM-            if (!rename($filename, $newFilename)) {
                $this->errMsg['error_file_already_exists'] = $filename;
                return;
            }*/
        }

        if (move_uploaded_file($file['tmp_name'], $filename)) {
            chmod($filename, 0644);
            $this->fileWritten = $filename;
            return true;
        } else {
            $this->errMsg['error_not_uploaded'] = $filename;
        }
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

