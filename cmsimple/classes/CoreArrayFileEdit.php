<?php

namespace XH;

/**
 * The abstract base class for editing of core config and text files.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
 */
abstract class CoreArrayFileEdit extends ArrayFileEdit
{
    /** @var string */
    protected $varName;

    /**
     * Constructs an instance.
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
        sort($options, SORT_NATURAL | SORT_FLAG_CASE);
        return $options;
    }
}
