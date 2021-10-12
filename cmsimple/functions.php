<?php

/**
 * @file functions.php
 *
 * General functions.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
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
 * @return string HTML
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
    return "";
}

/**
 * Returns the contents of the given URL adding all current GET parameters.
 *
 * @param string $u A URL.
 *
 * @return string HTML
 */
function geturlwp($u)
{
    global $su;

    $t = '';
    $qs = preg_replace("/^" . preg_quote($su, '/') . "(\&)?/s", "", sv('QUERY_STRING'));
    if ($fh = fopen($u . '?' . $qs, "r")) {
        while (!feof($fh)) {
            $t .= fread($fh, 1024);
        }
        fclose($fh);
        return $t;
    }
    return "";
}

/**
 * Returns a page heading.
 *
 * @param int $n The index of the page.
 *
 * @return string
 *
 * @see $h
 *
 * @deprecated since 1.7. Use $h instead.
 */
function h($n)
{
    global $h;

    trigger_error('Function h() is deprecated', E_USER_DEPRECATED);

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
 *
 * @deprecated since 1.7. Use $l instead.
 */
function l($n)
{
    global $l;

    trigger_error('Function l() is deprecated', E_USER_DEPRECATED);

    return $l[$n];
}

/**
 * Returns a text with CMSimple scripting evaluated.
 *
 * Scripts are evaluated as if they were in the global scope, except that
 * no new global variables can be defined (unless via $GLOBALS).
 *
 * @param string $__text   The text.
 * @param bool   $__compat Whether only last CMSimple script should be evaluated.
 *
 * @return string
 *
 * @since 1.5
 */
function evaluate_cmsimple_scripting($__text, $__compat = true)
{
    extract($GLOBALS, EXTR_REFS);
    $__scripts = array();
    preg_match_all('~#CMSimple (.*?)#~is', $__text, $__scripts);
    if (count($__scripts[1]) > 0) {
        $output = preg_replace('~#CMSimple (?!hide)(.*?)#~is', '', $__text);
        if ($__compat) {
            $__scripts[1] = array_reverse($__scripts[1]);
        }
        foreach ($__scripts[1] as $__script) {
            if (!in_array(strtolower($__script), array('hide', 'remove'))) {
                $__script = html_entity_decode($__script, ENT_QUOTES, 'UTF-8');
                try {
                    eval($__script);
                } catch (ParseError $ex) {
                    trigger_error('Parse error: ' . $ex->getMessage(), E_USER_WARNING);
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
 * @since 1.5
 */
function evaluate_plugincall($text)
{
    global $tx;

    $message = '<span class="xh_fail">' . $tx['error']['plugincall']
        . '</span>';
    $re = '/{{{(?:PLUGIN:)?([a-z_0-9]+)\s*\(?(.*?)\)?;?}}}/iu';
    preg_match_all($re, $text, $calls, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $results = array();
    foreach ($calls as $call) {
        $arguments = preg_replace(
            array(
                '/&(quot|#34);/i', '/&(amp|#38);/i', '/&(apos|#39);/i',
                '/&(lt|#60);/i', '/&(gt|#62);/i', '/&(nbsp|#160);/i'
            ),
            array('"', '&', '\'', '<', '>', ' '),
            $call[2][0]
        );
        $function = $call[1][0];
        if (function_exists($function)) {
            try {
                $results[] = XH_evaluateSinglePluginCall(
                    $function . '(' . $arguments . ')'
                );
            } catch (ParseError $ex) {
                $results[] = '';
                trigger_error('Parse error: ' . $ex->getMessage(), E_USER_WARNING);
            }
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
 * @return string
 *
 * @since 1.6
 */
function XH_evaluateSinglePluginCall($___expression)
{
    extract($GLOBALS);
    return preg_replace_callback(
        '/#(CMSimple .*?)#/is',
        'XH_escapeCMSimpleScripting',
        eval('return ' . $___expression . ';')
    );
}

/**
 * Escapes CMSimple scripting returned from a plugin call.
 *
 * @param array $matches An array of matches.
 *
 * @return string
 *
 * @since 1.6.6
 */
function XH_escapeCMSimpleScripting(array $matches)
{
    trigger_error(
        'CMSimple scripting not allowed in return value of plugin call',
        E_USER_WARNING
    );
    return "#\xE2\x80\x8B{$matches[1]}#";
}

/**
 * Removes a portion of a string and replaces it with something else.
 * This does basically the same to strings as array_splice() for arrays.
 * Note that the behavior of negative values for <var>$offset</var>
 * and <var>$length</var> is not defined.
 *
 * @param string $string      The string to manipulate.
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
 * @return string
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
 * @return string|false
 */
function newsbox($heading)
{
    global $c, $cl, $h, $edit;

    for ($i = 0; $i < $cl; $i++) {
        if ($h[$i] == $heading) {
            $pattern = '/.*?<!--XH_ml[1-9]:.*?-->/isu';
            $body = preg_replace($pattern, "", $c[$i]);
            $pattern = '/#CMSimple (.*?)#/is';
            return XH_ADM && $edit
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
 * @return bool
 *
 * @see http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces
 *
 * @since 1.5
 */
function init_editor(array $elementClasses = array(), $initFile = false)
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
 * @return bool
 *
 * @see http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces
 *
 * @since 1.5
 */
// @codingStandardsIgnoreStart
function include_editor()
{
// @codingStandardsIgnoreEnd
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
 * @param string|false $elementID The element with this ID will become an editor.
 * @param string       $config    The configuration.
 *
 * @return string|false
 *
 * @see http://www.cmsimple-xh.org/wiki/doku.php/plugin_interfaces

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
 * Callback for output buffering. Returns the postprocessed HTML.
 *
 * Currently debug information and admin menu are prepended,
 * and $bjs is appended to the body element.
 *
 * @param string $html The HTML generated so far.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_finalCleanUp($html)
{
    global $errors, $cf, $tx, $bjs;

    if (XH_ADM === true) {
        $debugHint = '';
        $errorList = '';

        if (error_reporting() > 0) {
            $debugHint .= '<div class="xh_debug">' . "\n"
                . $tx['message']['debug_mode'] . "\n"
                . '</div>' . "\n";
        }

        $adminMenuFunc = trim($cf['editmenu']['external']);
        if ($adminMenuFunc == '' || !function_exists($adminMenuFunc)) {
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
        } else {
            $id =' id="xh_adminmenu_fixed"';
        }

        $adminMenu = $adminMenuFunc(XH_plugins(true));
        $replacement = '$0' . '<div' . $id . '>' . addcslashes($debugHint, '$\\')
            . addcslashes($adminMenu, '$\\')
            . '</div>' ."\n" . addcslashes($errorList, '$\\');
        $html = preg_replace('~<body[^>]*>~i', $replacement, $html, 1);
    }

    if (!empty($bjs)) {
        $html = str_replace('</body', "$bjs\n</body", $html);
    }
    return XH_afterFinalCleanUp($html);
}

/**
 * Initializes a global variable according to a GET or POST parameter.
 *
 * @param string $name The name of the global variable.
 *
 * @return void
 *
 * @deprecated since 1.7.0
 */
function initvar($name)
{
    trigger_error('Function ' . __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED);

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
 * @param string $s The key.
 *
 * @return string
 */
function sv($s)
{
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
 * Since magic_quotes are gone, it is a NOP now.
 *
 * @param string $t A string.
 *
 * @return string
 *
 * @deprecated since 1.8
 */
function stsl($t)
{
    return $t;
}

/**
 * Makes the file available for download.
 *
 * If the file can't be downloaded, an HTTP 404 Not found response will be generated.
 *
 * @param string $fl The file name.
 *
 * @return void
 */
function download($fl)
{
    global $download, $o;

    if (!is_readable($fl)
        || ($download != '' && !preg_match('/.+\..+$/', $fl))
    ) {
        shead(404);
        $o .= '<p>File ' . XH_hsc($fl) . '</p>';
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
 * Appends an error message about the file to $e.
 *
 * @param string $et A key in $tx['error'].
 * @param string $ft A key in $tx['filetype'].
 * @param string $fn The file name.
 *
 * @return void
 */
function e($et, $ft, $fn)
{
    global $e, $tx;

    $e .= '<li><b>' . $tx['error'][$et] . ' ' . $tx['filetype'][$ft] . '</b>'
        . '<br>' . $fn . '</li>' . "\n";
}

/**
 * Reads and parses the content file and sets global variables accordingly.
 *
 * @return void
 */
function rfc()
{
    global $edit, $c, $cl, $h, $u, $l, $su, $s, $tx, $e, $pth, $pd_router, $xh_publisher;

    $contents = XH_readContents();
    if ($contents === false) {
        e('missing', 'content', $pth['file']['content']);
        $contents = array(
            array(), array(), array(), array(), array(),
            new XH\PageDataRouter(array(), array(), array(), array()),
            array()
        );
    }
    list($u, $tooLong, $h, $l, $c, $pd_router, $removed) = array_values($contents);
    $duplicate = 0;

    $cl = count($c);
    $s = -1;

    if ($cl == 0) {
        $c[] = '<!--XH_ml1:' . $tx['toc']['newpage'] . '-->'; //HI
        $h[] = trim(strip_tags($tx['toc']['newpage']));
        $u[] = uenc($h[0]);
        $l[] = 1;
        if ($su == $u[0]) {
            $s = 0;
        }
        $cl = 1;
        $removed = array(false);
        $pd_router->appendNewPage(array('last_edit' => '0'));
        $xh_publisher = new XH\Publisher($removed);
        return;
    }

    foreach ($tooLong as $i => $tl) {
        if (XH_ADM && $tl) {
            $e .= '<li><b>' . $tx['uri']['toolong'] . '</b>' . '<br>'
                . '<a href="?' . $u[$i] . '">' . $h[$i] . '</a>' . '</li>';
        }
    }

    foreach ($u as $i => $url) {
        if (($su == $url || $su == urlencode($url))
            && (XH_ADM && $edit || !$removed[$i])
        ) {
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

    $xh_publisher = new XH\Publisher($removed);
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
 * - <var>removed</var>: Flags whether pages are removed.
 * Returns FALSE, if the file couldn't be read.
 *
 * @param string $language The language to read.
 *                         <var>null</var> means the default language.
 *
 * @return array|false
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
    $removed = array();
    $l = array();
    $empty = 0;
    $search = explode(XH_URICHAR_SEPARATOR, $tx['urichar']['org']);
    array_unshift($search, "\xC2\xAD");
    $replace = explode(XH_URICHAR_SEPARATOR, $tx['urichar']['new']);
    array_unshift($replace, "");

    if (($content = XH_readFile($contentFile)) === false) {
        return false;
    }
    $content = preg_split('/(?=<!--XH_ml[1-9]:)/i', $content);
    $content[] = preg_replace('/(.*?)<\/body>.*/isu', '$1', array_pop($content));
    $contentHead = array_shift($content);

    $temp_h = array();
    foreach ($content as $page) {
        $c[] = $page;
        preg_match('~<!--XH_ml([1-9]):(.*)-->~isU', $page, $temp);
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
        $ancestors[(int) $l[$i] - 1] = XH_uenc($temp, $search, $replace);
        $ancestors = array_slice($ancestors, 0, (int) $l[$i]);
        $url = implode($cf['uri']['seperator'], $ancestors);
        $u[] = utf8_substr($url, 0, (int) $cf['uri']['length']);
        $tooLong[] = utf8_strlen($url) > $cf['uri']['length'];
        $removed[] = false;
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

    $pd_router = new XH\PageDataRouter($h, $page_data_fields, $temp_data, $page_data);

    // remove unpublished pages
    if (!($edit && XH_ADM)) {
        foreach ($c as $i => $text) {
            if (cmscript('remove', $text)) {
                $c[$i] = '#CMSimple hide# #CMSimple shead(404);#';
                $removed[$i] = true;
            }
        }
    }

    //TODO: don't use $cf['menu']['levels'] anymore
    $cf['menu']['levels'] = count($l) ? max($l) : 1;

    return array(
        'urls' => $u,
        'too_long' => $tooLong,
        'headings' => $h,
        'levels' => $l,
        'pages' => $c,
        'pd_router' => $pd_router,
        'removed' => $removed
    );
}

/**
 * Finds the index of the previous page.
 *
 * @return int|false
 *
 * @since 1.6.3
 */
function XH_findPreviousPage()
{
    global $s;

    for ($i = $s - 1; $i > -1; $i--) {
        if (!hide($i)) {
            return $i;
        }
    }
    return false;
}

/**
 * Finds the index of the next page.
 *
 * @return int|false
 *
 * @since 1.6.3
 */
function XH_findNextPage()
{
    global $s, $cl;

    for ($i = $s + 1; $i < $cl; $i++) {
        if (!hide($i)) {
            return $i;
        }
    }
    return false;
}

/**
 * Returns an opening a tag as link to a page.
 *
 * @param int    $i The page index.
 * @param string $x Arbitrary appendix of the URL.
 *
 * @return string HTML
 */
function a($i, $x)
{
    $a_href = XH_getPageURL($i);
    if (stripos($a_href, '?') === false) {
        ($x ? $x = '?' . $x : '');
    }
    return '<a href="' . $a_href . $x . '">';
}

/**
 * Returns the meta element for name, if defined in <var>$cf['meta']</var>;
 * <var>null</var> otherwise.
 *
 * @param string $n The name attribute.
 *
 * @return string|null HTML
 */
function meta($n)
{
    global $cf, $tx, $print;

    $exclude = array('robots', 'keywords', 'description');
    $value = isset($tx['meta'][$n]) ? $tx['meta'][$n] : $cf['meta'][$n];
    if ($n != 'codepage' && !empty($value) && !($print && in_array($n, $exclude))) {
        $content = XH_hsc($value);
        return '<meta name="' . $n . '" content="' . $content . '">' . "\n";
    }
    return null;
}

/**
 * Returns the link to a special CMSimple_XH page, e.g. sitemap.
 *
 * @param string $i A key of $tx['menu'].
 *
 * @return string HTML
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
 * @return string
 *
 * @see XH_uenc()
 */
function uenc($s)
{
    global $tx;

    if (isset($tx['urichar']['org']) && isset($tx['urichar']['new'])) {
        $search = explode(XH_URICHAR_SEPARATOR, $tx['urichar']['org']);
        array_unshift($search, "\xC2\xAD");
        $replace = explode(XH_URICHAR_SEPARATOR, $tx['urichar']['new']);
        array_unshift($replace, "");
    } else {
        $search = $replace = array();
    }
    return XH_uenc($s, $search, $replace);
}

/**
 * Returns a percent encoded URL component.
 *
 * Additionally all character sequences in $search will be replaced
 * by their according character sequences in $replace, spaces will be replaced
 * by the configured word_separator and leading, trailing and multiple
 * consecutive word_separators will be trimmed.
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
function XH_uenc($s, array $search, array $replace)
{
    global $cf;

    $separator = $cf['uri']['word_separator'];
    $s = str_replace($search, $replace, $s);
    $s = str_replace('+', $separator, urlencode($s));
    $s = trim($s, $separator);
    $s = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $s);
    return $s;
}

/**
 * Returns the alphabetically sorted content of a directory.
 *
 * Caveat: the result includes '.' and '..'.
 *
 * @param string $dir An existing directory path.
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
 * @return int
 */
function cmscript($script, $text)
{
    $pattern = str_replace('(.*?)', $script, '/#CMSimple (.*?)#/is');
    return preg_match($pattern, $text);
}

/**
 * Returns whether a page is hidden.
 *
 * @param int $i The page index.
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
 * Returns an HTML stand alone tag.
 *
 * Used to returns an (X)HTML compliant stand alone tag
 * according to the settings of $cf['xhtml']['endtags'].
 *
 * @param string $s The contents of the tag.
 *
 * @return string HTML
 *
 * @deprecated since 1.7
 *
 * @todo Add deprecation warning (XH 1.8?)
 */
function tag($s)
{
    return '<' . $s . '>';
}

/**
 * Sends error header and sets $title and $o accordingly.
 *
 * @param int $s The HTTP status response code (401, 403, 404).
 *
 * @return void
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
 * @return boolean Whether error_reporting is enabled.
 *
 * @author Holger
 *
 * @since 1.0rc3
 */
function XH_debugmode()
{
    global $pth;

    $dbglevel = '';
    $filename = $pth['folder']['downloads'] . '_XHdebug.txt';
    if (file_exists($filename)) {
        ini_set('display_errors', "0");
        $dbglevel = file_get_contents($filename);
        if (strlen($dbglevel) == 1) {
            set_error_handler('XH_debug');
            switch ($dbglevel) {
                case 0:
                    error_reporting(0);
                    break;
                case 1:
                    error_reporting(E_ERROR | E_USER_ERROR | E_USER_WARNING | E_PARSE);
                    break;
                case 2:
                    error_reporting(E_ERROR | E_USER_ERROR | E_WARNING | E_USER_WARNING | E_PARSE);
                    break;
                case 3:
                    error_reporting(
                        E_ERROR | E_USER_ERROR | E_WARNING | E_USER_WARNING | E_PARSE | E_NOTICE
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
                    error_reporting(E_ERROR | E_USER_ERROR | E_USER_WARNING | E_PARSE);
            }
        } else {
            error_reporting(E_ERROR | E_USER_ERROR | E_USER_WARNING | E_PARSE);
        }
    } else {
        ini_set('display_errors', "0");
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
 *
 * @return bool
 */
function XH_debug($errno, $errstr, $errfile, $errline)
{
    global $errors;

    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return false;
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
        case E_RECOVERABLE_ERROR:
            $errtype = 'ERROR';
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
            $errtype = "Unknown error type [$errno]";
    }

    $errors[] = "<b>$errtype:</b> $errstr" . '<br>' . "$errfile:$errline"
        . '<br>' . "\n";

    if (in_array($errno, array(E_USER_ERROR, E_RECOVERABLE_ERROR))) {
        XH_exit($errors[count($errors) - 1]);
    }

    /* Don't execute PHP internal error handler */
    return true;
}

/**
 * Checks <var>$arr</var> recursively for valid UTF-8.
 * Otherwise it exits the script.
 *
 * Useful for checking user input.
 *
 * @param array $arr Array to check.
 *
 * @return void
 *
 * @since 1.5.5
 */
function XH_checkValidUtf8(array $arr)
{
    global $tx;

    foreach ($arr as $elt) {
        if (is_array($elt)) {
            XH_checkValidUtf8($elt);
        } elseif (!utf8_is_valid($elt)) {
            header('HTTP/1.0 400 Bad Request');
            header('Content-Type: text/html; charset=UTF-8');
            echo <<<EOT
<!DOCTYPE html>
<html>
    <head><title>{$tx['title']['bad_request']}</title></head>
    <body>{$tx['error']['badrequest']}</body>
</html>
EOT;
            exit;
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
 *
 * @todo Remove handling of LANGconfigs, unless they won't get reintroduced.
 */
function XH_createLanguageFile($dst)
{
    $config = preg_match('/config.php$/', $dst) ? 'config' : '';
    if (!file_exists($dst)) {
        if (is_readable($src = dirname($dst) . "/default$config.php")) {
            return copy($src, $dst);
        } elseif (is_readable($src = dirname($dst) . "/en$config.php")) {
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
 * @return void
 */
function pluginFiles($plugin)
{
    global $pth, $sl;
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
 * Returns a list of all active plugins.
 *
 * @param bool $admin Whether to return only plugins with a admin.php
 *
 * @return array
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
        if (is_dir($pth['folder']['plugins']) && ($dh = opendir($pth['folder']['plugins']))) {
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
        sort($plugins, SORT_NATURAL | SORT_FLAG_CASE);
        sort($admPlugins, SORT_NATURAL | SORT_FLAG_CASE);
    }
    return $admin ? $admPlugins : $plugins;
}

/**
 * Returns the value of a cookie, or <var>null</var> if the cookie doesn't exist.
 *
 * @param string $s The name of the cookie.
 *
 * @return string|null
 */
function gc($s)
{
    if (isset($_COOKIE[$s])) {
        return $_COOKIE[$s];
    }
    return null;
}

/**
 * Returns wether the user is logged in.
 *
 * @return bool
 */
function logincheck()
{
    global $cf;

    XH_startSession();
    return isset($_SESSION['xh_password'])
        && $_SESSION['xh_password'] == $cf['security']['password']
        && isset($_SESSION['xh_user_agent'])
        && $_SESSION['xh_user_agent'] == md5($_SERVER['HTTP_USER_AGENT']);
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
        if (XH_lockFile($stream, LOCK_EX)) {
            $ok = fwrite($stream, $message . PHP_EOL) !== false;
            fflush($stream);
            XH_lockFile($stream, LOCK_UN);
        }
        fclose($stream);
    }
    return $ok;
}

/**
 * Returns the login form.
 *
 * @return void
 */
function loginforms()
{
    global $cf, $tx, $onload, $f, $o, $s, $sn, $su, $u, $title, $xh_publisher;

    if ($f == 'login' || $f == 'xh_login_failed') {
        $cf['meta']['robots'] = "noindex";
        $onload .= 'document.forms[\'login\'].elements[\'keycut\'].focus();';
        $message = ($f == 'xh_login_failed')
            ? XH_message('fail', $tx['login']['failure'])
            : '';
        $title = $tx['menu']['login'];
        $o .= '<div class="xh_login">'
            . '<h1>' . $tx['menu']['login'] . '</h1>'
            . $message
            . '<p><b>' . $tx['login']['warning'] . '</b></p>'
            . '<form id="login" name="login" action="' . $sn . '?' . $su
            . '" method="post">'
            . '<input type="hidden" name="login" value="true">'
            . '<input type="password" name="keycut" id="passwd" value="">'
            . ' '
            . '<input type="submit" name="submit" id="submit" value="'
            . $tx['menu']['login'] . '">'
            . '</form>';
        if (!empty($cf['security']['email'])) {
            $o .= '<p><a href="' . $sn . '?&function=forgotten">'
                . $tx['title']['password_forgotten'] . '</a></p>';
        }
        $query = $su === 'login' ? $u[$xh_publisher->getFirstPublishedPage()] : $su;
        if ($query !== '') {
            $query = "?$query";
        }
        $o .= '<p><a href="' . "$sn$query" . '">' . $tx['login']['back']
            . '</a></p>';
        $o .= ' </div>';
        $s = -1;
    }
}

/**
 * Reads a file and returns its contents; <var>false</var> on failure.
 * During reading, the file is locked for shared access.
 *
 * @param string $filename A file path.
 *
 * @return string|false
 *
 * @since 1.6
 */
function XH_readFile($filename)
{
    $contents = false;
    $stream = fopen($filename, 'rb');
    if ($stream) {
        if (XH_lockFile($stream, LOCK_SH)) {
            $contents = stream_get_contents($stream);
            XH_lockFile($stream, LOCK_UN);
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
    $stream = fopen($filename, 'cb');
    if ($stream) {
        if (XH_lockFile($stream, LOCK_EX)) {
            ftruncate($stream, 0);
            $res = fwrite($stream, $contents);
            fflush($stream);
            XH_lockFile($stream, LOCK_UN);
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
 * @since 1.6
 */
function XH_afterPluginLoading($callback = null)
{
    static $callbacks = array();

    if (isset($callback)) {
        $callbacks[] = $callback;
    } else {
        foreach ($callbacks as $callback) {
            $callback();
        }
    }
}

/**
 * Registers or executes registered callbacks at the end of XH_finalCleanUp().
 *
 * Registers a callback for execution at the end of {@link XH_finalCleanUp()},
 * if <var>$param</var> is a callable; otherwise executes these callbacks,
 * passing <var>$param</var> as parameter to the callback function. The latter
 * variant is supposed to be called only by the core, and in this case will
 * invoke the callback with the page HTML, and expects the callback to return
 * the possibly modified HTML.
 *
 * @param mixed $param A parameter.
 *
 * @return void|string
 *
 * @since 1.7
 */
function XH_afterFinalCleanUp($param)
{
    static $callbacks = array();

    if (is_callable($param)) {
        $callbacks[] = $param;
    } else {
        foreach ($callbacks as $callback) {
            $param = $callback($param);
        }
        return $param;
    }
}

/**
 * Returns the path of the combined plugin stylesheet.
 * If necessary, this stylesheet will be created/updated.
 *
 * @return string
 *
 * @since 1.6
 */
function XH_pluginStylesheet()
{
    global $pth;

    $plugins = XH_plugins();

    // create array of pluginname => hash of CSS file contents
    $hashes = array('core' => sha1_file($pth['file']['corestyle']));
    foreach ($plugins as $plugin) {
        $fn = $pth['folder']['plugins'] . $plugin . '/css/stylesheet.css';
        if (is_file($fn)) {
            $hashes[$plugin] = sha1_file($fn);
        } else {
            $hashes[$plugin] = '';
        }
    }

    $ofn = $pth['folder']['corestyle'] . 'xhstyles.css';
    $expired = !file_exists($ofn);

    // check for newly (un)installed plugins and changes in the individual plugin stylesheets
    if (!$expired) {
        if (($ofp = fopen($ofn, 'r')) !== false
            && fgets($ofp) && fgets($ofp)
            && ($oldPlugins = fgets($ofp))
        ) {
            $oldPlugins = explode(',', trim($oldPlugins, " *\r\n"));
            $oldhashes = array();
            foreach ($oldPlugins as $oldPlugin) {
                list($plugin, $hash) = explode(':', $oldPlugin);
                $oldhashes[$plugin] = $hash;
            }
            $expired = $hashes != $oldhashes;
        } else {
            $expired = true;
        }
        if ($ofp !== false) {
            fclose($ofp);
        }
    }

    // create combined plugin stylesheet
    if ($expired) {
        $o = array(
            PHP_EOL . '/' . str_pad(' ' . $pth['file']['corestyle'], 76, '*', STR_PAD_LEFT) . ' */'
            . PHP_EOL . PHP_EOL . file_get_contents($pth['file']['corestyle'])
        );
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
        $pluginline = '';
        foreach ($hashes as $plugin => $hash) {
            if ($pluginline) {
                $pluginline .= ',';
            }
            $pluginline .= "$plugin:$hash";
        }
        $o = '/*' . PHP_EOL
            . ' * Automatically created by CMSimple_XH. DO NOT MODIFY!' . PHP_EOL
            . ' * ' . $pluginline . PHP_EOL
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
        "url(\$1../../plugins/$plugin/css/\$2\$3)",
        $css
    );
}

/**
 * Returns an HTML element formatted as message.
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
 * Creates backups of all content files.
 *
 * Surplus old backups will be deleted. Returns an appropriate message.
 *
 * @return string HTML
 *
 * @since 1.6
 */
function XH_backup()
{
    global $pth;

    $languages = XH_secondLanguages();
    $folders = array($pth['folder']['base'] . 'content/');
    foreach ($languages as $language) {
        $folders[] = $pth['folder']['base'] . 'content/' . $language . '/';
    }
    $backup = new XH\Backup($folders);
    return $backup->execute();
}

/**
 * Returns whether <var>$name</var> is a language folder.
 *
 * @param string $name The name to check.
 *
 * @return bool
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
 * @return string HTML
 *
 * @since 1.6
 */
function XH_builtinTemplate($bodyClass)
{
    global $sl, $_XH_csrfProtection, $bjs;

    echo '<!DOCTYPE html>', "\n", '<html',
        (strlen($sl) == 2 ? " lang=\"$sl\"" : ''), '>', "\n";
    $content = XH_convertPrintUrls(content());
    echo '<head>', "\n" . head(),
        '<meta name="robots" content="noindex">', "\n",
        '</head>', "\n", '<body class="', $bodyClass,'"', onload(), '>', "\n",
        $content, $bjs, '</body>', "\n", '</html>', "\n";
    if (isset($_XH_csrfProtection)) {
        $_XH_csrfProtection->store();
    }
    exit;
}
/**
 * Returns a help icon which displays a tooltip on hover.
 *
 * @param string $tooltip A tooltip in HTML.
 *
 * @return string HTML
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
        . '<img src="' . $src . '" alt="' . $tx['editmenu']['help'] . '">'
        . '<div>' . $tooltip . '</div>'
        . '</div>';
    return $o;
}

/**
 * Returns whether a file is a content backup by checking the filename.
 *
 * @param string $filename    A filename.
 * @param bool   $regularOnly Whether to check for regalur backup names only.
 *
 * @return bool
 *
 * @since 1.6
 */
function XH_isContentBackup($filename, $regularOnly = true)
{
    $suffix = $regularOnly ? 'content' : '[^.]+';
    return (bool) preg_match('/^\d{8}_\d{6}_' . $suffix . '.htm$/', $filename);
}

/**
 * Returns an array of installed templates.
 *
 * @return array
 *
 * @since 1.6
 */
function XH_templates()
{
    global $pth;

    $templates = array();
    if (is_dir($pth['folder']['templates']) && ($handle = opendir($pth['folder']['templates']))) {
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
    sort($templates, SORT_NATURAL | SORT_FLAG_CASE);
    return $templates;
}

/**
 * Returns an array of available languages (in cmsimple/languages/).
 *
 * @return array
 *
 * @since 1.6
 */
function XH_availableLocalizations()
{
    global $pth;

    $languages = array();
    if (is_dir($pth['folder']['language']) && ($handle = opendir($pth['folder']['language']))) {
        while (($file = readdir($handle)) !== false) {
            if (preg_match('/^([a-z]{2})\.php$/i', $file, $m)) {
                $languages[] = $m[1];
            }
        }
        closedir($handle);
    }
    sort($languages, SORT_NATURAL | SORT_FLAG_CASE);
    return $languages;
}

/**
 * Returns the installed second languages in alphabetic order.
 *
 * @return array
 *
 * @since 1.6
 */
function XH_secondLanguages()
{
    global $pth;
    static $langs;

    if (!isset($langs)) {
        $langs = array();
        if (is_dir($pth['folder']['base']) && ($dir = opendir($pth['folder']['base']))) {
            while (($entry = readdir($dir)) !== false) {
                if ($entry[0] != '.' && XH_isLanguageFolder($entry)) {
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
 * @return bool
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
 * @param array $urlParts Parts of an URL.
 *
 * @return bool
 *
 * @since 1.6
 */
function XH_isInternalUrl($urlParts)
{
    $ok = true;
    foreach (array('scheme', 'host', 'port', 'user', 'pass') as $key) {
        $ok = $ok && !isset($urlParts[$key]);
    }
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
function XH_convertToPrintUrl(array $matches)
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
 * @param string $pageContent Some HTML.
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
 * @since 1.6
 *
 * @todo Deprecate starting with 1.8.
 */
function XH_decodeJson($string)
{
    return json_decode($string);
}

/**
 * Returns the JSON representation of a value.
 *
 * @param mixed $value A PHP value.
 *
 * @return string or
 *         bool false on JSON error
 *
 * @since 1.6
 *
 * @todo Deprecate starting with 1.8.
 */
function XH_encodeJson($value)
{
    return json_encode($value);
}

/**
 * Returns whether an error has occurred
 * during the last {@link XH_decodeJSON()}.
 *
 * @return bool
 *
 * @since 1.6
 *
 * @todo Deprecate starting with 1.8.
 */
function XH_lastJsonError()
{
    return (bool) json_last_error();
}

/**
 * Converts special characters to HTML entities.
 *
 * Same as htmlspecialchars($string, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8').
 *
 * @param string $string A string.
 *
 * @return string
 *
 * @since 1.5.8
 */
function XH_hsc($string)
{
    return htmlspecialchars($string, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Handles a mailform embedded in a CMSimple_XH page.
 *
 * @param string $subject An alternative subject field preset text
 *                        instead of the subject default in localization.
 *
 * @return string HTML
 *
 * @since 1.6
 */
function XH_mailform($subject = null)
{
    global $cf;

    if ($cf['mailform']['email'] == '') {
        return '';
    }

    $mailform = new XH\Mailform(true, $subject);
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
        if (XH_lockFile($_stream, LOCK_SH)) {
            $_res = include $_filename;
            XH_lockFile($_stream, LOCK_UN);
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
        $var = XH_includeVar($filename, $varname);
        $$varname = XH_unionOf2DArrays(
            is_array($var) ? $var : array(),
            is_array($$varname) ? $$varname : array()
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
function XH_unionOf2DArrays(array $array1, array $array2)
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
 *
 * @todo Deprecate for 1.8.
 */
function XH_renameFile($oldname, $newname)
{
    return rename($oldname, $newname);
}

/**
 * Exits the running script.
 *
 * Simple wrapper for exit for testing purposes.
 *
 * @param mixed $status A status message or code.
 *
 * @return noreturn
 *
 * @since 1.6.2
 */
function XH_exit($status = 0)
{
    exit($status);
}

/**
 * Returns the root (= installation) folder of the system.
 *
 * @return string
 *
 * @since 1.6.2
 */
function XH_getRootFolder()
{
    global $sn, $sl;

    return preg_replace(
        '/\/' . preg_quote($sl, '/') . '\/$/',
        '/',
        preg_replace('/\/index\.php$/', '/', $sn)
    );
}

/**
 * Registers the type of a plugin resp. returns the registered plugins of a
 * certain type.
 *
 * @param string $type   A plugin type ('editor', 'filebrowser', 'pagemanager',
 *                       'editmenu').
 * @param string $plugin A plugin name or <var>null</var>.
 *
 * @return mixed
 *
 * @since 1.6.2
 */
function XH_registerPluginType($type, $plugin = null)
{
    static $plugins = array();

    if (isset($plugin)) {
        $plugins[$type][] = $plugin;
    } else {
        if (isset($plugins[$type])) {
            $result = $plugins[$type];
            sort($result, SORT_NATURAL | SORT_FLAG_CASE);
            return $result;
        } else {
            return array();
        }
    }
}

/**
 * Returns the names of the registered editor plugins.
 *
 * @return array
 *
 * @since 1.6.2
 */
function XH_registeredEditorPlugins()
{
    return XH_registerPluginType('editor');
}

/**
 * Returns the names of the registered filebrowser plugins.
 *
 * @return array
 *
 * @since 1.6.2
 */
function XH_registeredFilebrowserPlugins()
{
    return XH_registerPluginType('filebrowser');
}

/**
 * Returns the names of the registered pagemanager plugins.
 *
 * @return array
 *
 * @since 1.6.2
 */
function XH_registeredPagemanagerPlugins()
{
    return XH_registerPluginType('pagemanager');
}

/**
 * Returns the names of the registered editmenu plugins.
 *
 * @return array
 *
 * @since 1.6.2
 */
function XH_registeredEditmenuPlugins()
{
    return XH_registerPluginType('editmenu');
}

/**
 * Handles the shutdown of the script.
 *
 * <ul>
 * <li>Unsets erroneously set password in session (backdoor mitigation).</li>
 * <li>Displays a message if a fatal error occurred.</li>
 * </ul>
 *
 * @return void
 *
 * @since 1.6.3
 */
function XH_onShutdown()
{
    global $tx;

    if (!XH_ADM && isset($_SESSION['xh_password'])) {
        unset($_SESSION['xh_password']);
    }

    $lastError = error_get_last();
    if (isset($lastError) && in_array($lastError['type'], array(E_ERROR, E_PARSE))) {
        if (error_reporting() <= 0) {
            echo $tx['error']['fatal'];
        } else {
            printf(
                '%s in <b>%s</b> on line <b>%d</b>',
                nl2br($lastError['message']),
                $lastError['file'],
                $lastError['line']
            );
        }
    }
}

/**
 * Returns a timestamp formatted according to config and lang.
 *
 * @param int $timestamp A UNIX timestamp.
 *
 * @return string
 *
 * @since 1.6.3
 */
function XH_formatDate($timestamp)
{
    global $cf, $tx;

    if (class_exists('IntlDateFormatter', false)) {
        $dateFormatter = new IntlDateFormatter(
            $tx['locale']['all'] ? $tx['locale']['all'] : null,
            constant('IntlDateFormatter::' . strtoupper($cf['format']['date'])),
            constant('IntlDateFormatter::' . strtoupper($cf['format']['time']))
        );
        return $dateFormatter->format($timestamp);
    }
    return date($tx['lastupdate']['dateformat'], $timestamp);
}

/**
 * Implements portable advisory file locking.
 *
 * For now it is just a simple wrapper around {@link flock flock()}.
 *
 * @param resource $handle    A file handle.
 * @param int      $operation A lock operation (use LOCK_SH, LOCK_EX or LOCK_UN).
 *
 * @return bool
 *
 * @since 1.6.3
 */
function XH_lockFile($handle, $operation)
{
    return flock($handle, $operation);
}

/**
 * Highlights the search words in a text.
 *
 * @param array  $words An array of search words.
 * @param string $text  A text.
 *
 * @return string HTML
 *
 * @since 1.6.5
 */
function XH_highlightSearchWords(array $words, $text)
{
    $words = array_unique($words);
    usort($words, function ($a, $b) {
        return strlen($b) - strlen($a);
    });
    $patterns = array();
    foreach ($words as $word) {
        $word = trim($word);
        if ($word != '') {
            $patterns[] = '/(?:<(?:"[^"]*?"|[^>]*?)*>|(' . preg_quote($word, '/') . ')|&[^;]*;)/isu';
        }
    }
    return preg_replace_callback(
        $patterns,
        function ($matches) {
            if (!isset($matches[1])) {
                return $matches[0];
            } else {
                return "<span class=\"xh_find\">{$matches[1]}</span>";
            }
        },
        $text
    );
}

/**
 * Autoloads classes named after CMSimple_XH/PEAR coding standards.
 *
 * @param string $className A class name.
 *
 * @return void
 *
 * @since 1.7
 */
function XH_autoload($className)
{
    global $pth;

    $className = str_replace('_', '\\', $className);
    // set $package, $subpackages and $class
    $subpackages = explode('\\', $className);
    if (count($subpackages) <= 1) {
        return;
    }
    $packages = array_splice($subpackages, 0, 1);
    $package = $packages[0];
    $classes = array_splice($subpackages, -1);
    $class = $classes[0];

    // construct $filename
    if ($package == 'XH') {
        $folder = $pth['folder']['classes'];
    } else {
        $folder = $pth['folder']['plugins'] . strtolower($package) . '/classes/';
    }
    foreach ($subpackages as $subpackage) {
        $folder .= strtolower($subpackage) . '/';
    }
    $filename = $folder . $class . '.php';

    if (!file_exists($filename)) {
        return;
    }

    include_once $filename;

    if (class_exists($className)) {
        class_alias($className, str_replace('\\', '_', $className));
    }
}

/**
 * Starts a named session.
 *
 * If session is already started, nothing happens.
 *
 * @return void
 *
 * @since 1.7
 */
function XH_startSession()
{
    global $pth;

    if (session_id() == '') {
        $sessionName = 'XH_' . bin2hex(CMSIMPLE_ROOT);
        file_put_contents("{$pth['folder']['cmsimple']}.sessionname", $sessionName);
        session_name($sessionName);
        session_start();
    }
}

/**
 * Returns the locator (breadcrumb navigation) model.
 *
 * The locator model is an ordered list of breadcrumb items, where each item is
 * an array of the title and the URL. If there is no appropriate URL, the
 * element is null.
 *
 * @return array
 *
 * @since 1.7
 */
function XH_getLocatorModel()
{
    global $title, $h, $s, $f, $l, $tx, $cf, $xh_publisher;

    if (hide($s) && $cf['show_hidden']['path_locator'] != 'true') {
        return array(array($h[$s], XH_getPageURL($s)));
    }
    $firstPublishedPage = $xh_publisher->getFirstPublishedPage();
    if ($s == $firstPublishedPage) {
        return array(array($h[$s], XH_getPageURL($s)));
    } elseif ($title != '' && (!isset($h[$s]) || $h[$s] != $title)) {
        $res = array(array($title, null));
    } elseif ($f != '') {
        return array(array(ucfirst($f), null));
    } elseif ($s > $firstPublishedPage) {
        $res = array();
        $tl = $l[$s];
        if ($tl > 1) {
            for ($i = $s - 1; $i > $firstPublishedPage; $i--) {
                if ($l[$i] < $tl) {
                    array_unshift($res, array($h[$i], XH_getPageURL($i)));
                    $tl--;
                }
                if ($tl < 2) {
                    break;
                }
            }
        }
    } else {
        return array(array('&nbsp;', null));
    }
    if ($cf['locator']['show_homepage'] == 'true') {
        array_unshift(
            $res,
            array($tx['locator']['home'], XH_getPageURL($firstPublishedPage))
        );
        if ($s > $firstPublishedPage && $h[$s] == $title) {
            $res[] = array($h[$s], XH_getPageURL($s));
        }
        return $res;
    } else {
        if ($s > $firstPublishedPage && $h[$s] == $title) {
            $res[] = array($h[$s], XH_getPageURL($s));
        }
        return $res;
    }
}

/**
 * Returns the full URL of a page.
 *
 * @param int $index A valid page index.
 *
 * @return string
 *
 * @since 1.7
 */
function XH_getPageURL($index)
{
    global $sn, $u, $xh_publisher;

    if ($index === $xh_publisher->getFirstPublishedPage() && !(XH_ADM)) {
        return $sn;
    } else {
        return $sn . '?' . $u[$index];
    }
}

/**
 * Returns the URL where to redirect `selected` GEt requests.
 *
 * @return string
 *
 * @since 1.7.0
 */
function XH_redirectSelectedUrl()
{
    global $selected;

    $queryString = ltrim(preg_replace('/&?selected=[^&]+/', '', $_SERVER['QUERY_STRING']), '&');
    if ($queryString) {
        $queryString = "$selected&$queryString";
    } else {
        $queryString = $selected;
    }
    return CMSIMPLE_URL . "?$queryString";
}
