<?php

/**
 * Handling of the content backups.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

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
class Backup
{
    /**
     * The paths of the content folders.
     *
     * @var array
     */
    private $contentFolders;

    /**
     * The path of the content folder.
     *
     * @var string
     */
    private $contentFolder;

    /**
     * The path of the content file.
     *
     * @var string
     */
    private $contentFile;

    /**
     * The maximum number of backups to keep.
     *
     * @var int
     */
    private $maxBackups;

    /**
     * Initializes a new instance.
     *
     * @param array $contentFolders An array of foldernames.
     *
     * @global array The configuration of the core.
     */
    public function __construct(array $contentFolders)
    {
        global $cf;

        $this->contentFolders = $contentFolders;
        $this->maxBackups = (int) $cf['backup']['numberoffiles'];
    }

    /**
     * Executes the backup process.
     *
     * @return string HTML
     */
    public function execute()
    {
        $result = '';
        foreach ($this->contentFolders as $folder) {
            $result .= $this->backupSingleFolder($folder);
        }
        return $result;
    }

    /**
     * Creates and deletes the backups of a single folder.
     *
     * @param string $folder A foldername.
     *
     * @return string HTML
     */
    public function backupSingleFolder($folder)
    {
        $result = '';
        $this->contentFolder = $folder;
        $this->contentFile = $this->contentFolder . 'content.htm';
        $basename = date("Ymd_His") . '_content.htm';
        $filename = $this->contentFolder . $basename;
        $needsBackup = $this->needsBackup();
        if (!$needsBackup || $this->backupFile($basename)) {
            if ($needsBackup) {
                $result .= $this->renderCreationInfo($filename);
            }
            $deletions = $this->deleteSurplusBackups();
            $result .= $this->renderDeletionResults($deletions);
        } else {
            e('cntsave', 'backup', $filename);
        }
        return $result;
    }

    /**
     * Returns the basenames of all existing backups.
     *
     * @return array
     */
    private function findBackups()
    {
        $result = array();
        if (is_dir($this->contentFolder) && ($dir = opendir($this->contentFolder))) {
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
    private function needsBackup()
    {
        if ($this->maxBackups <= 0) {
            return false;
        }
        $latestBackup = $this->latestBackup();
        if ($latestBackup) {
            return md5_file($this->contentFile) != md5_file($latestBackup);
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
    private function latestBackup()
    {
        $backups = $this->findBackups();
        if (!empty($backups)) {
            return $this->contentFolder . $backups[count($backups) - 1];
        } else {
            return false;
        }
    }

    /**
     * Creates a backup of the content file.
     *
     * @param string $basename The name of the backup.
     *
     * @return bool
     */
    private function backupFile($basename)
    {
        return copy($this->contentFile, $this->contentFolder . $basename);
    }

    /**
     * Deletes surplus backups.
     *
     * @return array A map of filenames => deletion success.
     */
    private function deleteSurplusBackups()
    {
        $result = array();
        $basenames = $this->findBackups();
        $basenames = array_slice($basenames, 0, -$this->maxBackups);
        foreach ($basenames as $basename) {
            $filename = $this->contentFolder . $basename;
            $result[$filename] = unlink($filename);
        }
        return $result;
    }

    /**
     * Renders the backup creation info message.
     *
     * @param string $filename A filename.
     *
     * @return string HTML
     *
     * @global array The localization of the core.
     */
    private function renderCreationInfo($filename)
    {
        global $tx;

        $message = sprintf('%s %s %s', utf8_ucfirst($tx['filetype']['backup']), $filename, $tx['result']['created']);
        return XH_message('info', $message);
    }

    /**
     * Renders the deletion results.
     *
     * @param array $deletions A map of filenames => deletion success.
     *
     * @return string HTML
     */
    private function renderDeletionResults(array $deletions)
    {
        $results = '';
        foreach ($deletions as $filename => $deleted) {
            if ($deleted) {
                $results .= $this->renderDeletionInfo($filename);
            } else {
                e('cntdelete', 'backup', $filename);
            }
        }
        return $results;
    }

    /**
     * Renders the deletion info message.
     *
     * @param string $filename A filename.
     *
     * @return string HTML
     *
     * @global array The localization of the core.
     */
    private function renderDeletionInfo($filename)
    {
        global $tx;

        $message = sprintf('%s %s %s', utf8_ucfirst($tx['filetype']['backup']), $filename, $tx['result']['deleted']);
        return XH_message('info', $message);
    }
}
