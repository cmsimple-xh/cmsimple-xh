<?php

/**
 * The abstract base class for plugin config file editing.
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
 * The abstract base class for plugin config file editing.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
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
     *
     * @global array  The paths of system files and folders.
     * @global string The current language.
     * @global string The name of the currently loading plugin.
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
