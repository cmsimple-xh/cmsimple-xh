<?php

/**
 * The autoloader.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Tinymce5
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @author    manu <info@pixolution.ch>
 * @copyright 2015 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Onepage_XH
 */


/**
 * Autoloads the plugin classes.
 *
 * @param string $class A class name.
 *
 * @return void
 *
 * @global array The paths of system files and folders.
 */
function Tinymce5_autoload($class)
{
    global $pth;

    $parts = explode('\\', $class, 2);
    if ($parts[0] == 'Tinymce5') {
        include_once $pth['folder']['plugins'] . 'tinymce5/classes/'
            . $parts[1] . '.php';
    }
}

spl_autoload_register('Tinymce5_autoload');

?>
