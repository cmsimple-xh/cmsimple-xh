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

//Encode QUERY_STRING for remove with use uenc()
function XH_enc_redir($url_query_str = '') {

global $cf;

    $url_sep = $cf['uri']['seperator'];
    $url_query_encstr = '';

    $url_query_array = explode($url_sep, $url_query_str);
    foreach($url_query_array as $url_query_tmp) {
        $tmp = uenc($url_query_tmp);
        $url_query_encstr .= $tmp . $url_sep;
    }
    //some characters are double encoded 
    //(Men%25C3%25BC-Ebenen-%5Bde%5D -> Men%C3%BC-Ebenen-%5Bde%5D)
    $url_query_encstr = preg_replace('#%(25)*#i', '%', $url_query_encstr);
    $url_query_encstr = rtrim($url_query_encstr, $url_sep);

return $url_query_encstr;
}

//Is based on the adc plugin from Holger Irmler <cmsimple@holgerirmler.de>
function XH_avoidDC() {

    global $cf, $su, $s, $xh_publisher;

    $force_ssl = $cf['avoid_dc']['force_ssl'];       //https erzwingen?
    $force_www = $cf['avoid_dc']['select_www'];      //Aufruf mit oder ohne "www"
    $remove_index = $cf['avoid_dc']['remove_index']; //index.pht loeschen?

    $parts = parse_url(CMSIMPLE_URL);
    $scheme = $parts['scheme'];
    $host = $parts['host'];
    $path = $parts['path'];
    $query_str = $_SERVER['QUERY_STRING'];

    $redir = false;

//Force Encrypted Connection
    if ($force_ssl && ($scheme == 'http')) {
        $scheme = 'https';
        $redir = true;
    }

//Remove empty path segments in an URL
//https://github.com/cmsimple-xh/cmsimple-xh/issues/282
    $ep_count = 0;
    $path = preg_replace('#(/){2,}#s', '/', $path, -1, $ep_count);
    if($ep_count > 0) {
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
            $url .= '?' . XH_enc_redir($query_str);
        }
        header("$protocol 301 Moved Permanently");
        header("Location: $url");
        header("Connection: close");
        exit;
    }
}

