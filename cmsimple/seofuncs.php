<?php

/**
 * @file seofuncs.php
 *
 * SEO functions.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

//Is based on the adc plugin from Holger Irmler <cmsimple@holgerirmler.de>
function XH_avoidDC() {

    global $cf, $su, $s, $xh_publisher;

    $use_ssl = $cf['avoid_dc']['use_ssl'];           //https erzwingen?
    $force_www = $cf['avoid_dc']['select_www'];      //Aufruf mit oder ohne "www"
    $remove_index = $cf['avoid_dc']['remove_index']; //index.pht loeschen?

    $parts = parse_url(CMSIMPLE_URL);
    $scheme = $parts['scheme'];
    $host = $parts['host'];
    $path = $parts['path'];
    $query_str = $_SERVER['QUERY_STRING'];

    $url_sep = $cf['uri']['seperator'];

    $redir = false;

//Force Encrypted Connection
    if (($use_ssl == 'force') && ($scheme == 'http')) {
        $scheme = 'https';
        $redir = true;
    }

//Remove empty path segments in an URL
//https://github.com/cmsimple-xh/cmsimple-xh/issues/282
    $ep_count = 0;
    $path = preg_replace('#(/){2,}#s',
                         '/',
                         $path,
                         -1,
                         $ep_count);
    if ($ep_count > 0) {
        $redir = true;
    }

//Replaced encoded url-seperator (i.e. / --> %2F, : --> %3A)
//sometimes also double encoded - / --> %252F --> %25252F
    $enus_count = 0;
    $query_str = preg_replace('#%(25)*' . bin2hex($url_sep) . '#i',
                              $url_sep,
                              $query_str,
                              -1,
                              $enus_count);
    if ($enus_count > 0) {
        $redir = true;
    }

//Remove index.php
    if ($remove_index) {
        if (strtolower(substr($path, -9)) == 'index.php') {
            $path = substr_replace($path, '', -9);
            $redir = true;
        }
    }

    if ($force_www == 'force') {
        //Call page with "www"
        if (strtolower(substr($host, 0, 4)) != 'www.') {
            $host = 'www.' . $host;
            $redir = true;
        }
    }
    if ($force_www == 'none') {
        //or filter out "www" if required
        if (strtolower(substr($host, 0, 4)) == 'www.') {
            $host = substr_replace($host, '', 0, 4);
            $redir = true;
        }
    }

//Remove $su from FirstPublicPage
    if (!XH_ADM && $s === $xh_publisher->getFirstPublishedPage() 
    && !isset($_GET['login']) 
    && !isset($_POST['login'])) {
        $fpp_count = 0;
        $query_str = preg_replace('/^' 
                   . preg_quote($su, '/') 
                   . '/', '', $query_str, -1, $fpp_count);
        if($fpp_count > 0) {
            $redir = true;
        }
    }

//Redirect if adjustments were necessary
    if ($redir) {
        if(isset($_SERVER['PROTOCOL'])
        && !empty($_SERVER['PROTOCOL'])) {
            $protocol = $_SERVER['PROTOCOL'];
        } else {
            $protocol = 'HTTP/1.1';
        }
        $url = $scheme . '://' . $host . $path;
        if ($query_str != '') {
            $url .= '?' . XH_uenc_redir($query_str);
        }
        header("$protocol 301 Moved Permanently");
        header("Location: $url");
        header("Connection: close");
        exit;
    }
}

//Encode QUERY_STRING for redirect with use uenc()
function XH_uenc_redir($url_query_str = '') {

global $cf;

    $url_sep = $cf['uri']['seperator'];
    $url_query_uencstr = '';

    $url_query_parts = array();
    if (strpos($url_query_str, '&') !== false) {
        $url_query_parts[] = strstr($url_query_str, '&', true);
        $url_query_parts[] = strstr($url_query_str, '&');
    } else {
        $url_query_parts[] = $url_query_str;
    }
    if (strpos($url_query_parts['0'], '=') === false) {
        $url_page_array = explode($url_sep, $url_query_parts['0']);
        foreach($url_page_array as $url_page_tmp) {
            $tmp = uenc($url_page_tmp);
            $tmp = preg_replace('#%(25)*#i', '%', $tmp);
            $url_query_uencstr .= $tmp . $url_sep;
        }
        $url_query_uencstr = rtrim($url_query_uencstr, $url_sep);
    } else {
        $url_query_uencstr = $url_query_parts['0'];
    }
    
    $url_query_uencstr = $url_query_uencstr 
                      . ($url_query_parts['1'] ? $url_query_parts['1'] : '');

return $url_query_uencstr;
}

//Return for $mcf['avoid_dc']['use_ssl'];
function XH_Check_SSL() {

    $field = 'enum:-,force';

    $parts = parse_url(CMSIMPLE_URL);
    $host = $parts['host'];

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,'https://' . $host);
        curl_setopt($ch, CURLOPT_CERTINFO, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0) {
            // certificate error or failed to connect port 443
            $field = 'enum:-';
        } else {
            $field = 'enum:-,force';
        }
    return $field;
    }

    if (function_exists('stream_socket_client')) {
        $errno = '';
        $errstr = '';
        $opt = stream_context_create(array('ssl' => array('capture_peer_cert' => TRUE)));
        $fp = stream_socket_client('ssl://' . $host . ':443',
              $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $opt);
        if (!$fp) {
            // certificate error or failed to connect port 443
            $field = 'enum:-';
        } else {
            $field = 'enum:-,force';
        }
    return $field;
    }

return $field;
}
