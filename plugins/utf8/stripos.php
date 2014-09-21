<?php

/**
 * @version $Id: stripos.php 40 2014-08-18 17:19:50Z cmb69 $
 * @package utf8
 * @subpackage strings
 */

if (function_exists('mb_stripos')) {

    /**
     * Finds position of first occurrence of a string within another, case
     * insensitive. Returns <var>false</var> if needle is not found.
     *
     * @param string $haystack A haystack.
     * @param string $needle   A needle.
     * @param int    $offset   An offset in Unicode code points.
     *
     * @return int
     */
    function utf8_stripos($haystack, $needle, $offset = 0)
    {
        return mb_stripos($haystack, $needle, $offset);
    }

} else {

    function utf8_stripos($haystack, $needle, $offset = 0)
    {
        return utf8_strpos(
            utf8_strtolower($haystack), utf8_strtolower($needle), $offset
        );
    }

}

?>
