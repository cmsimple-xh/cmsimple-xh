<?php

/**
 * The abstract base class for editing of core config and text files.
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
 * The abstract base class for editing of core config and text files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
abstract class CoreArrayFileEdit extends ArrayFileEdit
{
    /**
     * Constructs an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The current language.
     * @global string The key of the system file.
     * @global array  The localization of the plugins.
     */
    public function __construct()
    {
        global $pth, $sl, $file, $tx;

        $this->filename = $pth['file'][$file];
        $this->caption = utf8_ucfirst($tx['filetype'][$file]);
        $this->metaLangFile = $pth['folder']['language'] . 'meta' . $sl . '.php';
        parent::__construct();
    }

    /**
     * Returns the the file contents as string for saving.
     *
     * @return string
     */
    protected function asString()
    {
        $o = "<?php\n\n";
        foreach ($this->cfg as $cat => $opts) {
            foreach ($opts as $name => $opt) {
                $opt = addcslashes($opt['val'], "\0..\37\"\$\\");
                $o .= "\$$this->varName['$cat']['$name']=\"$opt\";\n";
            }
        }
        return $o;
    }

    /**
     * Returns an array of select options for files.
     *
     * @param string $fn    The key of the system folder.
     * @param string $regex The regex the filename must match.
     *
     * @return array
     *
     * @global array The paths of system files and folders.
     */
    protected function selectOptions($fn, $regex)
    {
        global $pth;

        $options = array();
        if (is_dir($pth['folder'][$fn]) && ($dh = opendir($pth['folder'][$fn]))) {
            while (($p = readdir($dh)) !== false) {
                if (preg_match($regex, $p, $m)) {
                    $options[] = $m[1];
                }
            }
            closedir($dh);
        }
        natcasesort($options);
        return $options;
    }
}
