<?php

namespace XH;

/**
 * The abstract base class for template config file editing.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 * @since     1.8
 */
abstract class TemplateArrayFileEdit extends ArrayFileEdit
{
    /**
     * The name of the config array variable.
     *
     * @var string
     */
    protected $varName = null;

    /**
     * Constructs an instance.
     */
    public function __construct()
    {
        global $pth, $sl, $file;

        $this->filename = $pth['file'][$file];
        $this->metaLangFile = "{$pth['folder']['template']}languages/meta$sl.php";
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
}
