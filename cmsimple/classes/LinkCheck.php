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
 * Internally <b>status codes</b> are used, which are either HTTP response codes,
 * or one of 'internalfail', 'externalfail', 'content not found',
 * 'file not found' or 'anchor missing'.
 *
 * Details about errors and notices are stored as a triple of <var>status</var>,
 * <var>URL</var> and <var>text</var>, where <var>text</var> actually is the
 * contents of the A element.
 *
 * $hints[$pageIndex][$type][$n] = $details, where <var>$type</var> is "errors"
 * or "caveats".
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class XH_LinkCheck
{
    /**
     * Checks all links and returns the result view.     *
     *
     * @return string The (X)HTML.
     *
     * @access public
     */
    function checkLinks()
    {
        list($hrefs, $texts, $checkedLinks) = $this->gatherLinks();
        $failure = array(
            '400', '404', '500', 'internalfail', 'externalfail',
            'content not found', 'file not found', 'anchor missing'
        );
        $hints = array();
        foreach ($hrefs as $index => $currentLinks) {
            foreach ($currentLinks as $counter => $link) {
                $status = $this->linkStatus($link);
                if ($status === '200') {
                    continue;
                }
                $details = array($status, $link, $texts[$index][$counter]);
                $type = in_array($status, $failure) ? 'errors' : 'caveats';
                $hints[$index][$type][] = $details;
            }
        }
        return $this->message($checkedLinks, $hints);
    }

    /**
     * Gathers all links in the content and returns the result.
     *
     * @return array
     *
     * @global array The page contents.
     * @global array The page URLs.
     * @global int   The number of pages.
     *
     * @access protected
     */
    function gatherLinks()
    {
        global $c, $u, $cl;

        $checkedLinks = 0;
        $hrefs = array();
        $texts = array();
        for ($i = 0; $i < $cl; $i++) {
            $hrefs[$i] = array();
            $texts[$i] = array();
            $pattern = '/<a.*?href=["]?([^"]*)["]?.*?>(.*?)<\/a>/is';
            preg_match_all($pattern, $c[$i], $pageLinks);
            if (count($pageLinks[1]) > 0) {
                foreach ($pageLinks[1] as $link) {
                    $link = str_replace('&amp;', '&', $link);
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
        return array($hrefs, $texts, $checkedLinks);
    }

    /**
     * Returns the status of a link.
     *
     * @param string $link A URL.
     *
     * @return string
     *
     * @access protected
     */
    function linkStatus($link)
    {
        $parts = parse_url($link);
        if (isset($parts['scheme'])) {
            switch ($parts['scheme']) {
            case 'http':
            case 'https':
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
        } else {
            $status = $this->checkInternalLink($parts);
        }
        return $status;
    }

    /**
     * Checks an internal link and returns the link status.
     *
     * @param array $test URL parts.
     *
     * @return string
     *
     * @global array The content of the pages.
     * @global array The URLs of the pages.
     * @global int   The number of pages.
     * @global array The paths of system files and folders.
     * @global array The configuration of the core.
     *
     * @access protected
     */
    function checkInternalLink($test)
    {
        global $c, $u, $cl, $pth, $cf;

        if (isset($test['path']) && !isset($test['query'])) {
            $filename = $test['path'];
            if (is_file($filename) && is_readable($filename)) {
                return '200';
            }
        }
        if (!isset($test['query'])) {
            return 'internalfail';
        }

        list($query) = explode('&', $test['query']);
        if ($query === 'sitemap'
            || $query === 'mailform' && $cf['mailform']['email'] !== ''
        ) {
            return '200';
        }
        $pageLinks = array();
        $pageContents = array();
        $contentLength = $cl;
        if (isset($test['path'])
            && preg_match('/\/([A-z]{2})\/[^\/]*/', $test['path'], $matches)
            && XH_isLanguageFolder($matches[1])
        ) {
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
            if ($urls[$i] === $query) {
                if (!isset($test['fragment'])) {
                    return '200';
                }
                $pattern = '/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i';
                if (preg_match($pattern, $pages[$i])) {
                    return '200';
                }
                // check for anchor in template
                $template = file_get_contents($pth['file']['template']);
                $pattern = '/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i';
                if (preg_match($pattern, $template)) {
                    return '200';
                }
                return 'anchor missing';
            }
        }
        $parts = explode('=', $test['query']);
        $temp = array('download', '&download', '&amp;download');
        if (in_array($parts[0], $temp)) {
            if (file_exists($pth['folder']['downloads'] . $parts[1])) {
                return '200';
            } else {
                return 'file not found';
            }
        }
        return 'internalfail';
    }

    /**
     * Checks an external link and returns the status code.
     *
     * @param array $parts URL parts.
     *
     * @return string
     *
     * @access protected
     */
    function checkExternalLink($parts)
    {
        set_time_limit(30);
        $host = $parts['host'];
        $fh = fsockopen($parts['host'], 80, $errno, $errstr, 5);
        if ($fh) {
            $path = isset($parts['path']) ? $parts['path'] : '/';
            if (isset($parts['query'])) {
                $path .= "?" . $parts['query'];
            }
            $request = "GET $path HTTP/1.1\r\nHost: $host\r\n"
                . "User-Agent: CMSimple_XH Link-Checker\r\n\r\n";
            fwrite($fh, $request);
            $response = fread($fh, 12);
            fclose($fh);
            $status = substr($response, 9);
            return $status;
        }
        return 'externalfail';
    }

    /**
     * Returns the report of a single error.
     *
     * @param array $error Link detail triple.
     *
     * @return (X)HTML.
     *
     * @access protected
     */
    function reportError($error)
    {
        global $tx;

        $o = '';
        $o .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>'
            . '<a href="' . $error[1] . '">' . $error[2] . '</a>'
            . tag('br') . "\n";
        $o .= '<b>' . $tx['link']['linked_page'] . '</b>' . $error[1]
            . tag('br') . "\n";
        if (is_numeric($error[0])) {
            $o .= '<b>' . $tx['link']['error'] . '</b>'
                . $tx['link']['ext_error_page'] . tag('br') . "\n"
                . '<b>' . $tx['link']['returned_status'] . '</b>'
                . $error[0];
        } elseif ($error[0] == 'internalfail') {
            $o .= '<b>' . $tx['link']['error'] . '</b>'
                . $tx['link']['int_error'];
        } elseif ($error[0] == 'anchor missing') {
            $o .= '<b>' . $tx['link']['error'] . '</b>'
                . $tx['link']['int_error_fragment'];
        } elseif ($error[0] == 'externalfail') {
            $o .= '<b>' . $tx['link']['error'] . '</b>'
                . $tx['link']['ext_error_domain'];
        } elseif ($error[0] == 'content not found') {
            $o .= '<b>' . $tx['link']['error'] . '</b>'
                . $tx['link']['int_error'];
        }
        $o .= "\n" . '</li>' . "\n";
        return $o;
    }

    /**
     * Returns the report of a single notice.
     *
     * @param array $notice Link detail triple.
     *
     * @return (X)HTML.
     *
     * @access protected
     */
    function reportNotice($notice)
    {
        global $tx;

        $o = '';
        $o .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>'
            . '<a href="' . $notice[1] . '">' . $notice[2] . '</a>'
            . tag('br') . "\n"
            . '<b>' . $tx['link']['linked_page'] . '</b>'
            . $notice[1] . tag('br') . "\n";
        if (is_numeric($notice[0])) {
            if ((int) $notice[0] >= 300 && (int) $notice[0] < 400) {
                $o .= '<b>' . $tx['link']['error'] . '</b>'
                    . $tx['link']['redirect'] . tag('br') . "\n";
            }
            $o .= '<b>' . $tx['link']['returned_status'] . '</b>'
                . $notice[0] . "\n";
        } else {
            if ($notice[0] === 'mailto') {
                $o .= $tx['link']['email'] . "\n";
            } else {
                $o .= $tx['link']['unknown'] . "\n";
            }
            $o .= '</li>' . "\n";
        }
        return $o;
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
     */
    function message($checkedLinks, $hints)
    {
        global $tx, $h, $u;

        $key = 'checked' . XH_numberSuffix($checkedLinks);
        $text = sprintf($tx['link'][$key], $checkedLinks);
        $o = "\n" . '<p>' . $text . '</p>' . "\n";
        if (count($hints) === 0) {
            $o .= '<p><b>' . $tx['link']['check_ok'] . '</b></p>' . "\n";
            return $o;
        }
        $o .= '<p><b>' . $tx['link']['check_errors'] . '</b></p>' . "\n";
        $o .= '<p>' . $tx['link']['check'] . '</p>' . "\n";
        foreach ($hints as $page => $problems) {
            $o .= tag('hr') . "\n\n" . '<h4>' . $tx['link']['page']
                . '<a href="?' . $u[$page] . '">' . $h[$page] . '</a></h4>' . "\n";
            if (isset($problems['errors'])) {
                $o .= '<h5>' . $tx['link']['errors'] . '</h5>' . "\n"
                    . '<ul>' . "\n";
                foreach ($problems['errors'] as $error) {
                    $o .= $this->reportError($error);
                }
                $o .= '</ul>' . "\n" . "\n";
            }
            if (isset($problems['caveats'])) {
                $o .= '<h5>' . $tx['link']['hints'] . '</h5>' . "\n"
                    . '<ul>' . "\n";
                foreach ($problems['caveats'] as $notice) {
                    $o .= $this->reportNotice($notice);
                }
                $o .= '</ul>' . "\n";
            }
        }
        return $o;
    }
}

?>
