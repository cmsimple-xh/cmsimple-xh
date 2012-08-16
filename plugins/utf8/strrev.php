<?php

/**
 * @version $Id: strrev.php 4 2012-08-07 21:08:48Z cmb69 $
 * @package utf8
 * @subpackage strings
 */


/**
 * UTF-8 aware alternative to strrev
 * 
 * Reverse a string
 * 
 * @param   string UTF-8 encoded
 * @return  string characters in string reverses
 * @link    http://www.php.net/strrev
 */
function utf8_strrev($str){
    preg_match_all('/./us', $str, $ar);
    return join('',array_reverse($ar[0]));
}

?>
