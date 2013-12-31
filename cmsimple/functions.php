<?php

/**
 * General functions.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */


/*
  ======================================
  @CMSIMPLE_XH_VERSION@
  @CMSIMPLE_XH_DATE@
  based on CMSimple version 3.3 - December 31. 2009
  For changelog, downloads and information please see http://www.cmsimple-xh.org/
  ======================================
  -- COPYRIGHT INFORMATION START --
  Based on CMSimple version 3.3 - December 31. 2009
  Small - simple - smart
  (c) 1999-2009 Peter Andreas Harteg - peter@harteg.dk

  This file is part of CMSimple_XH
  For licence see notice in /cmsimple/cms.php
  -- COPYRIGHT INFORMATION END --
  ======================================
 */


/**
 * Returns the inner HTML of the body element of the given URL.
 *
 * @param string $u A URL.
 *
 * @return string The (X)HTML.
 */
function geturl($u)
{
    $t = '';
    if ($fh = fopen(preg_replace("/\&amp;/is", "&", $u), "r")) {
        while (!feof($fh)) {
            $t .= fread($fh, 1024);
        }
        fclose($fh);
        return preg_replace("/.*<body[^>]*>(.*)<\/body>.*/is", '$1', $t);
    }
}

/**
 * Returns the contents of the given URL adding all current GET parameters.
 *
 * @param string $u A URL.
 *
 * @return string The (X)HTML.
 */
function geturlwp($u)
{
    global $su;

    $t = '';
    $qs = preg_replace(
        "/^" . preg_quote($su, '/') . "(\&)?/s",
        "", sv('QUERY_STRING')
    );
    if ($fh = fopen($u . '?' . $qs, "r")) {
        while (!feof($fh)) {
            $t .= fread($fh, 1024);
        }
        fclose($fh);
        return $t;
    }
}

/**
 * Returns the code to display a photogallery.
 *
 * @param string $u Autogallery's installation folder.
 *
 * @global string The URL of the active page.
 *
 * @return string The (X)HTML.
 *
 * @deprecated since 1.5.4. Use a gallery plugin instead.
 */
function autogallery($u)
{
    global $su;

    trigger_error('Function autogallery() is deprecated', E_USER_DEPRECATED);

    return preg_replace(
        "/.*<!-- autogallery -->(.*)<!-- \/autogallery -->.*/is", '$1',
        preg_replace(
            "/(option value=\"\?)(p=)/is", '${1}' . $su . '&$2',
            preg_replace(
                "/(href=\"\?)/is", '${1}' . $su . '&amp;',
                preg_replace("/(src=\")(\.)/is", '${1}' . $u . '$2', geturlwp($u))
            )
        )
    );
}

/**
 * Returns a page heading.
 *
 * @param int $n The index of the page.
 *
 * @return string
 *
 * @see $h
 */
function h($n)
{
    global $h;

    return $h[$n];
}

/**
 * Returns a page's menu level.
 *
 * @param int $n The index of the page.
 *
 * @return int
 *
 * @see $l
 */
function l($n)
{
    global $l;

    return $l[$n];
}

/**
 * Returns a text with CMSimple scripting evaluated.
 *
 * Scripts are evaluated in the global scope.
 *
 * @param string $__text   The text.
 * @param bool   $__compat Whether only last CMSimple script should be evaluated.
 *
 * @global string The output.
 *
 * @return string
 *
 * @since  1.5
 */
function evaluate_cmsimple_scripting($__text, $__compat = true)
{
    global $output;
    foreach ($GLOBALS as $__name => $__dummy) {
        $$__name = &$GLOBALS[$__name];
    }

    $__scope_before = null; // just that it exists
    $__scripts = array();
    preg_match_all('~#CMSimple (.*?)#~is', $__text, $__scripts);
    if (count($__scripts[1]) > 0) {
        $output = preg_replace('~#CMSimple (?!hide)(.*?)#~is', '', $__text);
        if ($__compat) {
            $__scripts[1] = array_reverse($__scripts[1]);
        }
        foreach ($__scripts[1] as $__script) {
            if (strtolower($__script) !== 'hide'
                && strtolower($__script) !== 'remove'
            ) {
                $__script = preg_replace(
                    array(
                        "'&(quot|#34);'i", "'&(amp|#38);'i", "'&(apos|#39);'i",
                        "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i"
                    ),
                    array("\"", "&", "'", "<", ">", " "),
                    $__script
                );
                $__scope_before = array_keys(get_defined_vars());
                eval($__script);
                $__scope_after = array_keys(get_defined_vars());
                $__diff = array_diff($__scope_after, $__scope_before);
                foreach ($__diff as $__var) {
                    $GLOBALS[$__var] = $$__var;
                }
                if ($__compat) {
                    break;
                }
            }
        }
        $eval_script_output = $output;
        $output = '';
        return $eval_script_output;
    }
    return $__text;
}

/**
 * Returns a text with all plugin calls evaluatated.
 *
 * All Plugins which are called through a function-call
 * can use this. At the moment it is not possible to do
 * this with class-based plugins. They need to be called
 * through standard-CMSimple-Scripting. Alternatively one
 * can offer a functional wrapper.
 *
 * To call a plugin, place the following on a CMSimple_XH page (example):
 * {{{pluginfunction('parameters');}}}
 *
 * About the scope rules see {@link XH_evaluateSinglePluginCall}.
 *
 * @param string $text The text.
 *
 * @return string
 *
 * @global array The localization of the core.
 *
 * @since 1.5
 */
function evaluate_plugincall($text)
{
    global $tx;

    $message = '<span class="xh_fail">' . $tx['error']['plugincall']
        . '</span>';
    $re = '/{{{(?:[^:]+:)?(([a-z_0-9]+)\(.*?\);)}}}/iu';
    preg_match_all($re, $text, $calls, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $results = array();
    foreach ($calls as $call) {
        $expression = preg_replace(
            array(
                '/&(quot|#34);/i', '/&(amp|#38);/i', '/&(apos|#39);/i',
                '/&(lt|#60);/i', '/&(gt|#62);/i', '/&(nbsp|#160);/i'
            ),
            array('"', '&', '\'', '<', '>', ' '),
            $call[1][0]
        );
        $function = $call[2][0];
        if (function_exists($function)) {
            $results[] = XH_evaluateSinglePluginCall($expression);
        } else {
            $results[] = sprintf($message, $function);
        }
    }
    $calls = array_reverse($calls);
    $results = array_reverse($results);
    foreach ($calls as $i => $call) {
        $length = strlen($call[0][0]);
        $offset = $call[0][1];
        XH_spliceString($text, $offset, $length, $results[$i]);
    }
    return $text;
}

/**
 * Returns the result of evaluating a single plugin call expression.
 *
 * The expression is evaluated as if it where in the global namespace.
 * To avoid clashes with local variables of this function,
 * these are prefixed with a triple underscore.
 * Reference parameters of the function do <b>not</b> modify the global scope.
 *
 * @param string $___expression The expression to evaluate.
 *
 * @return srting
 *
 * @since 1.6
 */
function XH_evaluateSinglePluginCall($___expression)
{
    foreach ($GLOBALS as $___var => $___value) {
        $$___var = $GLOBALS[$___var];
    }
    return eval('return ' . $___expression);
}

/**
 * Removes a portion of a string and replaces it with something else.
 * This does basically the same to strings as array_splice() for arrays.
 * Note that the behavior of negative values for <var>$offset</var>
 * and <var>$length</var> is not defined.
 *
 * @param string &$string     The string to manipulate.
 * @param int    $offset      Offset of the string where to start the replacement.
 * @param int    $length      The number of characters to be replaced.
 * @param string $replacement The string to replace the removed characters.
 *
 * @return string The replaced characters.
 *
 * @since 1.6
 */
function XH_spliceString(&$string, $offset, $length = 0, $replacement = '')
{
    $result = substr($string, $offset, $length);
    $string = substr($string, 0, $offset) . $replacement
        . substr($string, $offset + $length);
    return $result;
}

/**
 * Returns a text with CMSimple scripting and plugin calls evaluated.
 *
 * @param string $text   The text.
 * @param bool   $compat Whether only last CMSimple script will be evaluated.
 *
 * @return void
 *
 * @since 1.5
 */
function evaluate_scripting($text, $compat = true)
{
    return evaluate_cmsimple_scripting(evaluate_plugincall($text), $compat);
}

/**
 * Returns content of the first page with the heading $heading
 * with the heading removed and all scripting evaluated.
 * Returns false, if the page doesn't exist.
 *
 * @param string $heading The page heading.
 *
 * @global array The content of the pages.
 * @global int   The number of pages.
 * @global array The headings of the pages.
 * @global array The configuation of the core.
 * @global bool  Whether edit mode is active.
 *
 * @return string The (X)HTML.
 */
function newsbox($heading)
{
    global $c, $cl, $h, $cf, $edit;

    for ($i = 0; $i < $cl; $i++) {
        if ($h[$i] == $heading) {
            $pattern = "/.*<\/h[1-".$cf['menu']['levels']."]>/is";
            $body = preg_replace($pattern, "", $c[$i]);
            $pattern = '/#CMSimple (.*?)#/is';
            return $edit
                ? $body
                : preg_replace($pattern, '', evaluate_scripting($body, false));
        }
    }
    return false;
}

/**
 * Calls init_*() of the configured editor. Returns whether that succeeded.
 *
 * @param array $elementClasses Elements with these classes will become an editor.
 * @param mixed $initFile       The init file or configuration.
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the core.
 *
 * @return bool
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces
 *
 * @since  1.5
 */
function init_editor($elementClasses = array(),  $initFile = false)
{
    global $pth, $cf;

    $fn = $pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php';
    if (!file_exists($fn)) {
         return false;
    }
    include_once $fn;
    $function = 'init_' . $cf['editor']['external'];

    if (!function_exists($function)) {
        return false;
    }

    $function($elementClasses, $initFile);

    return true;
}

/**
 * Calls include_*() of the configured editor. Returns whether that succeeded.
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the core.
 *
 * @return bool
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces
 *
 * @since  1.5
 */
function include_editor()
{
    global $pth, $cf;

    $fn = $pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php';
    if (!file_exists($fn)) {
         return false;
    }
    include_once $fn;
    $function = 'include_' . $cf['editor']['external'];

    if (!function_exists($function)) {
        return false;
    }

    $function();

    return true;
}

/**
 * Returns the result of calling *_replace() of the configured editor.
 * Returns false on failure.
 *
 * @param string $elementID The element with this ID will become an editor.
 * @param string $config    The configuration.
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the core.
 *
 * @return void
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces

 * @since 1.5
 */
function editor_replace($elementID = false, $config = '')
{
    global $pth, $cf;

    if (!$elementID) {
        trigger_error('No elementID given', E_USER_NOTICE);
        return false;
    }

    $fn = $pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php';
    if (!file_exists($fn)) {
         return false;
    }
    include_once $fn;
    $function = $cf['editor']['external'] . '_replace';

    if (!function_exists($function)) {
        return false;
    }

    return $function($elementID, $config);
}

/**
 * Callback for output buffering. Returns the postprocessed (X)HTML.
 *
 * Currently debug information and admin menu are prepended,
 * and $bjs is appended to the body element.
 *
 * @param string $html The (X)HTML generated so far.
 *
 * @global int    The index of the active page.
 * @global string The (X)HTML of the contents area.
 * @global array
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global string (X)HTML to be preprended to the closing BODY tag.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_finalCleanUp($html)
{
    global $s, $o, $errors, $cf, $tx, $bjs;

    if (XH_ADM === true) {
        $debugHint = '';
        $errorList = '';
        $margin = 36;

        if ($debugMode = error_reporting() > 0) {
            $debugHint .= '<div class="xh_debug">' . "\n"
                . $tx['message']['debug_mode'] . "\n"
                . '</div>' . "\n";
            $margin += 22;
        }

        $adminMenuFunc = trim($cf['editmenu']['external']);
        if ($adminMenuFunc != '' && function_exists($adminMenuFunc)) {
            $margin -= 36;
        } else {
            $adminMenuFunc = 'XH_adminMenu';
        }

        if (count($errors) > 0) {
            $errorList .= '<div class="xh_debug_warnings"><ul>';
            $errors = array_unique($errors);
            foreach ($errors as $error) {
                $errorList .= '<li>' . $error . '</li>';
            }
            $errorList .= '</ul></div>';
        }
        if (isset($cf['editmenu']['scroll'])
            && $cf['editmenu']['scroll'] == 'true'
        ) {
            $id = ' id="xh_adminmenu_scrolling"';
            $margin = 0;
        } else {
            $id =' id="xh_adminmenu_fixed"';
            $replacement = '<style type="text/css">html {margin-top: ' . $margin
                . 'px;}</style>' ."\n" . '$0';
            $html = preg_replace('~</head>~i', $replacement, $html, 1);
        }

        $adminMenu = call_user_func($adminMenuFunc, XH_plugins(true));
        $replacement = '$0' . '<div' . $id . '>' . addcslashes($debugHint, '$\\')
            . addcslashes($adminMenu, '$\\')
            . '</div>' ."\n" . addcslashes($errorList, '$\\');
        $html = preg_replace('~<body[^>]*>~i', $replacement, $html, 1);
    }

    if (!empty($bjs)) {
        $html = str_replace('</body', "$bjs\n</body", $html);
    }

    return $html;
}

/**
 * Initializes a global variable according to a GET or POST parameter.
 *
 * @param string $name The name of the global variable.
 *
 * @return void
 *
 * @see http://www.cmsimpleforum.com/viewtopic.php?f=29&t=5315
 */
function initvar($name)
{
    if (!isset($GLOBALS[$name])) {
        if (isset($_GET[$name])) {
            $GLOBALS[$name] = $_GET[$name];
        } elseif (isset($_POST[$name])) {
            $GLOBALS[$name] = $_POST[$name];
        } else {
            $GLOBALS[$name] = '';
        }
    }
}

/**
 * Returns the value of a $_SERVER key.
 *
 * Has fallback to $HTTP_SERVER_VARS for PHP < 4.1.0. ;-)
 *
 * @param string $s The key.
 *
 * @return string
 */
function sv($s)
{
    if (!isset($_SERVER)) {
        global $_SERVER;
        $_SERVER = $GLOBALS['HTTP_SERVER_VARS'];
    }
    if (isset($_SERVER[$s])) {
        return $_SERVER[$s];
    } else {
        return '';
    }
}

/**
 * Returns $t with all (consecutive) line endings replaced by a single newline.
 *
 * @param string $t A string.
 *
 * @return string
 */
function rmnl($t)
{
    return preg_replace("/(\r\n|\r|\n)+/", "\n", $t);
}

/**
 * Returns $str with all (consecutive) whitespaces replaced by a single space.
 *
 * @param string $str A string.
 *
 * @return string
 *
 * @since 1.5.4
 */
function XH_rmws($str)
{
    $ws = '[\x09-\x0d\x20]'
        . '|\xc2[\x85\xa0]'
        . '|\xe1(\x9a\x80|\xa0\x8e)'
        . '|\xe2\x80[\x80-\x8a\xa8\xa9\xaf]'
        . '|\xe2\x81\x9f'
        . '|\xe3\x80\x80';
    return preg_replace('/(?:' . $ws . ')+/', ' ', $str);
}

/**
 * Returns $t with all line endings removed.
 *
 * @param string $t A string.
 *
 * @return string
 */
function rmanl($t)
{
    return preg_replace("/(\r\n|\r|\n)+/", "", $t);
}

/**
 * Returns the un-quoted $t, i.e. reverses the effect
 * of magic_quotes_gpc/magic_quotes_sybase.
 *
 * If in doubt, use on all user input (but at most once!).
 *
 * @param string $t A string.
 *
 * @return string
 */
function stsl($t)
{
    return get_magic_quotes_gpc() ? stripslashes($t) : $t;
}

/**
 * Makes the file available for download.
 *
 * If the file can't be downloaded, an HTTP 404 Not found response will be generated.
 *
 * @param string $fl The file name.
 *
 * @global string The script name.
 * @global string The file to download.
 *
 * @return void
 */
function download($fl)
{
    global $download, $o;

    if (!is_readable($fl)
        || ($download != '' && !preg_match('/.+\..+$/', $fl))
    ) {
        shead('404');
        $o .= '<p>File ' . $fl . '</p>';
        return;
    } else {
        header('Content-Type: application/save-as');
        header('Content-Disposition: attachment; filename="' . basename($fl) . '"');
        header('Content-Length:' . filesize($fl));
        header('Content-Transfer-Encoding: binary');
        readfile($fl);
        exit;
    }
}

/**
 * Returns whether the file exists in the download folder
 * and is available for download.
 *
 * @param string $fl The download URL, e.g. ?download=file.ext
 *
 * @return bool
 *
 * @global array  The paths of system files and folders.
 * @global string The script name.
 *
 * @deprecated since 1.6.
 */
function chkdl($fl)
{
    global $pth, $sn;

    trigger_error(
        'Function ' . __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED
    );
    $m = false;
    if (is_dir($pth['folder']['downloads'])) {
        if ($fd = opendir($pth['folder']['downloads'])) {
            while (($p = readdir($fd)) == true) {
                if (preg_match("/.+\..+$/", $p)) {
                    if ($fl == $sn . '?download=' . $p) {
                        $m = true;
                    }
                }
            }
            closedir($fd);
        }
    }
    return $m;
}

/**
 * Returns the content of file $filename, if it does exist, null otherwise.
 *
 * @param string $fl The file name.
 *
 * @return string
 *
 * @deprecated since 1.6
 */
function rf($fl)
{
    trigger_error(
        'Function ' . __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED
    );
    if (!file_exists($fl)) {
        return;
    }
    clearstatcache();
    return file_get_contents($fl);
}

/**
 * Checks wether the file exists, is readable,
 * and if $writeable is true, is writeable.
 *
 * Appends an according message to $e otherwise.
 *
 * @param string $fl       A key of $pth['file'].
 * @param bool   $writable Whether the file has to writable.
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 *
 * @return bool
 *
 * @deprecated since 1.6.
 */
function chkfile($fl, $writable)
{
    global $pth, $tx;

    trigger_error(
        'Function '. __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED
    );

    $t = isset($pth['file'][$fl]) ? $pth['file'][$fl] : '';
    if ($t == '') {
        e('undefined', 'file', $fl);
    } elseif (!file_exists($t)) {
        e('missing', $fl, $t);
    } elseif (!is_readable($t)) {
        e('notreadable', $fl, $t);
    } elseif (!is_writable($t) && $writable) {
        e('notwritable', $fl, $t);
    }
}

/**
 * Appends an error message about the file to $e.
 *
 * @param string $et A key in $tx['error'].
 * @param string $ft A key in $tx['filetype'].
 * @param string $fn The file name.
 *
 * @global string Error messages as (X)HTML fragment consisting of LI Elements.
 * @global array  The localization of the core.
 *
 * @return void
 */
function e($et, $ft, $fn)
{
    global $e, $tx;

    $e .= '<li><b>' . $tx['error'][$et] . ' ' . $tx['filetype'][$ft] . '</b>'
        . tag('br') . $fn . '</li>' . "\n";
}

/**
 * Reads and parses the content file and sets global variables accordingly.
 *
 * @global array  The contents of the pages.
 * @global int    The number of pages.
 * @global array  The headings of the pages.
 * @global array  The URLs of the pages.
 * @global array  The menu levels of the pages.
 * @global string The URL of the current page.
 * @global string The index of the current page.
 * @global array  The localization of the core.
 * @global string Error messages as (X)HTML fragment consisting of LI Elements.
 * @global object The pagedata router.
 *
 * @return void
 */
function rfc()
{
    global $c, $cl, $h, $u, $l, $su, $s, $tx, $e, $pth, $pd_router;

    $contents = XH_readContents();
    if ($contents === false) {
        e('missing', 'content', $pth['file']['content']);
        $contents = array(
            array(), array(), array(), array(), array(),
            new XH_PageDataRouter(array(), array(), array(), array())
        );
    }
    list($u, $tooLong, $h, $l, $c, $pd_router) = array_values($contents);
    $duplicate = 0;

    $cl = count($c);
    $s = -1;

    if ($cl == 0) {
        $c[] = '<h1>' . $tx['toc']['newpage'] . '</h1>';
        $h[] = trim(strip_tags($tx['toc']['newpage']));
        $u[] = uenc($h[0]);
        $l[] = 1;
        if ($su == $u[0]) {
            $s = 0;
        }
        $cl = 1;
        $pd_router->appendNewPage();
        return;
    }

    foreach ($tooLong as $i => $tl) {
        if (XH_ADM && $tl) {
            $e .= '<li><b>' . $tx['uri']['toolong'] . '</b>' . tag('br')
                . '<a href="?' . $u[$i] . '">' . $h[$i] . '</a>' . '</li>';
        }
    }

    foreach ($u as $i => $url) {
        if ($su == $u[$i] || $su == urlencode($u[$i])) {
            $s = $i;
        } // get index of selected page

        for ($j = $i + 1; $j < $cl; $j++) {   //check for duplicate "urls"
            if ($u[$j] == $u[$i]) {
                $duplicate++;
                $h[$j] = $tx['toc']['dupl'] . ' ' . $duplicate;
                $u[$j] = uenc($h[$j]);
            }
        }
    }
}

/**
 * Reads and parses a content file.
 *
 * Returns an associative array containing the following information:
 * - <var>urls</var>: The URLs of the pages.
 * - <var>too_long</var>:  Flags, whether URLs were too long.
 * - <var>headings</var>: The headings of the pages.
 * - <var>levels</var>: The menu levels of the pages.
 * - <var>pages</var>: The contents of the pages.
 * - <var>pd_router</var>: A page data router object.
 * Returns FALSE, if the file couldn't be read.
 *
 * @param string $language The language to read.
 *                         <var>null</var> means the default language.
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the core.
 * @global bool  Whether edit mode is active.
 *
 * @return array
 *
 * @since 1.6
 */
function XH_readContents($language = null)
{
    global $pth, $cf, $edit;

    if (isset($language)) {
        $contentFolder = $pth['folder']['base'] . 'content/' . $language . '/';
        $contentFile = $contentFolder . 'content.htm';
        $pageDataFile = $contentFolder . 'pagedata.php';
        $tx = XH_includeVar($pth['folder']['language'] . $language . '.php', 'tx');
    } else {
        $contentFile = $pth['file']['content'];
        $pageDataFile = $pth['file']['pagedata'];
        $tx = $GLOBALS['tx'];
    }

    $c = array();
    $h = array();
    $u = array();
    $tooLong = array();
    $l = array();
    $empty = 0;
    $search = explode(XH_URICHAR_SEPARATOR, $tx['urichar']['org']);
    $replace = explode(XH_URICHAR_SEPARATOR, $tx['urichar']['new']);

    if (($content = XH_readFile($contentFile)) === false) {
        return false;
    }
    $stop = $cf['menu']['levels'];
    $content = preg_split('/(?=<h[1-' . $stop . '])/i', $content);
    $content[] = preg_replace('/(.*?)<\/body>.*/isu', '$1', array_pop($content));
    $contentHead = array_shift($content);

    $temp_h = array();
    foreach ($content as $page) {
        $c[] = $page;
        preg_match('~<h([1-' . $stop . ']).*>(.*)</h~isU', $page, $temp);
        $l[] = $temp[1];
        $temp_h[] = trim(xh_rmws(strip_tags($temp[2])));
    }

    /*
     * just a helper for the "url" construction:
     * will be filled like this [0] => "Page"
     *                          [1] => "Subpage"
     *                          [2] => "Sub_Subpage" etc.
     */
    $ancestors = array();

    foreach ($temp_h as $i => $heading) {
        $temp = $heading;
        if ($temp == '') {
            $empty++;
            $temp = $tx['toc']['empty'] . ' ' . $empty;
        }
        $h[] = $temp;
        $ancestors[$l[$i] - 1] = XH_uenc($temp, $search, $replace);
        $ancestors = array_slice($ancestors, 0, $l[$i]);
        $url = implode($cf['uri']['seperator'], $ancestors);
        $u[] = substr($url, 0, $cf['uri']['length']);
        $tooLong[] = strlen($url) > $cf['uri']['length'];
    }

    $page_data_fields = $temp_data = array();
    if (preg_match('/<\?php(.*?)\?>/isu', $contentHead, $m)) {
        eval($m[1]);
    }
    $page_data = array();
    $hasPageData = false;
    foreach ($c as $i => $j) {
        if (preg_match('/<\?php(.*?)\?>/is', $j, $m)) {
            eval($m[1]);
            $c[$i] = preg_replace('/<\?php(.*?)\?>/is', '', $j);
            $hasPageData = true;
        } else {
            $page_data[] = array();
        }
    }

    if (empty($page_data_fields) && empty($temp_data) && !$hasPageData
        && is_readable($pageDataFile)
    ) {
        include $pageDataFile;
    }

    $pd_router = new XH_PageDataRouter(
        $h, $page_data_fields, $temp_data, $page_data
    );

    // remove unpublished pages
    if (!($edit && XH_ADM)) {
        foreach ($c as $i => $text) {
            if (cmscript('remove', $text)) {
                $c[$i] = '#CMSimple hide# #CMSimple shead(404);#';
            }
        }
    }

    return array(
        'urls' => $u,
        'too_long' => $tooLong,
        'headings' => $h,
        'levels' => $l,
        'pages' => $c,
        'pd_router' => $pd_router
    );
}

/**
 * Returns an opening a tag as link to a page.
 *
 * @param int    $i The page index.
 * @param string $x Arbitrary appendix of the URL.
 *
 * @global string The script name.
 * @global array  The URLs of the pages.
 * @global array  The configuration of the core.
 *
 * @return string The (X)HTML.
 */
function a($i, $x)
{
    global $sn, $u, $cf;

    if ($i == 0 && !XH_ADM) {
        if ($x == '' && $cf['locator']['show_homepage'] == 'true') {
            return '<a href="' . $sn . '?' . $u[0] . '">';
        }
    }
    return isset($u[$i])
        ? '<a href="' . $sn . '?' . $u[$i] . $x . '">'
        : '<a href="' . $sn . '?' . $x . '">';
}

/**
 * Returns the meta element for name, if defined in <var>$cf['meta']</var>;
 * <var>null</var> otherwise.
 *
 * @param string $n The name attribute.
 *
 * @global array The configuration of the core.
 * @global array The localization of the core.
 * @global bool  Whether print mode is active.
 *
 * @return string The (X)HTML.
 */
function meta($n)
{
    global $cf, $tx, $print;

    $exclude = array('robots', 'keywords', 'description');
    $value = isset($tx['meta'][$n]) ? $tx['meta'][$n] : $cf['meta'][$n];
    if ($n != 'codepage' && !empty($value) && !($print && in_array($n, $exclude))) {
        $content = XH_hsc($value);
        return tag('meta name="' . $n . '" content="' . $content . '"') . "\n";
    }
}

/**
 * Returns the link to a special CMSimple_XH page, e.g. sitemap.
 *
 * @param string $i A key of $tx['menu'].
 *
 * @global string The requested special function.
 * @global string The script name.
 * @global array  The localization of the core.
 *
 * @return string The (X)HTML.
 */
function ml($i)
{
    global $f, $sn, $tx;

    $t = '';
    if ($f != $i) {
        $t .= '<a href="' . $sn . '?&amp;' . $i . '">';
    }
    $t .= $tx['menu'][$i];
    if ($f != $i) {
        $t .= '</a>';
    }
    return $t;
}

/**
 * Returns a percent encoded URL component.
 *
 * Additionally all character sequences in $tx['urichar']['org'] will be replaced
 * by their according character sequences in $tx['urichar']['new'].
 *
 * @param string $s The URL component.
 *
 * @global array The localization of the core.
 *
 * @return string
 *
 * @see XH_uenc()
 */
function uenc($s)
{
    global $tx;

    if (isset($tx['urichar']['org']) && isset($tx['urichar']['new'])) {
        $search = explode(XH_URICHAR_SEPARATOR, $tx['urichar']['org']);
        $replace = explode(XH_URICHAR_SEPARATOR, $tx['urichar']['new']);
    } else {
        $search = $replace = array();
    }
    return XH_uenc($s, $search, $replace);
}

/**
 * Returns a percent encoded URL component.
 *
 * Additionally all character sequences in $search will be replaced
 * by their according character sequences in $replace.
 *
 * @param string $s       The URL component.
 * @param array  $search  Strings to search for.
 * @param array  $replace Replacement strings.
 *
 * @return string
 *
 * @see uenc()
 *
 * @since 1.6
 */
function XH_uenc($s, $search, $replace)
{
    $s = str_replace($search, $replace, $s);
    return str_replace('+', '_', urlencode($s));
}

/**
 * Returns the canonicalized absolute pathname on success.
 * Otherwise returns its input.
 *
 * @param string $p The file name.
 *
 * @return string
 *
 * @deprecated since 1.5.4. Use realpath() instead.
 */
function rp($p)
{
    trigger_error('Function rp() is deprecated', E_USER_DEPRECATED);

    if (realpath($p) == '') {
        return $p;
    } else {
        return realpath($p);
    }
}

/**
 * Returns the alphabetically sorted content of a directory.
 *
 * Caveat: the result includes '.' and '..'.
 *
 * @param string $dir The directory path.
 *
 * @return array
 */
function sortdir($dir)
{
    $fs = array();
    if ($fd = opendir($dir)) {
        while (false !== ($fn = readdir($fd))) {
            $fs[] = $fn;
        }
        closedir($fd);
    }
    sort($fs, SORT_STRING);
    return $fs;
}

/**
 * Returns the number of times a CMSimple script is found.
 *
 * @param string $script The needle.
 * @param string $text   The haystack.
 *
 * @global array The configuration of the core.
 *
 * @return int
 */
function cmscript($script, $text)
{
    global $cf;

    $pattern = str_replace('(.*?)', $script, '/#CMSimple (.*?)#/is');
    return preg_match($pattern, $text);
}

/**
 * Returns whether a page is hidden.
 *
 * @param int $i The page index.
 *
 * @global array The content of the pages.
 * @global bool  Whether edit mode is active.
 *
 * @return bool
 */
function hide($i)
{
    global $c, $edit;

    if ($i < 0) {
        return false;
    }
    return (!($edit && XH_ADM) && cmscript('hide', $c[$i]));
}

/**
 * Returns an (X)HTML compliant stand alone tag
 * according to the settings of $cf['xhtml']['endtags'].
 *
 * @param string $s The contents of the tag.
 *
 * @global array The configuration of the core.
 *
 * @return string The (X)HTML.
 */
function tag($s)
{
    global $cf;

    $t = $cf['xhtml']['endtags'] == 'true' ? ' /' : '';
    return '<' . $s . $t . '>';
}

/**
 * Returns '&' or '&amp;' according to the setting of $cf['xhtml']['amp'].
 *
 * @return string The (X)HTML.
 *
 * @global array The configuration of the core.
 *
 * @deprecated since 1.5.4. Use '&amp;' instead.
 */
function amp()
{
    global $cf;

    trigger_error('Function amp() is deprecated', E_USER_DEPRECATED);

    if ($cf['xhtml']['amp'] == 'true') {
        return '&amp;';
    } else {
        return '&';
    }
}

/**
 * Sends error header and sets $title and $o accordingly.
 *
 * @param int $s The HTTP status response code (401, 403, 404).
 *
 * @global bool   Whether the server is IIS.
 * @global bool   Whether the API is CGI.
 * @global array  The localization of the core.
 * @global string The page title.
 * @global string The (X)HTML of the contents area.
 *
 * @return void.
 */
function shead($s)
{
    global $iis, $cgi, $tx, $title, $o;

    if ($s == '401') {
        header(
            ($cgi || $iis) ? 'status: 401 Unauthorized' : 'HTTP/1.0 401 Unauthorized'
        );
    } elseif ($s == '403') {
        header(($cgi || $iis) ? 'status: 403 Forbidden' : 'HTTP/1.0 403 Forbidden');
    } elseif ($s == '404') {
        if (function_exists('custom_404')) {
            custom_404();
        } else {
            header(
                ($cgi || $iis) ? 'status: 404 Not Found' : 'HTTP/1.0 404 Not Found'
            );
        }
    }
    if ($title == '') {
        $title = $tx['error'][$s];
    }
    $o = '<h1>' . $title . '</h1>' . $o;
}

/**
 * Debug-Mode
 *
 * Check if file "_XHdebug.txt" exists to turn on debug-mode
 * with default debug level 1.
 * The level of the debug mode can be adjusted by placing an
 * integer-value within the file using following values:
 * - 0: Turn off all error reporting
 * - 1: Runtime errors except warnings
 * - 2: Runtime errors
 * - 3: Runtime errors + notices
 * - 4: All errors except notices and warnings
 * - 5: All errors except notices
 * - 6: All errors
 *
 * @global array The paths of system files and folders.
 *
 * @return boolean Whether error_reporting is enabled.
 *
 * @author Holger
 * @since 1.0rc3
 */
function XH_debugmode()
{
    global $pth;

    $dbglevel = '';
    $filename = $pth['folder']['downloads'] . '_XHdebug.txt';
    if (file_exists($filename)) {
        ini_set('display_errors', 1);
        $dbglevel = file_get_contents($filename);
        if (strlen($dbglevel) == 1) {
            set_error_handler('XH_debug');
            switch ($dbglevel) {
            case 0:
                error_reporting(0);
                break;
            case 1:
                error_reporting(E_ERROR | E_USER_WARNING | E_PARSE);
                break;
            case 2:
                error_reporting(E_ERROR | E_WARNING | E_USER_WARNING | E_PARSE);
                break;
            case 3:
                error_reporting(
                    E_ERROR | E_WARNING | E_USER_WARNING | E_PARSE | E_NOTICE
                );
                break;
            case 4:
                error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_USER_WARNING));
                break;
            case 5:
                error_reporting(E_ALL ^ E_NOTICE);
                break;
            case 6:
                error_reporting(E_ALL);
                break;
            default:
                error_reporting(E_ERROR | E_USER_WARNING | E_PARSE);
            }
        } else {
            error_reporting(E_ERROR | E_USER_WARNING | E_PARSE);
        }
    } else {
        ini_set('display_errors', 0);
        error_reporting(0);
    }
    return error_reporting() > 0;
}

/**
 * Writes all recoverable PHP errors to $e.
 *
 * @param int    $errno   Level of the error.
 * @param string $errstr  An error message.
 * @param string $errfile Filename where error was raised.
 * @param int    $errline Line number where error was raised.
 * @param array  $context The error context.
 *
 * @global array The list of PHP errors formatted as (X)HTML fragment.
 *
 * @return void
 */
function XH_debug($errno, $errstr, $errfile, $errline, $context)
{
    global $errors;

    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        $errtype = 'XH-ERROR';
        break;
    case E_USER_WARNING:
        $errtype = 'XH-WARNING';
        break;
    case E_USER_NOTICE:
        $errtype = 'XH-NOTICE';
        break;
    case E_USER_DEPRECATED:
        $errtype = 'XH-DEPRECATED';
        $backtrace = debug_backtrace(false);
        $errfile = $backtrace[2]['file'];
        $errline = $backtrace[2]['line'];
        break;
    case E_WARNING:
        $errtype = 'WARNING';
        break;
    case E_NOTICE:
        $errtype = 'NOTICE';
        break;
    case E_STRICT:
        $errtype = 'STRICT';
        break;
    case E_DEPRECATED:
        $errtype = 'DEPRECATED';
        break;
    default:
        $errtype = "Unknow error type [$errno]";
    }

    $errors[] = "<b>$errtype:</b> $errstr" . tag('br') . "$errfile:$errline"
        . tag('br') . "\n";

    if ($errno === E_USER_ERROR) {
        die($errors[count($errors) - 1]);
    }

    /* Don't execute PHP internal error handler */
    return true;
}

/**
 * Checks <var>$arr</var> recursively for valid UTF-8.
 * Otherwise it exists the script.
 *
 * Useful for checking user input.
 *
 * @param array $arr Array to check.
 *
 * @return  void
 *
 * @since 1.5.5
 */
function XH_checkValidUtf8($arr)
{
    foreach ($arr as $elt) {
        if (is_array($elt)) {
            XH_checkValidUtf8($elt);
        } elseif (!utf8_is_valid($elt)) {
            header('HTTP/1.0 400 Bad Request');
            exit('Malformed UTF-8 detected!');
        }
    }
}

/**
 * Copies default file, if actual language file is missing. Returns whether
 * the language file exists afterwards.
 *
 * @param string $dst The destination filename.
 *
 * @return bool
 *
 * @since 1.6
 */
function XH_createLanguageFile($dst)
{
    $config = preg_match('/config.php$/', $dst) ? 'config' : '';
    if (!file_exists($dst)) {
        if (is_readable($src = dirname($dst) . "/default$config.php")) {
            return copy($src, $dst);
        } elseif ($src = is_readable(dirname($dst) . "/en$config.php")) {
            return copy($src, $dst);
        }
    }
    return true;
}

/**
 * Set plugin paths.
 *
 * @param string $plugin The name of the plugin.
 *
 * @global array  The configuration of the core.
 * @global array  The paths of system files and folders.
 * @global string The active language.
 *
 * @return void
 *
 * @staticvar array The help filename cache.
 */
function pluginFiles($plugin)
{
    global $cf, $pth, $sl;
    static $helpFiles = array();

    $folders = array(
        'plugin' => '/',
        'plugin_classes' => '/classes/',
        'plugin_config' => '/config/',
        'plugin_content' => '/content/',
        'plugin_css' => '/css/',
        'plugin_help' => '/help/',
        'plugin_includes' => '/includes/',
        'plugin_languages' => '/languages/'
    );
    foreach ($folders as $key => $folder) {
        $pth['folder'][$key] = $pth['folder']['plugins'] . $plugin . $folder;
    }

    $pth['file']['plugin_index'] = $pth['folder']['plugin'] . 'index.php';
    $pth['file']['plugin_admin'] = $pth['folder']['plugin'] . 'admin.php';

    $pth['file']['plugin_language'] = $pth['folder']['plugin_languages']
        . strtolower($sl) . '.php';

    $pth['file']['plugin_classes'] = $pth['folder']['plugin_classes']
        . 'required_classes.php';
    $pth['file']['plugin_config'] = $pth['folder']['plugin_config']
        . 'config.php';
    $pth['file']['plugin_stylesheet'] = $pth['folder']['plugin_css']
        . 'stylesheet.css';

    if (!isset($helpFiles[$plugin])) {
        $helpFiles[$plugin] = $pth['folder']['plugin_help']
            . 'help_' . strtolower($sl) . '.htm';
        if (!file_exists($helpFiles[$plugin])) {
            $helpFiles[$plugin] = $pth['folder']['plugin_help'] . 'help_en.htm';
        }
        if (!file_exists($helpFiles[$plugin])
            && file_exists($pth['folder']['plugin_help'] . 'help.htm')
        ) {
            $helpFiles[$plugin] = $pth['folder']['plugin_help'] . 'help.htm';
        }
    }
    $pth['file']['plugin_help'] = $helpFiles[$plugin];
}

/**
 * Function preCallPlugins() => Pre-Call of Plugins.
 *
 * All Plugins which are called through a function-call
 * can use this. At the moment it is'nt possible to do
 * this with class-based plugins. They need to be called
 * through standard-CMSimple-Scripting.
 *
 * Call a plugin: place this in your code (example):
 * {{{PLUGIN:pluginfunction('parameters');}}}
 *
 * Call a built-in function (at the moment only one for
 * demonstration):
 * {{{HOME}}} or: {{{HOME:name_of_Link}}}
 * This creates a link to the first page of your CMSimple-
 * Installation.
 *
 * @param int $pageIndex The page index.
 *
 * @global bool  Whether edit-mode is active.
 * @global array The contents of all pages.
 * @global int   The Index of the active page.
 * @global array The URLs of all pages.
 *
 * @return void
 *
 * @author mvwd
 *
 * @since 1.0
 * @deprecated since 1.6
 */
function preCallPlugins($pageIndex = -1)
{
    global $edit, $c, $s, $u;

    trigger_error('Function preCallPlugins() is deprecated', E_USER_DEPRECATED);

    if (!$edit) {
        if ((int) $pageIndex > - 1 && (int) $pageIndex < count($u)) {
            $as = $pageIndex;
        } else {
            $as = $s < 0 ? 0 : $s;
        }
        $c[$as] = evaluate_plugincall($c[$as]);
    }
}

/**
 * Returns a list of all installed plugins.
 *
 * @param bool $admin Whether to return only plugins with a admin.php
 *
 * @return  array
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the core.
 *
 * @staticvar array The plugin name cache.
 * @staticvar array The admin plugin name cache.
 *
 * @since 1.6
 *
 * @todo Might be optimized to set $admPlugins only when necessary.
 */
function XH_plugins($admin = false)
{
    global $pth, $cf;
    static $plugins = null;
    static $admPlugins = null;

    if (!isset($plugins)) {
        $plugins = array();
        $admPlugins = array();
        $disabledPlugins = explode(',', $cf['plugins']['disabled']);
        $disabledPlugins = array_map('trim', $disabledPlugins);
        if ($dh = opendir($pth['folder']['plugins'])) {
            while (($fn = readdir($dh)) !== false) {
                if (strpos($fn, '.') !== 0
                    && is_dir($pth['folder']['plugins'] . $fn)
                    && !in_array($fn, $disabledPlugins)
                ) {
                    $plugins[] = $fn;
                    pluginFiles($fn);
                    if (is_file($pth['file']['plugin_admin'])) {
                        $admPlugins[] = $fn;
                    }
                }
            }
            closedir($dh);
        }
        natcasesort($plugins);
        natcasesort($admPlugins);
    }
    return $admin ? $admPlugins : $plugins;
}

/**
 * Returns the value of a cookie, or <var>null</var> if the cookie doesn't exist.
 *
 * Has fallback to $HTTP_COOKIE_VARS for PHP < 4.1.0. ;-)
 *
 * @param string $s The name of the cookie.
 *
 * @return string
 */
function gc($s)
{
    if (!isset($_COOKIE)) {
        global $_COOKIE;
        $_COOKIE = $GLOBALS['HTTP_COOKIE_VARS'];
    }
    if (isset($_COOKIE[$s])) {
        return $_COOKIE[$s];
    }
}

/**
 * Returns wether the user is logged in.
 *
 * @global array The configuration of the core.
 *
 * @return bool.
 */
function logincheck()
{
    global $cf;

    if (session_id() == '') {
        session_start();
    }
    return isset($_SESSION['xh_password'])
        && isset($_SESSION['xh_password'][CMSIMPLE_ROOT])
        && $_SESSION['xh_password'][CMSIMPLE_ROOT] == $cf['security']['password']
        && isset($_SESSION['xh_user_agent'])
        && $_SESSION['xh_user_agent'] == md5($_SERVER['HTTP_USER_AGENT']);
}

/**
 * Appends a message to the logfile.
 *
 * On failure an according message is appended to $e.
 *
 * @param string $m The log message.
 *
 * @return void
 *
 * @global array  The paths of system files and folders.
 * @global string Error messages as (X)HTML fragment consisting of LI Elements.
 *
 * @deprecated since 1.6
 */
function writelog($m)
{
    global $pth, $e;

    trigger_error(
        'Function ' . __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED
    );
    if ($fh = fopen($pth['file']['log'], "a")) {
        fwrite($fh, $m);
        fclose($fh);
    } else {
        e('cntwriteto', 'log', $pth['file']['log']);
    }
}

/**
 * Appends a message to the log file, and returns whether that succeeded.
 *
 * @param string $type        A message type ("info", "warning", "error").
 * @param string $module      A module name ("XH" or plugin name).
 * @param string $category    A category.
 * @param string $description A description.
 *
 * @return bool
 *
 * @global array The paths of system files and folders.
 *
 * @since 1.6
 */
function XH_logMessage($type, $module, $category, $description)
{
    global $pth;

    $timestamp = date('Y-m-d H:i:s');
    $message = "$timestamp\t$type\t$module\t$category\t$description";
    $ok = false;
    $stream = fopen($pth['file']['log'], 'a');
    if ($stream) {
        if (flock($stream, LOCK_EX)) {
            $ok = fwrite($stream, $message . PHP_EOL) !== false;
            fflush($stream);
            flock($stream, LOCK_UN);
        }
        fclose($stream);
    }
    return $ok;
}

/**
 * Returns the login link.
 *
 * @global int    The index of the requested page.
 * @global array  The localization of the core.
 *
 * @return string The (X)HTML.
 */
function lilink()
{
    global $s, $tx;

    if (!XH_ADM) {
        return a($s > -1 ? $s : 0, '&amp;login') . $tx['menu']['login'] . '</a>';
    }
}

/**
 * Returns the login form.
 *
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global string JavaScript for the onload event of the BODY element.
 * @global string The requested special function.
 * @global string The (X)HTML of the contents area.
 * @global int    The index of the requested page.
 * @global string The script name.
 * @global array  The URLs of the pages.
 *
 * @return string The (X)HTML.
 */
function loginforms()
{
    global $cf, $tx, $onload, $f, $o, $s, $sn, $u;

    if ($f == 'login' || $f == 'xh_login_failed') {
        $cf['meta']['robots'] = "noindex";
        $onload .= 'document.forms[\'login\'].elements[\'keycut\'].focus();';
        $message = ($f == 'xh_login_failed')
            ? XH_message('fail', $tx['login']['failure'])
            : '';
        $f = $tx['menu']['login'];
        $o .= '<div class="xh_login">'
            . '<h1>' . $tx['menu']['login'] . '</h1>'
            . $message
            . '<p><b>' . $tx['login']['warning'] . '</b></p>'
            . '<form id="login" name="login" action="' . $sn . '?' . $u[$s]
            . '" method="post">'
            . tag('input type="hidden" name="login" value="true"')
            . tag('input type="hidden" name="selected" value="' . $u[$s] . '"')
            . tag('input type="password" name="keycut" id="passwd" value=""')
            . ' '
            . tag(
                'input type="submit" name="submit" id="submit" value="'
                . $tx['menu']['login'] . '"'
            )
            . '</form>';
        if (!empty($cf['security']['email'])) {
            $o .= '<a href="' . $sn . '?&function=forgotten">'
                . $tx['title']['password_forgotten'] . '</a>';
        }
        $o .= ' </div>';
        $s = -1;
    }
}

/**
 * Returns the remaining contents of a stream.
 *
 * @param resource $stream An open stream.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_getStreamContents($stream)
{
    $func = 'stream_get_contents';
    if (function_exists($func)) {
        $contents = $func($stream);
    } else {
        ob_start();
        fpassthru($stream);
        $contents = ob_get_clean();
    }
    return $contents;
}

/**
 * Reads a file and returns its contents; <var>false</var> on failure.
 * During reading, the file is locked for shared access.
 *
 * @param string $filename A file path.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_readFile($filename)
{
    $contents = false;
    $stream = fopen($filename, 'rb');
    if ($stream) {
        if (flock($stream, LOCK_SH)) {
            $contents = XH_getStreamContents($stream);
            flock($stream, LOCK_UN);
        }
        fclose($stream);
    }
    return $contents;
}

/**
 * Writes <var>$contents</var> to the file <var>$filename</var>.
 * During writing the file is locked exclusively.
 *
 * @param string $filename The filename.
 * @param string $contents The content to write.
 *
 * @return int The number of bytes written, or false on failure.
 *
 * @since 1.6
 */
function XH_writeFile($filename, $contents)
{
    $res = false;
    // we can't use "cb" as it is available only since PHP 5.2.6
    // we can't use "r+b" as it will fail if the file does not already exist
    $stream = fopen($filename, 'a+b');
    if ($stream) {
        if (flock($stream, LOCK_EX)) {
            fseek($stream, 0);
            ftruncate($stream, 0);
            $res = fwrite($stream, $contents);
            fflush($stream);
            flock($stream, LOCK_UN);
        }
        fclose($stream);
    }
    return $res;
}

/**
 * Registers a callback for execution after all plugins were loaded,
 * if <var>$callback</var> is given; otherwise executes these callbacks.
 *
 * @param callable $callback The callback.
 *
 * @return void
 *
 * @staticvar array The callbacks for later execution.
 *
 * @since 1.6
 */
function XH_afterPluginLoading($callback = null)
{
    static $callbacks = array();

    if (isset($callback)) {
        $callbacks[] = $callback;
    } else {
        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }
}

/**
 * Returns the path of the combined plugin stylesheet.
 * If necessary, this stylesheet will be created/updated.
 *
 * @return string
 *
 * @global array  The paths of system files and folders.
 *
 * @since 1.6
 */
function XH_pluginStylesheet()
{
    global $pth;

    $plugins = XH_plugins();

    $ofn = $pth['folder']['corestyle'] . 'plugins.css';
    $expired = !file_exists($ofn);

    // check for newly (un)installed plugins
    if (!$expired) {
        if (($ofp = fopen($ofn, 'r')) !== false
            && fgets($ofp, 4096) && fgets($ofp, 4096)
            && ($oldPlugins = fgets($ofp, 4096))
        ) {
            $oldPlugins = explode(',', trim($oldPlugins, " *\r\n"));
            $expired = $plugins != $oldPlugins;
        } else {
            $expired = true;
        }
        if ($ofp !== false) {
            fclose($ofp);
        }
    }

    // check for changes in the individual plugin stylesheets
    if (!$expired) {
        foreach ($plugins as $plugin) {
            $fn = $pth['folder']['plugins'] . $plugin . '/css/stylesheet.css';
            if (file_exists($fn) && filemtime($fn) > filemtime($ofn)) {
                $expired = true;
                break;
            }
        }
    }

    // create combined plugin stylesheet
    if ($expired) {
        $o = array();
        foreach ($plugins as $plugin) {
            $fn = $pth['folder']['plugins'] . $plugin . '/css/stylesheet.css';
            if (file_exists($fn)) {
                $css = file_get_contents($fn);
                if (substr($css, 0, 3) === "\xEF\xBB\xBF") {
                    $css = substr($css, 3);
                }
                $css = XH_adjustStylesheetURLs($plugin, $css);
                $css = PHP_EOL
                    . '/' . str_pad(' ' . $fn, 76, '*', STR_PAD_LEFT) . ' */'
                    . PHP_EOL . PHP_EOL . $css;
                $o[] = $css;
            }
        }
        $o = '/*' . PHP_EOL
            . ' * Automatically created by ' . CMSIMPLE_XH_VERSION
            . '. DO NOT MODIFY!' . PHP_EOL
            . ' * ' . implode(',', $plugins) . PHP_EOL
            . ' */' . PHP_EOL . PHP_EOL
            . implode(PHP_EOL . PHP_EOL, $o);
        if (!XH_writeFile($ofn, $o)) {
            e('cntwriteto', 'stylesheet', $ofn);
        }
    }

    return $ofn;
}

/**
 * Adjusts all relative url(...) in a stylesheet to be used
 * in the combined plugin stylesheet.
 *
 * @param string $plugin The name of the plugin.
 * @param string $css    The content of the stylesheet.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_adjustStylesheetURLs($plugin, $css)
{
    return preg_replace(
        '/url\(\s*(["\']?)(?!\s*["\']?\/|\s*["\']?http[s]?:)(.*?)(["\']?)\s*\)/s',
        "url(\$1../plugins/$plugin/css/\$2\$3)", $css
    );
}

/**
 * Returns an (X)HTML element formatted as message.
 *
 * @param string $type    The type of message ('success', 'info', 'warning', 'fail').
 * @param string $message A message format to print in an printf() style.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_message($type, $message)
{
    $class = 'xh_' . $type;
    $args = array_slice(func_get_args(), 2);
    $message = vsprintf($message, $args);
    $message = XH_hsc($message);
    return '<p class="' . $class . '">' . $message . '</p>';
}

/**
 * Creates a backup of the contents file. Surplus old backups will be deleted.
 * Returns an appropriate message.
 *
 * @return string The (X)HTML.
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the core.
 * @global array The localization of the core.
 *
 * @since 1.6
 */
function XH_backup()
{
    global $pth, $cf, $tx;

    $o = '';
    $date = date("Ymd_His");
    $fn = $date . '_content.htm';
    if (empty($cf['backup']['numberoffiles'])
        || copy($pth['file']['content'], $pth['folder']['content'] . $fn)
    ) {
        if (!empty($cf['backup']['numberoffiles'])) {
            $message = utf8_ucfirst($tx['filetype']['backup']) . ' ' . $fn
                . ' ' . $tx['result']['created'];
            $o .= XH_message('info', $message);
        }
        $fl = array();
        if ($fd = opendir($pth['folder']['content'])) {
            while (($p = readdir($fd)) == true) {
                if (XH_isContentBackup($p)) {
                    $fl[] = $p;
                }
            }
            closedir($fd);
        }
        sort($fl);
        $v = count($fl) - $cf['backup']['numberoffiles'];
        for ($i = 0; $i < $v; $i++) {
            if (unlink($pth['folder']['content'] . $fl[$i])) {
                $message = utf8_ucfirst($tx['filetype']['backup'])
                    . ' ' . $fl[$i] . ' ' . $tx['result']['deleted'];
                $o .= XH_message('info', $message);
            } else {
                e('cntdelete', 'backup', $fl[$i]);
            }
        }
    } else {
        e('cntsave', 'backup', $fn);
    }
    return $o;
}

/**
 * Returns whether <var>$name</var> is a language folder.
 *
 * @param string $name The name to check.
 *
 * @return bool
 *
 * @global array The paths of system files and folders.
 *
 * @since 1.6
 */
function XH_isLanguageFolder($name)
{
    global $pth;

    $path = $pth['folder']['base'] . $name;
    return is_dir($path) && preg_match('/^[A-z]{2}$/', $name)
        && file_exists($path . '/.2lang');
}

/**
 * Returns the text content for a TITLE element.
 *
 * @param string $site     A site name.
 * @param string $subtitle A subtitle (e.g. the page heading).
 *
 * @return string
 *
 * @since 1.6
 */
function XH_title($site, $subtitle)
{
    global $cf;

    if ($site != '') {
        $site = XH_hsc($site);
        $replacePairs = array('{SITE}' => $site, '{PAGE}' => $subtitle);
        $title = strtr($cf['title']['format'], $replacePairs);
    } else {
        $title = $subtitle;
    }
    return $title;
}

/**
 * A minimal built-in template for some special functions.
 * Currently used for the print view and the login screen.
 *
 * @param string $bodyClass The CSS class of the BODY element.
 *
 * @return string (X)HTML.
 *
 * @since 1.6
 */
function XH_builtinTemplate($bodyClass)
{
    global $_XH_csrfProtection;

    if ($cf['xhtml']['endtags'] == 'true') {
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"',
            ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">', "\n",
            '<html xmlns="http://www.w3.org/1999/xhtml"',
            (strlen($sl) == 2 ? " lang=\"$sl\" xml:lang=\"$sl\"" : ''), '>', "\n";
    } else {
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"',
            ' "http://www.w3.org/TR/html4/loose.dtd">', "\n", '<html',
            (strlen($sl) == 2 ? " lang=\"$sl\"" : ''), '>', "\n";
    }
    $content = XH_convertPrintUrls(content());
    echo '<head>', "\n" . head(),
        tag('meta name="robots" content="noindex"'), "\n",
        '</head>', "\n", '<body class="', $bodyClass,'"', onload(), '>', "\n",
        $content, '</body>', "\n", '</html>', "\n";
    $_XH_csrfProtection->store();
    exit;
}

/**
 * Returns a help icon which displays a tooltip on hover.
 *
 * @param string $tooltip A tooltip in (X)HTML.
 *
 * @return string (X)HTML.
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 *
 * @since 1.6
 *
 * @todo Change the DIVs to SPANs and require the <var>$tooltip</var> to be an
 *       inline fragment (requires block level elements to be removed from all
 *       help texts--even $plugin_tx).
 */
function XH_helpIcon($tooltip)
{
    global $pth, $tx;

    $src = $pth['folder']['corestyle'] . 'help_icon.png';
    $o = '<div class="pl_tooltip">'
        . tag('img src="' . $src . '" alt="' . $tx['editmenu']['help'] . '"')
        . '<div>' . $tooltip . '</div>'
        . '</div>';
    return $o;
}

/**
 * Returns whether a file is a content backup by checking the filename.
 *
 * @param string $filename    A filename.
 * @param string $regularOnly Whether to check for regalur backup names only.
 *
 * @return bool
 *
 * @since 1.6
 */
function XH_isContentBackup($filename, $regularOnly = true)
{
    $suffix = $regularOnly ? 'content' : '[^.]+';
    return preg_match('/^\d{8}_\d{6}_' . $suffix . '.htm$/', $filename);
}

/**
 * Returns an array of installed templates.
 *
 * @return array
 *
 * @global array The paths of system files and folders.
 *
 * @since 1.6
 */
function XH_templates()
{
    global $pth;

    $templates = array();
    if ($handle = opendir($pth['folder']['templates'])) {
        while (($file = readdir($handle)) !== false) {
            $dir = $pth['folder']['templates'] . $file;
            if ($file[0] != '.' && is_dir($dir)
                && file_exists($dir . '/template.htm')
            ) {
                $templates[] = $file;
            }
        }
        closedir($handle);
    }
    natcasesort($templates);
    return $templates;
}

/**
 * Returns an array of available languages (in cmsimple/languages/).
 *
 * @return array
 *
 * @global array The paths of system files and folders.
 *
 * @since 1.6
 */
function XH_availableLocalizations()
{
    global $pth;

    $languages = array();
    if ($handle = opendir($pth['folder']['language'])) {
        while (($file = readdir($handle)) !== false) {
            if (preg_match('/^([a-z]{2})\.php$/i', $file, $m)) {
                $languages[] = $m[1];
            }
        }
        closedir($handle);
    }
    natcasesort($languages);
    return $languages;
}

/**
 * Returns the installed second languages in alphabetic order.
 *
 * @return array
 *
 * @global array The paths of system files and folders.
 *
 * @staticvar array The language names cache.
 *
 * @since 1.6
 */
function XH_secondLanguages()
{
    global $pth;
    static $langs;

    if (!isset($langs)) {
        $langs = array();
        if ($dir = opendir($pth['folder']['base'])) {
            while (($entry = readdir($dir)) !== false) {
                if (XH_isLanguageFolder($entry)) {
                    $langs[] = $entry;
                }
            }
            closedir($dir);
        }
        sort($langs);
    }
    return $langs;
}

/**
 * Returns whether a path refers to a CMSimple index.php.
 *
 * @param string $path A relative path.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_isInternalPath($path)
{
    global $sl, $cf;

    $parts = explode('/', $path);
    $part0 = '';
    if ($parts[0] === '.'
        || $parts[0] === '..' && $sl !== $cf['language']['default']
    ) {
        $part0 = array_shift($parts);
    }
    if (empty($parts)) {
        return true;
    }
    if (($sl === $cf['language']['default'] || $part0 === '..')
        && array_search($parts[0], XH_secondLanguages())
    ) {
        array_shift($parts);
    }
    if (empty($parts)) {
        return true;
    }
    if ($parts[0] === '' || $parts[0] === 'index.php') {
        array_shift($parts);
    }
    return empty($parts);
}

/**
 * Returns whether a URL points to this CMSimple installation.
 *
 * @param string $urlParts Parts of an URL.
 *
 * @return bool
 *
 * @since 1.6
 */
function XH_isInternalUrl($urlParts)
{
    $ok = !isset(
        $urlParts['scheme'], $urlParts['host'], $urlParts['port'],
        $urlParts['user'], $urlParts['pass']
    );
    $ok = $ok
        && (!isset($urlParts['path']) || XH_isInternalPath($urlParts['path']));
    return $ok;
}

/**
 * Returns a single URL converted to a print URL, if appropriate.
 * Serves as helper for @see XH_convertPrintUrls().
 *
 * @param array $matches The matches of a PREG.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_convertToPrintUrl($matches)
{
    $url = $matches[3];
    $parts = parse_url($url);
    if (XH_isInternalUrl($parts)) {
        $parts['query'] = (isset($parts['query']) ? $parts['query'] . '&amp;' : '');
        $parts['query'] .= 'print';
        $url = isset($parts['path']) ? $parts['path'] : '';
        $url .= '?' . $parts['query'];
        if (isset($parts['fragment'])) {
            $url .= '#' . $parts['fragment'];
        }
    }
    return $matches[1] . $url . $matches[2];
}

/**
 * Convert all internal URLs in a text to print URLs.
 *
 * @param string $pageContent Some (X)HTML.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_convertPrintUrls($pageContent)
{
    $regex = '/(<a[^>]+href=(["\']))([^"\']*)\\2/iu';
    $content = preg_replace_callback($regex, 'XH_convertToPrintUrl', $pageContent);
    return $content;
}

/**
 * Returns the JSON string decoded as PHP value.
 *
 * @param string $string A JSON string.
 *
 * @return mixed
 *
 * @global array  The paths of system files and folders.
 * @global object The JSON codec.
 *
 * @since 1.6
 */
function XH_decodeJson($string)
{
    global $pth, $_XH_json;

    $func = 'json_decode';
    if (function_exists($func)) {
        return $func($string); // indirect call to satisfy PHP_CI
    } else {
        if (!isset($_XH_json)) {
            include_once $pth['folder']['classes'] . 'JSON.php';
            $_XH_json = new XH_JSON();
        }
        return $_XH_json->decode($string);
    }
}

/**
 * Returns the JSON representation of a value.
 *
 * @param mixed $value A PHP value.
 *
 * @return string
 *
 * @global array The paths of system files and folders.
 * @global object The JSON codec.
 *
 * @since 1.6
 */
function XH_encodeJson($value)
{
    global $pth, $_XH_json;

    $func = 'json_encode';
    if (function_exists($func)) {
        return $func($value); // indirect call to satisfy PHP_CI
    } else {
        if (!isset($_XH_json)) {
            include_once $pth['folder']['classes'] . 'JSON.php';
            $_XH_json = new XH_JSON();
        }
        return $_XH_json->encode($value);
    }
}

/**
 * Returns whether an error has occurred
 * during the last {@link XH_decodeJSON()}.
 *
 * @return bool
 *
 * @global array The paths of system files and folders.
 * @global object The JSON codec.
 *
 * @since 1.6
 */
function XH_lastJsonError()
{
    global $pth, $_XH_json;

    if (function_exists('json_last_error')) {
        return json_last_error();
    } else {
        if (!isset($_XH_json)) {
            include_once $pth['folder']['classes'] . 'JSON.php';
            $_XH_json = new XH_JSON();
        }
        return $_XH_json->lastError();
    }
}

/**
 * Converts special characters to HTML entities.
 *
 * Same as htmlspecialchars($string, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8'),
 * but works for PHP < 5.4 as well.
 *
 * @param string $string A string.
 *
 * @return string
 *
 * @since 1.5.8
 */
function XH_hsc($string)
{
    if (!defined('ENT_SUBSTITUTE')) {
        include_once UTF8 . '/utils/bad.php';
        $string = utf8_bad_replace($string, "\xEF\xBF\xBD");
        $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    } else {
        $string = htmlspecialchars($string, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
    }
    return $string;
}

/**
 * Handles a mailform embedded in a CMSimple_XH page.
 *
 * @return string (X)HTML.
 *
 * @global array The paths of system files and folders.
 *
 * @since 1.6
 */
function XH_mailform()
{
    global $pth;

    include_once $pth['folder']['classes'] . 'Mailform.php';

    $mailform = new XH_Mailform(true);
    return $mailform->process();
}

/**
 * Includes a PHP data file and returns the value of the variable.
 * Returns <var>false</var>, if including failed.
 * During the inclusion, the file is locked for shared access.
 *
 * @param string $_filename A filename.
 * @param string $_varname  A variable name.
 *
 * @return mixed
 *
 * @since 1.6
 */
function XH_includeVar($_filename, $_varname)
{
    $_res = false;
    $_stream = fopen($_filename, 'r');
    if ($_stream) {
        if (flock($_stream, LOCK_SH)) {
            $_res = include $_filename;
            flock($_stream, LOCK_UN);
        }
        fclose($_stream);
    }
    if (!isset($$_varname)) {
        $$_varname = array();
    }
    return $_res !== false ? $$_varname : false;
}

/**
 * Returns a suffix for a language string key according to the number
 * (singular, paucal or plural).
 *
 * @param int $count Count of the items.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_numberSuffix($count)
{
    if ($count == 1) {
        $suffix = '_1';
    } elseif ($count >= 2 && $count <= 4) {
        $suffix = '_2_4';
    } else {
        $suffix = '_5';
    }
    return $suffix;
}

/**
 * Returns the configuration resp. language array of the core resp. a plugin.
 *
 * For plugins pluginFiles() has to be called before.
 *
 * @param bool $plugin   Whether to return plugin information (opposed to core).
 * @param bool $language Whether to return the language array (opposed to config).
 *
 * @return array
 *
 * @global array The paths of system files and folders.
 *
 * @since 1.6
 */
function XH_readConfiguration($plugin = false, $language = false)
{
    global $pth;

    if (!$plugin) {
        if (!$language) {
            $varname = 'cf';
            $defaultFilename = $pth['folder']['cmsimple'] . 'defaultconfig.php';
            $filename = $pth['file']['config'];
        } else {
            $varname = 'tx';
            $defaultFilename = $pth['folder']['language'] . 'default.php';
            $filename = $pth['file']['language'];
        }
    } else {
        if (!$language) {
            $varname = 'plugin_cf';
            $defaultFilename = $pth['folder']['plugin_config'] . 'defaultconfig.php';
            $filename = $pth['file']['plugin_config'];
        } else {
            $varname = 'plugin_tx';
            $defaultFilename = $pth['folder']['plugin_languages'] . 'default.php';
            $filename = $pth['file']['plugin_language'];
        }
    }
    if (is_readable($defaultFilename)) {
        include $defaultFilename;
    } else {
        $$varname = array();
    }
    if (is_readable($filename)) {
        $$varname = XH_unionOf2DArrays(
            (array) XH_includeVar($filename, $varname),
            (array) $$varname
        );
    }
    return $$varname;
}

/**
 * Returns the union of two "2-dimensional" arrays in the same manner as the
 * union operator (i.e. keys and subkeys in the first array have higher
 * priority).
 *
 * @param array $array1 A "2-dimensional" array.
 * @param array $array2 A "2-dimensional" array.
 *
 * @return array
 *
 * @since 1.6
 */
function XH_unionOf2DArrays($array1, $array2)
{
    foreach ($array1 as $key => $subarray1) {
        $subarray2 = isset($array2[$key]) ? $array2[$key] : array();
        $array2[$key] = $subarray1 + $subarray2;
    }
    return $array2;
}

/**
 * Attempts to rename oldname to newname, and returns whether that succeeded.
 *
 * The file is moved between directories if necessary. If newname exists, it
 * will be overwritten.
 *
 * This is a wrapper around {@link rename rename()}, which offers a fallback for
 * the limitation of PHP < 5.3 on Windows that the rename operation fails, if
 * <var>$newfile</var> already exists. Note, that the fallback solution is not
 * atomic.
 *
 * @param string $oldname A filename.
 * @param string $newname A filename.
 *
 * @return bool
 *
 * @since 1.6
 */
function XH_renameFile($oldname, $newname)
{
    if (strtoupper(substr(php_uname(), 0, 3)) == 'WIN'
        && file_exists($newname)
    ) {
        unlink($newname);
    }
    return rename($oldname, $newname);
}

?>
