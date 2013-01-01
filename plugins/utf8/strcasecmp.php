<?php

/**
 * @version $Id: strcasecmp.php 4 2012-08-07 21:08:48Z cmb69 $
 * @package utf8
 * @subpackage strings
 */


/**
 * UTF-8 aware alternative to strcasecmp
 * A case insensivite string comparison
 * 
 * Note: requires utf8_strtolower
 * 
 * @param   string
 * @param   string
 * @return  int
 * @link    http://www.php.net/strcasecmp
 * @see     utf8_strtolower()
 */
function utf8_strcasecmp($strX, $strY) {
    $strX = utf8_strtolower($strX);
    $strY = utf8_strtolower($strY);
    return strcmp($strX, $strY);
}

?>
