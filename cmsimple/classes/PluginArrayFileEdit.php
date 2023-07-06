<?php

namespace XH;

/**
 * The abstract base class for plugin config file editing.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
 */
abstract class PluginArrayFileEdit extends ArrayFileEdit
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
        global $pth, $sl, $plugin;

        $this->plugin = $plugin;
        $this->metaLangFile = $pth['folder']['plugins'] . $plugin
            . '/languages/meta' . $sl . '.php';
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
                $key = $cat;
                !empty($name) and $key .= "_$name";
                $opt = addcslashes($opt['val'], "\0..\37\"\$\\");
                $o .= "\$$this->varName['$this->plugin']['$key']=\"$opt\";\n";
            }
        }
        return $o;
    }
}
