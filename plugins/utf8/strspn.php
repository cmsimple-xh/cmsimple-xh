<?php

/**
 * @version $Id: strspn.php 19 2012-08-12 12:57:02Z cmb69 $
 * @package utf8
 * @subpackage strings
 */


/**
 * UTF-8 aware alternative to strspn
 * 
 * Find length of initial segment matching mask
 * Note: requires utf8_strlen and utf8_substr (if start, length are used)
 * 
 * @param   string
 * @return  int
 * @link    http://www.php.net/strspn
 */
function utf8_strspn($str, $mask, $start = NULL, $length = NULL)
{
    if (empty($mask)) {
        return 0;
    }
    
    $mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);
    
    if ( $start !== NULL || $length !== NULL ) {
        $str = utf8_substr($str, $start, $length);
    }
        
    preg_match('/^['.$mask.']+/u',$str, $matches);
    
    if ( isset($matches[0]) ) {
        return utf8_strlen($matches[0]);
    }
    
    return 0;

}

?>
