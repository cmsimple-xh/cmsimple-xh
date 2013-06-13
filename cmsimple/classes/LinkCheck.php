<?php

/**
 * The link checker.
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


/**
 * The link checker.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 *
 * @todo needs fixing!
 */
class XH_LinkCheck
{
    /**
     * Checks all links and returns the result view.     *
     *
     * @return string The (X)HTML.
     *
     * @global array The page contents.
     * @global array The page URLs.
     * @global int   The number of pages.
     *
     * @access public
     */
    function checkLinks()
    {
        global $c, $u, $cl;

        $checkedLinks = 0;
        for ($i = 0; $i < $cl; $i++) {
            $pattern = '/<a.*?href=["]?([^"]*)["]?.*?>(.*?)<\/a>/is';
            preg_match_all($pattern, $c[$i], $pageLinks);
            if (count($pageLinks[1]) > 0) {
                foreach ($pageLinks[1] as $link) {
                    if (strpos($link, '#') === 0) {
                        $hrefs[$i][] = '?' . $u[$i] . $link;
                    } else {
                        $hrefs[$i][] = $link;
                    }
                }
                $texts[$i] = $pageLinks[2];
                $checkedLinks += count($pageLinks[1]);
            }
        }
        $hints = array();
        $i = 0;
        foreach ($hrefs as $index => $currentLinks) {
            foreach ($currentLinks as $counter => $link) {
                $parts = parse_url($link);
                switch ($parts['scheme']) {
                case 'http':
                    $status = $this->checkExternalLink($parts);
                    break;
                case 'mailto':
                    $status = 'mailto';
                    break;
                case '':
                    $status = $this->checkInternalLink($parts);
                    break;
                default:
                    $status = 'unknown';
                }
                if ($status == '200') {
                    continue;
                }
                $failure = array(
                    '400', '404', '500', 'internalfail', 'externalfail',
                    'content not found', 'file not found'
                );
                if (in_array($status, $failure)) {
                    $hints[$index]['errors'][] = array(
                        $status, $link, $texts[$index][$counter]
                    );
                    continue;
                }
                $hints[$index]['caveats'][] = array(
                    $status, $link, $texts[$index][$counter]
                );
            }
            $i++;
        }
        return $this->message($checkedLinks, $hints);
    }

    /**
     * Checks an internal link.
     *
     * @param array $test The parsed(?) URL.
     *
     * @return mixed 200 on success; otherwise an error string.
     *
     * @global array  The content of the pages.
     * @global array  The URLs of the pages.
     * @global int    The number of pages.
     * @global array  The paths of system files and folders.
     *
     * @access protected
     */
    function checkInternalLink($test)
    {
        global $c, $u, $cl, $pth;

        // link to a file
        $filename = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $test['path'];
        if (isset($test['path']) && !isset($test['query'])
            && file_exists($filename)
        ) {
            return 200;
        }

        $template = file_get_contents($pth['file']['template']); // read it

        // TODO: consider using parse_str()

        list($query) = explode('&', $test['query']);
        $pageLinks = array();
        $pageContents = array();
        $contentLength = $cl;

        preg_match('/\/([A-z]{2})\/[^\/]*/', $test['path'], $matches);
        if (XH_isLanguageFolder($matches[1])) {
            $lang = $matches[1];
        }

        if (isset($lang)) {
            $query = str_replace('/' . $lang . '/?', '', $query);
            $content = XH_readContents($lang);
            if (!$content) {
                return 'content not found';
            }
            $urls = $content['urls'];
            $pages = $content['pages'];
            $contentLength = count($pages);
        } else {
            $urls = $u;
            $pages = $c;
        }
        for ($i = 0; $i < $contentLength; $i++) {
            if ($urls[$i] == $query) {
                if (!$test['fragment']) {
                    return 200;
                }
                $pattern = '/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i';
                if (preg_match($pattern, $pages[$i])) {
                    return 200;
                }
                // check for anchor in template
                $pattern = '/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i';
                if (preg_match($pattern, $template)) {
                    return 200;
                }
            }
        }

        $parts = explode('=', $test['query']);

        $temp = array('download', '&download', '&amp;download');
        if (in_array($parts[0], $temp)) {
            if (file_exists($pth['folder']['downloads'] . $parts[1])) {
                return 200;
            } else {
                return 'file not found';
            }
        }
        $parts = explode('/', $test['path']);
        if (in_array($parts[1], $temp)) {
            if (file_exists($pth['folder']['downloads'] . $parts[2])) {
                return 200;
            } else {
                return 'file not found';
            }
        }
        return 'internalfail';
    }

    /**
     * Checks an external link and returns the status code.
     *
     * @param array $parts The parsed URL.
     *
     * @return string The status code
     *
     * @access protected
     */
    function checkExternalLink($parts)
    {
        set_time_limit(30);  // actually increase time limit
        $host = $parts['host'];
        $fh = fsockopen($parts['host'], 80, $errno, $errstr, 5);
        if ($fh) {
            $path = isset($parts['path']) ? $parts['path'] : '/';
            //if (substr($path, -1) !== '/' && substr_count($path, '.') == 0) {
            //    $path .= '/';
            //}
            if (isset($parts['query'])) {
                $path .= "?" . $parts['query'];
            }
            $request = "GET $path HTTP/1.1\r\nHost: $host\r\n"
                . "User-Agent: CMSimple_XH Link-Checker\r\n\r\n";
            fwrite($fh, $request);
            // TODO: can't we just read 9 characters in the first place?
            $response = fread($fh, 12);
            $status = substr($response, 9);
            fclose($fh);
            return $status;
        }
        return 'externalfail';
    }

    /**
     * Returns the linkcheck results.
     *
     * @param int   $checkedLinks The number of checked links.
     * @param array $hints        The errors and warnings.
     *
     * @return string The (X)HTML.
     *
     * @global array The localization of the core.
     * @global array The page headings.
     * @global array The page URLs.
     *
     * @access protected
     *
     * @todo internalization
     */
    function message($checkedLinks, $hints)
    {
        global $tx, $h, $u;

        $o = "\n" . '<p>' . $checkedLinks . $tx['link']['checked'] . '</p>' . "\n";
        if (count($hints) == 0) {
            $o .= '<p><b>' . $tx['link']['check_ok'] . '</b></p>' . "\n";
            return $o;
        }
        $o .= '<p><b>' . $tx['link']['check_errors'] . '</b></p>' . "\n";
        $o .= '<p>' . $tx['link']['check'] . '</p>' . "\n";
        foreach ($hints as $page => $problems) {
            $o .= tag('hr') . "\n\n" . '<h4>' . $tx['link']['page']
                . '<a href="?' . $u[$page] . '">' . $h[$page] . '</a></h4>' . "\n";
            if (isset($problems['errors'])) {
                $o .= '<h4>' . $tx['link']['errors'] . '</h4>' . "\n"
                    . '<ul>' . "\n";
                foreach ($problems['errors'] as $error) {
                    $o .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>'
                        . '<a href="' . $error[1] . '">' . $error[2] . '</a>'
                        . tag('br') . "\n";
                    $o .= '<b>' . $tx['link']['linked_page'] . '</b>' . $error[1]
                        . tag('br') . "\n";
                    if ((int) $error[0]) {
                        $o .= '<b>' . $tx['link']['error'] . '</b>'
                            . $tx['link']['ext_error_page'] . tag('br') . "\n"
                            . '<b>' . $tx['link']['returned_status'] . '</b>'
                            . $error[0];
                    }
                    // TODO: use a switch or elseifs
                    if ($error[0] == 'internalfail') {
                        $o .= '<b>' . $tx['link']['error'] . '</b>'
                            . $tx['link']['int_error'];
                    }
                    if ($error[0] == 'externalfail') {
                        $o .= '<b>' . $tx['link']['error'] . '</b>'
                            . $tx['link']['ext_error_domain'];
                    }
                    if ($error[0] == 'content not found') {
                        $o .= '<b>' . $tx['link']['error'] . '</b>'
                            . $tx['link']['int_error'];
                    }
                    $o .= "\n" . '</li>' . "\n";
                }
                $o .= '</ul>' . "\n" . "\n";
            }
            if (isset($problems['caveats'])) {
                $o .= '<h4>' . $tx['link']['hints'] . '</h4>' . "\n"
                    . '<ul>' . "\n";
                foreach ($problems['caveats'] as $notice) {
                    $o .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>'
                        . '<a href="' . $notice[1] . '">' . $notice[2] . '</a>'
                        . tag('br') . "\n"
                        . '<b>' . $tx['link']['linked_page'] . '</b>'
                        . $notice[1] . tag('br') . "\n";
                    if ((int) $notice[0]) {
                        if ((int) $notice[0] >= 300 && (int) $notice[0] < 400) {
                            $o .= '<b>' . $tx['link']['error'] . '</b>'
                                . $tx['link']['redirect'] . tag('br') . "\n";
                        }
                        $o .= '<b>' . $tx['link']['returned_status'] . '</b>'
                            . $notice[0] . "\n";
                    } else {
                        if ($notice[0] == 'mailto') {
                            $o .= $tx['link']['email'] . "\n";
                        } else {
                            $o .= $tx['link']['unknown'] . "\n";
                        }
                        $o .= '</li>' . "\n";
                    }
                }
                $o .= '</ul>' . "\n";
            }
        }
        return $o;
    }

}

?>
