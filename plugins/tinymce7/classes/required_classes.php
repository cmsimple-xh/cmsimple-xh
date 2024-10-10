<?php

/**
 * The autoloader.
 *
 * PHP version 8
 *
 * @category  CMSimple_XH
 * @package   Tinymce7
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @author    manu <info@pixolution.ch>
 * @copyright 2023 Christoph M. Becker <http://3-magi.net>
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
function Tinymce7_autoload($class)
{
    global $pth;

    $parts = explode('\\', $class, 2);
    if ($parts[0] == 'Tinymce7') {
        include_once $pth['folder']['plugins'] . 'tinymce7/classes/'
            . $parts[1] . '.php';
    }
}

spl_autoload_register('Tinymce7_autoload');

?>
