<?php

/**
 * @version     $Id: wordwrap.php 4 2012-08-07 21:08:48Z cmb69 $
 * @package     utf8
 * @subpackage  strings
 */


/**
 * Wraps a string to a given number of characters using a string break character.
 * 
 * @param   string $str  The input string.
 * @param   int $width  The number of characters at which the string will be wrapped.
 * @param   string $break  The line is broken using the optional break parameter.
 * @param   bool $cut  If the cut is set to TRUE, the string is always wrapped at or before the specified width. So if you have a word that is larger than the given width, it is broken apart.
 * @return  string  Returns the given string wrapped at the specified length.
 */
function utf8_wordwrap($str, $width = 75, $break = "\n", $cut = false)
{
    $ws = "\x09\x0a\x0b\x0c\x0d\x20\xc2\x85\xc2\xa0\xe1\x9a\x80\xe1\xa0\x8e"
        . "\xe2\x80\x80\xe2\x80\x81\xe2\x80\x82\xe2\x80\x83\xe2\x80\x84"
        . "\xe2\x80\x85\xe2\x80\x86\xe2\x80\x87\xe2\x80\x88\xe2\x80\x89"
        . "\xe2\x80\x8a\xe2\x80\xa8\xe2\x80\xa9\xe2\x80\xaf\xe2\x81\x9f"
        . "\xe3\x80\x80";
    $qmax = "{1,$width}";
    $qmin = '{' . $width . ',' . ($cut ? $width : '') . '}';
    $lines = array();
    while (strlen($str) > 0) {
        preg_match("/^(?:(.$qmax)(?:[$ws]|\$)|([^$ws]$qmin)\s?)/su", $str, $m);
        $lines[] = !empty($m[1]) ? $m[1] : $m[2];
        $str = substr($str, strlen($m[0]));
    }
    return implode($lines, $break);
}

?>
