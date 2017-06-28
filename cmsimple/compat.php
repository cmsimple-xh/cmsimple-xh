<?php

/**
 * @file compat.php
 *
 * Backward compatible functionality.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/**
 * Returns the code to display a photogallery.
 *
 * @param string $u Autogallery's installation folder.
 *
 * @global string The URL of the active page.
 *
 * @return string HTML
 *
 * @deprecated since 1.5.4. Use a gallery plugin instead.
 */
function autogallery($u)
{
    global $su;

    trigger_error('Function autogallery() is deprecated', E_USER_DEPRECATED);

    return preg_replace(
        "/.*<!-- autogallery -->(.*)<!-- \/autogallery -->.*/is",
        '$1',
        preg_replace(
            "/(option value=\"\?)(p=)/is",
            '${1}' . $su . '&$2',
            preg_replace(
                "/(href=\"\?)/is",
                '${1}' . $su . '&amp;',
                preg_replace("/(src=\")(\.)/is", '${1}' . $u . '$2', geturlwp($u))
            )
        )
    );
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
 * Returns `&` or `&amp;amp;` according to the setting of `$cf['xhtml']['amp']`.
 *
 * @return string HTML
 *
 * @global array The configuration of the core.
 *
 * @deprecated since 1.5.4. Use `&amp;amp;` instead.
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
 * Returns the link to the guestbook.
 *
 * @return string HTML
 *
 * @deprecated since 1.5.4
 */
function guestbooklink()
{
    trigger_error('Function ' . __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED);
    if (function_exists('gblink')) {
        return gblink();
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

    trigger_error('Function ' . __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED);
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
    trigger_error('Function ' . __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED);
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
 *
 * @return bool
 *
 * @deprecated since 1.6.
 */
function chkfile($fl, $writable)
{
    global $pth;

    trigger_error('Function '. __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED);

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
 *
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
 * Appends a message to the logfile.
 *
 * On failure an according message is appended to $e.
 *
 * @param string $m The log message.
 *
 * @return void
 *
 * @global array  The paths of system files and folders.
 *
 * @deprecated since 1.6
 */
function writelog($m)
{
    global $pth;

    trigger_error('Function ' . __FUNCTION__ . '() is deprecated', E_USER_DEPRECATED);
    if ($fh = fopen($pth['file']['log'], "a")) {
        fwrite($fh, $m);
        fclose($fh);
    } else {
        e('cntwriteto', 'log', $pth['file']['log']);
    }
}
