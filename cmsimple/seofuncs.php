<?php

/**
 * @file seofuncs.php
 *
 * SEO functions.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2020 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 */

/**
 * SEO functionality
 *
 * Integration of the ADC-Core_XH plugin with extended functions (optional)
 *
 * Remove empty path segments in an URL
 * Remove $su from FirstPublicPage
 *
 * @return void
 *
 * @since 1.7.3
 */
function XH_URI_Cleaning()
{
    global $su, $s, $xh_publisher, $pth;

    $parts = parse_url(CMSIMPLE_URL);
    assert(isset($parts['scheme'], $parts['host'], $parts['path']));
    $scheme = $parts['scheme'];
    $host = $parts['host'];
    $port = '';
    if (!empty($parts['port'])) {
        $port = ':' . $parts['port'];
    }
    $path = $parts['path'];
    $query_str = '';
    if (isset($_SERVER['QUERY_STRING'])) {
        $query_str = $_SERVER['QUERY_STRING'];
    }

    $redir = false;

//Integration of the ADC-Core_XH plugin with extended functions (optional)
    if (is_readable($pth['folder']['plugins'] . 'adc_core/seofuncs.php')) {
        include_once $pth['folder']['plugins'] . 'adc_core/seofuncs.php';
    }

//Remove empty path segments in an URL
//https://github.com/cmsimple-xh/cmsimple-xh/issues/282
    $ep_count = 0;
    $path = preg_replace(
        '#(/){2,}#s',
        '/',
        $path,
        -1,
        $ep_count
    );
    if ($ep_count > 0) {
        $redir = true;
    }

//Remove $su from FirstPublicPage
    if (!XH_ADM && $s === $xh_publisher->getFirstPublishedPage()
    && !isset($_GET['login'])
    && !isset($_POST['login'])) {
        $fpp_count = 0;
        $query_str = preg_replace('/^'
                   . preg_quote($su, '/')
                   . '/', '', $query_str, -1, $fpp_count);
        if ($fpp_count > 0) {
            $redir = true;
            header("Cache-Control: no-cache, no-store, must-revalidate");
        }
    }

//Redirect if adjustments were necessary
    if ($redir) {
        if (isset($_SERVER['PROTOCOL'])
        && !empty($_SERVER['PROTOCOL'])) {
            $protocol = $_SERVER['PROTOCOL'];
        } else {
            $protocol = 'HTTP/1.1';
        }
        $url = $scheme . '://' . $host . $port . $path;
        if ($query_str != '') {
            $url .= '?' . XH_uenc_redir($query_str);
        }
        header("$protocol 301 Moved Permanently");
        header("Location: $url");
        header("Connection: close");
        exit;
    }
}

/**
 * Encode QUERY_STRING for redirect with use uenc()
 *
 * @param string $url_query_str
 * @return string
 **/
function XH_uenc_redir($url_query_str = '')
{
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
        foreach ($url_page_array as $url_page_tmp) {
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
