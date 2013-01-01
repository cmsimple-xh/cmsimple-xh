<?php

/**
 * @version $Id: trim.php 4 2012-08-07 21:08:48Z cmb69 $
 * @package utf8
 * @subpackage strings
 */


/**
 * UTF-8 aware replacement for ltrim()
 * 
 * Note: you only need to use this if you are supplying the charlist
 * optional arg and it contains UTF-8 characters. Otherwise ltrim will
 * work normally on a UTF-8 string
 * 
 * @return  string
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @link    http://www.php.net/ltrim
 * @link    http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
 */
function utf8_ltrim( $str, $charlist = FALSE ) {
    if($charlist === FALSE) return ltrim($str);
    
    //quote charlist for use in a characterclass
    $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$charlist);
    
    return preg_replace('/^['.$charlist.']+/u','',$str);
}


/**
 * UTF-8 aware replacement for rtrim()
 * 
 * Note: you only need to use this if you are supplying the charlist
 * optional arg and it contains UTF-8 characters. Otherwise rtrim will
 * work normally on a UTF-8 string
 * 
 * @return  string
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @link    http://www.php.net/rtrim
 * @link    http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
 */
function utf8_rtrim( $str, $charlist = FALSE ) {
    if($charlist === FALSE) return rtrim($str);
    
    //quote charlist for use in a characterclass
    $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$charlist);
  
    return preg_replace('/['.$charlist.']+$/u','',$str);
}


/**
 * UTF-8 aware replacement for trim()
 * 
 * Note: you only need to use this if you are supplying the charlist
 * optional arg and it contains UTF-8 characters. Otherwise trim will
 * work normally on a UTF-8 string
 * 
 * @return  string
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @link    http://www.php.net/trim
 * @link    http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
 */
function utf8_trim( $str, $charlist = FALSE ) {
    if($charlist === FALSE) return trim($str);
    return utf8_ltrim(utf8_rtrim($str, $charlist), $charlist);
}

?>
