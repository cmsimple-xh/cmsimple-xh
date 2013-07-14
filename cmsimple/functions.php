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
  $CMSIMPLE_XH_VERSION$
  $CMSIMPLE_XH_DATE$
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
    if ($fh = @fopen(preg_replace("/\&amp;/is", "&", $u), "r")) {
        while (!feof($fh)) {
            $t .= fread($fh, 1024);
        }
        fclose($fh);
        return preg_replace("/.*<body[^>]*>(.*)<\/body>.*/is", "\\1", $t);
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
    if ($fh = @fopen($u . '?' . $qs, "r")) {
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
        "/.*<!-- autogallery -->(.*)<!-- \/autogallery -->.*/is", "\\1",
        preg_replace(
            "/(option value=\"\?)(p=)/is", "\\1" . $su . "&\\2",
            preg_replace(
                "/(href=\"\?)/is", "\\1" . $su . '&amp;',
                preg_replace("/(src=\")(\.)/is", "\\1" . $u . "\\2", geturlwp($u))
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

    $message = '<span class="cmsimplecore_fail">' . $tx['error']['plugincall']
        . '</span>';
    $re = '/{{{(?:[^:]+:)?(([a-z_0-9]+)\([^\)]*\);)}}}/iu';
    preg_match_all($re, $text, $calls, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $calls = array_reverse($calls);
    foreach ($calls as $call) {
        $length = strlen($call[0][0]);
        $offset = $call[0][1];
        $expression = $call[1][0];
        $function = $call[2][0];
        if (function_exists($function)) {
            $result = XH_evaluateSinglePluginCall($expression);
        } else {
            $result = sprintf($message, $function);
        }
        XH_spliceString($text, $offset, $length, $result);
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
 * Returns the result view of the system check.
 *
 * @param array $data The data ;)
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the core.
 *
 * @return string The (X)HTML.
 *
 * @link http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces#system_check
 *
 * @since 1.5.4
 */
function XH_systemCheck($data)
{
    global $pth, $tx;

    $stx = $tx['syscheck'];

    foreach (array('ok', 'warning', 'failure') as $img) {
        $txt = ucfirst($img);
        $imgs[$img] = tag(
            'img src="' . $pth['folder']['corestyle'] . $img . '.gif" alt="'
            . $txt . '" title="' . $txt . '" width="16" height="16"'
        );
    }

    $o = "<h4>$stx[title]</h4>\n<ul id=\"xh_system_check\">\n";

    if (key_exists('phpversion', $data)) {
        $ok = version_compare(PHP_VERSION, $data['phpversion']) >= 0;
        $o .= '<li>' . $imgs[$ok ? 'ok' : 'fail']
            . sprintf($stx['phpversion'], $data['phpversion']) . "</li>\n";
    }

    if (key_exists('extensions', $data)) {
        $cat = ' class="xh_system_check_cat_start"';
        foreach ($data['extensions'] as $ext) {
            if (is_array($ext)) {
                $notok = $ext[1] ? 'failure' : 'warning';
                $ext = $ext[0];
            } else {
                $notok = 'failure';
            }
            $o .= '<li' . $cat . '>' . $imgs[extension_loaded($ext) ? 'ok' : $notok]
                . sprintf($stx['extension'], $ext) . "</li>\n";
            $cat = '';
        }
    }

    if (key_exists('writable', $data)) {
        $cat = ' class="xh_system_check_cat_start"';
        foreach ($data['writable'] as $file) {
            if (is_array($file)) {
                $notok = $file[1] ? 'failure' : 'warning';
                $file = $file[0];
            } else {
                $notok = 'warning';
            }
            $o .= '<li' . $cat . '>' . $imgs[is_writable($file) ? 'ok' : $notok]
                . sprintf($stx['writable'], $file) . "</li>\n";
            $cat = '';
        }
    }

    if (key_exists('other', $data)) {
        $cat = ' class="xh_system_check_cat_start"';
        foreach ($data['other'] as $check) {
            $notok = $check[1] ? 'failure' : 'warning';
            $o .= '<li' . $cat . '>' . $imgs[$check[0] ? 'ok' : $notok]
                . $check[2] . "</li>\n";
            $cat = '';
        }
    }

    $o .= "</ul>\n";

    return $o;
}


/**
 * Callback for output buffering. Returns the postprocessed (X)HTML.
 *
 * Currently debug information and admin menu are prepended,
 * and $bjs is appended to the body element.
 *
 * @param string $html The (X)HTML generated so far.
 *
 * @global bool   Whether admin mode is active.
 * @global int    The index of the active page.
 * @global string The (X)HTML of the contents area.
 * @global array
 * @global array  The configuration of the core.
 * @global string (X)HTML to be preprended to the closing BODY tag.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_finalCleanUp($html)
{
    global $adm, $s, $o, $errors, $cf, $bjs;

    if ($adm === true) {
        $debugHint = '';
        $errorList = '';
        $margin = 36;

        if ($debugMode = error_reporting() > 0) {
            $debugHint .= '<div class="cmsimplecore_debug">' . "\n"
                . '<b>Notice:</b> Debug-Mode is enabled!' . "\n"
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
            $errorList .= '<div class="cmsimplecore_warning" style="margin: 0;'
                . ' border-width: 0;"><ul>';
            $errors = array_unique($errors);
            foreach ($errors as $error) {
                $errorList .= '<li>' . $error . '</li>';
            }
            $errorList .= '</ul></div>';
        }
        if (isset($cf['editmenu']['scroll'])
            && $cf['editmenu']['scroll'] == 'true'
        ) {
            $id = ' id="editmenu_scrolling"';
            $margin = 0;
        } else {
            $id =' id="editmenu_fixed"';
            $replacement = '<style type="text/css">html {margin-top: ' . $margin
                . 'px;}</style>' ."\n" . '$0';
            $html = preg_replace('~</head>~i', $replacement, $html, 1);
        }

        $replacement = '$0' . '<div' . $id . '>' . $debugHint
            . call_user_func($adminMenuFunc, XH_plugins(true)) . '</div>' ."\n"
            . $errorList;
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
    global $sn, $download, $o;

    // TODO: for security better set $fl = basename($fl) here.
    if (!is_readable($fl)
        || ($download != '' && !chkdl($sn . '?download=' . basename($fl)))
    ) {
        shead('404');
        $o .= '<p>File ' . htmlspecialchars($fl, ENT_NOQUOTES, 'UTF-8') . '</p>';
        return;
    } else {
        header('Content-Type: application/save-as');
        header('Content-Disposition: attachment; filename="' . basename($fl) . '"');
        header('Content-Length:' . filesize($fl));
        header('Content-Transfer-Encoding: binary');
        // TODO: why not readfile() instead?
        if ($fh = @fopen($fl, "rb")) {
            while (!feof($fh)) {
                echo fread($fh, filesize($fl));
            }
            fclose($fh);
        }
        exit;
    }
}


/**
 * Returns whether the file exists in the download folder
 * and is available for download.
 *
 * @param string $fl The download URL, e.g. ?download=file.ext
 *
 * @global array  The paths of system files and folders.
 * @global string The script name.
 *
 * @return bool
 */
function chkdl($fl)
{
    global $pth, $sn;

    $m = false;
    if (@is_dir($pth['folder']['downloads'])) {
        $fd = @opendir($pth['folder']['downloads']);
        while (($p = @readdir($fd)) == true) {
            if (preg_match("/.+\..+$/", $p)) {
                if ($fl == $sn . '?download=' . $p) {
                    $m = true;
                }
            }
        }
        if ($fd == true) {
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
 * @todo Should be deprecated. Use file_get_contents instead.
 */
function rf($fl)
{
    if (!file_exists($fl)) {
        return;
    }
    clearstatcache(); // TODO: remove this?
    // TODO: remove unnecessary fallback for PHP < 4.3
    if (function_exists('file_get_contents')) {
        return file_get_contents($fl);
    } else {
        // this would double the newlines.
        return join("\n", file($fl));
    }
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
 * @global bool   Whether admin mode is active.
 * @global string Error messages as (X)HTML fragment consisting of LI Elements.
 * @global object The pagedata router.
 *
 * @return void
 */
function rfc()
{
    global $c, $cl, $h, $u, $l, $su, $s, $tx, $adm, $e, $pd_router;

    list($u, $tooLong, $h, $l, $c, $pd_router) = array_values(XH_readContents());
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
        $pd_router->new_page();
        return;
    }

    foreach ($tooLong as $i => $tl) {
        if ($adm && $tl) {
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
 * @global bool  Whether admin mode is active.
 *
 * @return array
 *
 * @since 1.6
 */
function XH_readContents($language = null)
{
    global $pth, $cf, $edit, $adm;

    if (isset($language)) {
        $contentFolder = $pth['folder']['base'] . 'content/' . $language . '/';
        $contentFile = $contentFolder . 'content.htm';
        $pageDataFile = $contentFolder . 'pagedata.php';
        include $pth['folder']['language'] . $language . '.php';
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
    $search = explode(URICHAR_SEPARATOR, $tx['urichar']['org']);
    $replace = explode(URICHAR_SEPARATOR, $tx['urichar']['new']);

    if (($content = file_get_contents($contentFile)) === false) {
        return false;
    }
    $stop = $cf['menu']['levels'];
    $split_token = '#@CMSIMPLE_SPLIT@#';

    $content = preg_split('~</body>~i', $content);
    $content = preg_replace(
        '~<h[1-' . $stop . ']~i', $split_token . '$0', $content[0]
    );
    $content = explode($split_token, $content);
    $contentHead = array_shift($content);

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
    if (!($edit && $adm)) {
        $removed = array_keys($pd_router->find_field_value('published', '0'));
        foreach ($c as $i => $text) {
            if (cmscript('remove', $text)) {
                $removed[] = $i;
            }
        }
        foreach ($removed as $i) {
            unset($u[$i]);
            unset($tooLong[$i]);
            unset($h[$i]);
            unset($l[$i]);
            unset($c[$i]);
        }
        $u = array_values($u);
        $tooLong = array_values($tooLong);
        $h = array_values($h);
        $l = array_values($l);
        $c = array_values($c);
        $pd_router->remove($removed);
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
 * @global bool   Whether admin mode is active.
 *
 * @return string The (X)HTML.
 */
function a($i, $x)
{
    global $sn, $u, $cf, $adm;

    if ($i == 0 && !$adm) {
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
        $content = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
        $search = explode(URICHAR_SEPARATOR, $tx['urichar']['org']);
        $replace = explode(URICHAR_SEPARATOR, $tx['urichar']['new']);
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

    if (@realpath($p) == '') {
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
    $fd = @opendir($dir);
    while (false !== ($fn = @readdir($fd))) {
        $fs[] = $fn;
    }
    if ($fd == true) {
        closedir($fd);
    }
    @sort($fs, SORT_STRING);
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
 * @global bool  Whether admin mode is active.
 *
 * @return bool
 */
function hide($i)
{
    global $c, $edit, $adm;

    if ($i < 0) {
        return false;
    }
    return (!($edit && $adm) && cmscript('hide', $c[$i]));
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
    $title = $tx['error'][$s];
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

    if (file_exists($pth['folder']['downloads'] . '_XHdebug.txt')) {
        ini_set('display_errors', 1);
        $dbglevel = rf($pth['folder']['downloads'] . '_XHdebug.txt');
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
            header('HTTP/1.0 400 Bad Request'); // TODO: use "Status:" for FastCGI?
            exit('Malformed UTF-8 detected!');
        }
    }
}


/**
 * Copies default file, if actual language file is missing.
 *
 * @param string $dst The destination filename.
 *
 * @return  void
 *
 * @since 1.6
 */
function XH_createLanguageFile($dst)
{
    $config = preg_match('/config.php$/', $dst) ? 'config' : '';
    if (!file_exists($dst)) {
        if (is_readable($src = dirname($dst) . "/default$config.php")) {
            copy($src, $dst);
        } elseif ($src = is_readable(dirname($dst) . "/en$config.php")) {
            copy($src, $dst);
        }
    }
    // TODO: error reporting???
    //if (!file_exists($dst)) {
    //    e('missing', 'file', $dst);
    //}
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
 */
function pluginFiles($plugin)
{
    global $cf, $pth, $sl;

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

    $pth['file']['plugin_help'] = $pth['folder']['plugin_help']
        . 'help_' . strtolower($sl) . '.htm';
    if (!file_exists($pth['file']['plugin_help'])) {
        $pth['file']['plugin_help'] = $pth['folder']['plugin_help'] . 'help_en.htm';
    }
    if (!file_exists($pth['file']['plugin_help'])
        && file_exists($pth['folder']['plugin_help'] . 'help.htm')
    ) {
        $pth['file']['plugin_help'] = $pth['folder']['plugin_help'] . 'help.htm';
    }
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
        $dh = opendir($pth['folder']['plugins']); // TODO: error handling?
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
 * @global array  The paths of system files and folders.
 * @global string Error messages as (X)HTML fragment consisting of LI Elements.
 *
 * @return void
 */
function writelog($m)
{
    global $pth, $e;

    if ($fh = @fopen($pth['file']['log'], "a")) {
        fwrite($fh, $m);
        fclose($fh);
    } else {
        e('cntwriteto', 'log', $pth['file']['log']);
    }
}


/**
 * Returns the login link.
 *
 * @global array  The configuration of the core.
 * @global int    The index of the requested page.
 * @global array  The localization of the core.
 *
 * @return string The (X)HTML.
 */
function lilink()
{
    global $adm, $s, $tx;

    if (!$adm) {
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
 *
 * @todo i18n (Password forgotten)
 */
function loginforms()
{
    global $cf, $tx, $onload, $f, $o, $s, $sn, $u;

    if ($f == 'login') {
        $cf['meta']['robots'] = "noindex";
        $onload .= 'self.focus();'
            . 'document.forms[\'login\'].elements[\'keycut\'].focus();';
        $f = $tx['menu']['login'];
        $o .= '<div class="xh_login">'
            . '<h1>' . $tx['menu']['login'] . '</h1>'
            . '<p><b>' . $tx['login']['warning'] . '</b></p>'
            . '<form id="login" name="login" action="' . $sn . '?' . $u[$s]
            . '" method="post">'
            . '<div id="login">'
            . tag('input type="hidden" name="login" value="true"')
            . tag('input type="hidden" name="selected" value="' . @$u[$s] . '"')
            . tag('input type="password" name="keycut" id="passwd" value=""') . ' '
            . tag(
                'input type="submit" name="submit" id="submit" value="'
                . $tx['menu']['login'] . '"'
            )
            . '</div></form>';
        if (!empty($cf['security']['email'])) {
            $o .= '<a href="' . $sn . '?&function=forgotten">'
                . $tx['title']['password_forgotten'] . '</a>';
        }
        $o .= ' </div>';
        $s = -1;
    }
}


/**
 * Writes <var>$contents</var> to the file <var>$filename</var>.
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
    $res = ($fh = fopen($filename, 'wb')) && fwrite($fh, $contents);
    if ($fh) {
        fclose($fh);
    }
    return $res;
}


/**
 * Saves the current contents (including the page data).
 *
 * @return bool Whether that succeeded
 *
 * @global array  The content of the pages.
 * @global array  The paths of system files and folders.
 * @global array  The configuration of the core.
 * @global array  The localization of the core.
 * @global object The page data router.
 *
 * @since 1.6
 */
function XH_saveContents()
{
    global $c, $pth, $cf, $tx, $pd_router;

    $hot = '<h[1-' . $cf['menu']['levels'] . '][^>]*>';
    $hct = '<\/h[1-' . $cf['menu']['levels'] . ']>';
    $title = utf8_ucfirst($tx['filetype']['content']);
    $cnts = "<html><head><title>$title</title>\n"
        . $pd_router->headAsPHP()
        . '</head><body>' . "\n";
    foreach ($c as $j => $i) {
        preg_match("/(.*?)($hot(.+?)$hct)(.*)/isu", $i, $matches);
        $page = $matches[1] . $matches[2] . PHP_EOL . $pd_router->pageAsPHP($j)
            . $matches[4];
        $cnts .= rmnl($page . "\n");
    }
    $cnts .= '</body></html>';
    return XH_writeFile($pth['file']['content'], $cnts) !== false;
}


/**
 * Registers a callback for execution after all plugins were loaded,
 * if <var>$callback</var> is given; otherwise executes these callbacks.
 *
 * @param callable $callback The callback.
 *
 * @return void
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
        '/url\(\s*(["\']?)(?!\s*["\']?\/|\s*["\']?http[s]?:)(.*?)(["\']?)\s*\)/su',
        "url(\$1../plugins/$plugin/css/\$2\$3)", $css
    );
}

/**
 * Returns an (X)HTML element formatted as message.
 *
 * @param string $type   The type of message ('success', 'info', 'warning', 'fail').
 * @param string $plugin The name of the plugin (<var>null</var> for the core).
 * @param string $key    The key of the language string.
 *
 * @return string
 *
 * @global array The localization of the core.
 * @global array The localization of the plugins.
 *
 * @since 1.6
 */
function XH_message($type, $plugin, $key)
{
    global $tx, $plugin_tx;

    $class = 'cmsimplecore_' . $type;
    $args = array_slice(func_get_args(), 3);
    $format = !isset($plugin)
        ? $tx['message'][$key]
        : $plugin_tx[$plugin][$key];
    $string = vsprintf($format, $args);
    $string = htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
    return '<p class="' . $class . '">' . $string . '</p>';
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
    static $date = null; // TODO: probably not necessary since wedding

    if (!isset($date)) {
        $date = date("Ymd_His");
    }
    $fn = "${date}_content.htm";
    if (empty($cf['backup']['numberoffiles'])
        || copy($pth['file']['content'], $pth['folder']['content'] . $fn)
    ) {
        if (!empty($cf['backup']['numberoffiles'])) {
            $o = '<p>' . utf8_ucfirst($tx['filetype']['backup'])
                . ' ' . $fn . ' ' . $tx['result']['created'] . '</p>';
        }
        $fl = array();
        $fd = @opendir($pth['folder']['content']);
        while (($p = @readdir($fd)) == true) {
            if (XH_isContentBackup($p)) {
                $fl[] = $p;
            }
        }
        if ($fd) {
            closedir($fd);
        }
        sort($fl);
        $v = count($fl) - $cf['backup']['numberoffiles'];
        for ($i = 0; $i < $v; $i++) {
            if (@unlink($pth['folder']['content'] . $fl[$i])) {
                $o .= '<p>' . utf8_ucfirst($tx['filetype']['backup'])
                    . ' ' . $fl[$i] . ' ' . $tx['result']['deleted'] . '</p>';
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
        $site = htmlspecialchars($site, ENT_QUOTES, 'UTF-8');
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
    echo '<head>', "\n" . head(),
        tag('meta name="robots" content="noindex"'), "\n",
        '</head>', "\n", '<body class="', $bodyClass,'"', onload(), '>', "\n",
        content(), '</body>', "\n", '</html>', "\n";
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
 * @param string $filename A filename.
 *
 * @return bool
 *
 * @since 1.6
 */
function XH_isContentBackup($filename)
{
    return preg_match('/^\d{8}_\d{6}_content.htm$/', $filename);
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

    $handle = opendir($pth['folder']['templates']);
    $templates = array();
    while (($file = readdir($handle)) !== false) {
        if (is_dir($pth['folder']['templates'] . $file) && $file{0} != '.' ) {
            $templates[] = $file;
        }
    }
    closedir($handle);
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
function XH_availableLanguages()
{
    global $pth;

    $handle = opendir($pth['folder']['language']);
    $languages = array();
    while (($file = readdir($handle)) !== false) {
        if (preg_match('/^([a-z]{2})\.php$/i', $file, $m)) {
            $languages[] = $m[1];
        }
    }
    closedir($handle);
    natcasesort($languages);
    return $languages;
}

/**
 * Returns the admin menu.
 *
 * @param array $plugins A list of plugins.
 * @param bool  $debug   Whether the debug mode is enabled.
 *
 * @return string (X)HTML.
 *
 * @global bool   Whether edit mode is active.
 * @global int    The index of the current page.
 * @global array  The URLs of the pages.
 * @global array  The localization of the core.
 * @global string The URL of the current page.
 *
 * @since 1.6
 */
function XH_adminMenu($plugins = array())
{
    global $edit, $s, $u, $tx, $su;

    if ($s < 0) {
        $su = $u[0];
    }
    $changeMode = $edit ? 'normal' : 'edit';
    $changeText = $edit ? $tx['editmenu']['normal'] : $tx['editmenu']['edit'];

    $filesMenu = array();
    foreach (array('images', 'downloads', 'media') as $item) {
        $filesMenu[] =  array(
            'label' => utf8_ucfirst($tx['editmenu'][$item]),
            'url' => '?&amp;normal&amp;' . $item
        );
    }
    $settingsMenu = array(
        array(
            'label' => utf8_ucfirst($tx['editmenu']['configuration']),
            'url' => '?file=config&amp;action=array'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['language']),
            'url' => '?file=language&amp;action=array'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['template']),
            'url' => '?file=template&amp;action=edit'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['stylesheet']),
            'url' => '?file=stylesheet&amp;action=edit'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['log']),
            'url' => '?file=log&amp;action=view'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['validate']),
            'url' => '?&amp;validate'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['backups']),
            'url' => '?&amp;xh_backups'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['sysinfo']),
            'url' => '?&amp;sysinfo'
        )
    );
    $pluginMenu = array();
    foreach ($plugins as $plugin) {
        $pluginMenu[] = array(
            'label' => ucfirst($plugin),
            'url' => '?' . $plugin . '&amp;normal'
        );
    }
    $menu = array(
        array(
            'label' => $changeText,
            'url' => '?' . $su . '&amp;' . $changeMode,
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['pagemanager']),
            'url' => '?&amp;normal&amp;xhpages'
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['files']),
            'url' => '?&amp;normal&amp;userfiles',
            'children' => $filesMenu
            ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['settings']),
            'url' => '?&amp;settings',
            'children' => $settingsMenu
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['plugins']),
            'children' => $pluginMenu
        ),
        array(
            'label' => utf8_ucfirst($tx['editmenu']['logout']),
            'url' => '?&amp;logout'
        )
    );

    $t .= "\n" . '<div id="editmenu">';
    $t .= "\n" . '<ul id="edit_menu">' . "\n";
    foreach ($menu as $item) {
        $t .= XH_adminMenuItem($item);
    }
    $t .= '</ul>' . "\n"
        . '<div style="float:none;clear:both;padding:0;margin:0;'
        . 'width:100%;height:0px;"></div>' . "\n" . '</div>' . "\n";
    return $t;
}

/**
 * Returns the LI element of an admin menu item.
 *
 * @param array $item  The menu item.
 * @param int   $level The level of the menu item.
 *
 * @return string
 *
 * @global string The scipt name.
 *
 * @since 1.6
 */
function XH_adminMenuItem($item, $level = 0)
{
    global $sn;

    $indent = str_repeat('    ', $level);
    $t .= $indent . '<li>';
    if (isset($item['url'])) {
        $t .= '<a href="' . $sn . $item['url'] . '">';
    } else {
        $t .= '<span>';
    }
    $t .= $item['label'];
    if (isset($item['url'])) {
        $t .= '</a>';
    } else {
        $t .= '</span>';
    }
    if (isset($item['children'])) {
        $t .= "\n" . $indent . '    <ul>' . "\n";
        foreach ($item['children'] as $child) {
            $t .= XH_adminMenuItem($child, $level + 1);
        }
        $t .= $indent . '    </ul>' . "\n" . $indent;
    }
    $t .= '</li>' . "\n";
    return $t;
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

    $func = 'json_last_error';
    if (function_exists($func)) {
        return $func(); // indirect call to satisfy PHP_CI
    } else {
        if (!isset($_XH_json)) {
            include_once $pth['folder']['classes'] . 'JSON.php';
            $_XH_json = new XH_JSON();
        }
        return $_XH_json->lastError();
    }
}

?>
