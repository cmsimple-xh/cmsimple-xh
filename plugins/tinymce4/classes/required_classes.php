<?php

/**
 * The autoloader.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Tinymce4
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @author    manu <info@pixolution.ch>
 * @copyright 2015 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Onepage_XH
 */


define('TINYMCE4_CDN_ORIG', 'https://cloud.tinymce.com/stable/tinymce.min.js');  //TinyMCE4 externally loaded
//define('TINYMCE4_VARIANT', 'jQuery');  //TinyMCE4 jQuery Version not yet realized 

/**
 * Autoloads the plugin classes.
 *
 * @param string $class A class name.
 *
 * @return void
 *
 * @global array The paths of system files and folders.
 */
function Tinymce4_autoload($class)
{
    global $pth;

    $parts = explode('\\', $class, 2);
    if ($parts[0] == 'Tinymce4') {
        include_once $pth['folder']['plugins'] . 'tinymce4/classes/'
            . $parts[1] . '.php';
    }
}

spl_autoload_register('Tinymce4_autoload');

?>
