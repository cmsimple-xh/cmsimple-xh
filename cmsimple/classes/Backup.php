<?php

/**
 * Handling of the content backups.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * Handling of the content backups.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Peter Harteg <peter@harteg.dk>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.2
 */
class XH_Backup
{
    /**
     * The path of the content folder.
     *
     * @var string
     */
    private $_contentFolder;

    /**
     * The path of the content file.
     *
     * @var string
     */
    private $_contentFile;

    /**
     * The maximum number of backups to keep.
     *
     * @var int
     */
    private $_maxBackups;

    /**
     * Initializes a new instance.
     *
     * @global array The paths of system files and folders.
     * @global array The configuration of the core.
     */
    function __construct()
    {
        global $pth, $cf;

        $this->_contentFolder = $pth['folder']['content'];
        $this->_contentFile = $pth['file']['content'];
        $this->_maxBackups = (int) $cf['backup']['numberoffiles'];
    }

    /**
     * Executes the backup process.
     *
     * @return string (X)HTML.
     */
    function execute()
    {
        $o = '';
        $filename = date("Ymd_His") . '_content.htm';
        $needsBackup = $this->_needsBackup();
        if (!$needsBackup || $this->_backupFile($filename)) {
            if ($needsBackup) {
                $o .= $this->_renderCreationInfo($filename);
            }
            $deletions = $this->_deleteSurplusBackups();
            $o .= $this->_renderDeletionResults($deletions);
        } else {
            e('cntsave', 'backup', $filename);
        }
        return $o;
    }

    /**
     * Returns the filenames of all existing backups.
     *
     * @return array
     */
    function _findBackups()
    {
        $result = array();
        if ($dir = opendir($this->_contentFolder)) {
            while (($entry = readdir($dir)) !== false) {
                if (XH_isContentBackup($entry)) {
                    $result[] = $entry;
                }
            }
            closedir($dir);
        }
        sort($result);
        return $result;
    }

    /**
     * Returns whether a backup is needed.
     *
     * @return bool
     */
    function _needsBackup()
    {
        if ($this->_maxBackups <= 0) {
            return false;
        }
        $latestBackup = $this->_latestBackup();
        if ($latestBackup) {
            return md5_file($this->_contentFile) != md5_file($latestBackup);
        } else {
            return true;
        }
    }

    /**
     * Returns the path of the latest backup file.
     *
     * If there is no backup file, <var>false</var> is returned.
     *
     * @return string
     */
    function _latestBackup()
    {
        $backups = $this->_findBackups();
        if (!empty($backups)) {
            return $this->_contentFolder . $backups[count($backups) - 1];
        } else {
            return false;
        }
    }

    /**
     * Creates a backup of the content file.
     *
     * @param string $filename The name of the backup.
     *
     * @return bool
     */
    function _backupFile($filename)
    {
        return copy($this->_contentFile, $this->_contentFolder . $filename);
    }

    /**
     * Deletes surplus backups.
     *
     * @return array A map of filenames => deletion success.
     */
    function _deleteSurplusBackups()
    {
        $result = array();
        $filenames = $this->_findBackups();
        $filenames = array_slice($filenames, 0, -$this->_maxBackups);
        foreach ($filenames as $filename) {
            $filepath = $this->_contentFolder . $filename;
            $result[$filepath] = unlink($filepath);
        }
        return $result;
    }

    /**
     * Renders the backup creation info message.
     *
     * @param string A filename.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the core.
     */
    function _renderCreationInfo($filename)
    {
        global $tx;

        $message = sprintf(
            '%s %s %s',
            utf8_ucfirst($tx['filetype']['backup']),
            $filename, $tx['result']['created']
        );
        return XH_message('info', $message);
    }

    /**
     * Renders the deletion results.
     *
     * @param array A map of filenames => deletion success.
     *
     * @return string (X)HTML.
     */
    function _renderDeletionResults($deletions)
    {
        $results = '';
        foreach ($deletions as $filename => $deleted) {
            if ($deleted) {
                $results .= $this->_renderDeletionInfo($filename);
            } else {
                e('cntdelete', 'backup', $filename);
            }
        }
        return $results;
    }

    /**
     * Renders the deletion info message.
     *
     * @param string A filename.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the core.
     */
    function _renderDeletionInfo($filename)
    {
        global $tx;

        $message = sprintf(
            '%s %s %s',
            utf8_ucfirst($tx['filetype']['backup']),
            $filename, $tx['result']['deleted']
        );
        return XH_message('info', $message);
    }
}

?>
