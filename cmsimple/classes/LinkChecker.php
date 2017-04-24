<?php

/**
 * The link checker.
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

namespace XH;

/**
 * The link checker.
 *
 * $hints[$pageIndex][$type][$n] = $link, where <var>$type</var> is "errors"
 * or "caveats".
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 */
class LinkChecker
{
    /**
     * Prepares the link check.
     *
     * @return string HTML
     *
     * @global string The script name.
     * @global array  The paths of system files and folders.
     * @global array  The localization of the core.
     */
    public function prepare()
    {
        global $sn, $pth, $tx;

        $url = $sn . '?&amp;xh_do_validate';
        $o = '<div id="xh_linkchecker" data-url="' . $url . '">'
            . '<img src="' . $pth['folder']['corestyle']
            . 'ajax-loader-bar.gif" width="128" height="15" alt="'
            . $tx['link']['checking'] . '">'
            . '</div>';
        return $o;
    }

    /**
     * Handles the actual link check request.
     *
     * @return void
     */
    public function doCheck()
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo $this->checkLinks();
        exit;
    }

    /**
     * Checks all links and returns the result view.
     *
     * @return string HTML
     */
    public function checkLinks()
    {
        $links = $this->gatherLinks();
        $failure = array(
            400, 404, 500, Link::STATUS_INTERNALFAIL,
            Link::STATUS_EXTERNALFAIL, Link::STATUS_CONTENT_NOT_FOUND,
            Link::STATUS_FILE_NOT_FOUND, Link::STATUS_ANCHOR_MISSING
        );
        $hints = array();
        foreach ($links as $index => $currentLinks) {
            foreach ($currentLinks as $link) {
                $this->determineLinkStatus($link);
                if ($link->getStatus() !== 200) {
                    $type = in_array($link->getStatus(), $failure)
                        ? 'errors' : 'caveats';
                    $hints[$index][$type][] = $link;
                }
            }
        }
        return $this->message($this->countLinks($links), $hints);
    }

    /**
     * Gathers all links in the content and returns the result.
     *
     * @return array
     *
     * @global array The page contents.
     * @global array The page URLs.
     * @global int   The number of pages.
     */
    private function gatherLinks()
    {
        global $c, $u, $cl;

        $links = array();
        for ($i = 0; $i < $cl; $i++) {
            $links[$i] = array();
            $pattern = '/<a.*?href=["]?([^"]*)["]?.*?>(.*?)<\/a>/is';
            preg_match_all($pattern, $c[$i], $pageLinks);
            if (count($pageLinks[1]) > 0) {
                foreach ($pageLinks[1] as $j => $url) {
                    $url = str_replace('&amp;', '&', $url);
                    if (strpos($url, '#') === 0) {
                        $url = '?' . $u[$i] . $url;
                    }
                    $text = $pageLinks[2][$j];
                    $links[$i][] = new Link($url, $text);
                }
            }
        }
        return $links;
    }

    /**
     * Returns the number of the links.
     *
     * @param array $links An array of page links.
     *
     * @return int
     */
    private function countLinks(array $links)
    {
        return array_sum(array_map('count', $links));
    }

    /**
     * Determines the status of a link.
     *
     * @param Link $link A link
     *
     * @return void
     */
    public function determineLinkStatus(Link $link)
    {
        $parts = parse_url($link->getURL());
        if (isset($parts['scheme'])) {
            switch ($parts['scheme']) {
                case 'http':
                    $status = $this->checkExternalLink($parts);
                    break;
                case 'mailto':
                    $status = Link::STATUS_MAILTO;
                    break;
                case '':
                    $status = $this->checkInternalLink($parts);
                    break;
                default:
                    $status = Link::STATUS_UNKNOWN;
            }
        } else {
            $status = $this->checkInternalLink($parts);
        }
        $link->setStatus($status);
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
     */
    private function checkInternalLink(array $test)
    {
        global $c, $u, $cl, $pth, $cf;

        if (isset($test['path']) && !isset($test['query'])) {
            $filename = urldecode($test['path']);
            if (is_file($filename) && is_readable($filename)) {
                return 200;
            }
        }
        if (!isset($test['query'])) {
            return Link::STATUS_INTERNALFAIL;
        }

        list($query) = explode('&', $test['query']);
        if ($query === 'sitemap'
            || $query === 'mailform' && $cf['mailform']['email'] !== ''
        ) {
            return 200;
        }
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
                return Link::STATUS_CONTENT_NOT_FOUND;
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
                    return 200;
                }
                $pattern = '/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i';
                if (preg_match($pattern, $pages[$i])) {
                    return 200;
                }
                // check for anchor in template
                $template = file_get_contents($pth['file']['template']);
                $pattern = '/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i';
                if (preg_match($pattern, $template)) {
                    return 200;
                }
                return Link::STATUS_ANCHOR_MISSING;
            }
        }
        $parts = explode('=', $test['query']);
        $temp = array('download', '&download', '&amp;download');
        if (in_array($parts[0], $temp)) {
            if (file_exists($pth['folder']['downloads'] . $parts[1])) {
                return 200;
            } else {
                return Link::STATUS_FILE_NOT_FOUND;
            }
        }
        return Link::STATUS_INTERNALFAIL;
    }

    /**
     * Checks an external link and returns the status code.
     *
     * @param array $parts URL parts.
     *
     * @return string
     */
    private function checkExternalLink(array $parts)
    {
        set_time_limit(30);
        $path = isset($parts['path']) ? $parts['path'] : '/';
        if (isset($parts['query'])) {
            $path .= "?" . $parts['query'];
        }
        $status = $this->makeHeadRequest($parts['host'], $path);
        return ($status !== false) ? $status : Link::STATUS_EXTERNALFAIL;
    }

    /**
     * Makes a head request and returns the response status code, FALSE if the
     * request failed.
     *
     * @param string $host A host name.
     * @param string $path An absolute path.
     *
     * @return int
     */
    protected function makeHeadRequest($host, $path)
    {
        $errno = $errstr = null;
        $socket = fsockopen($host, 80, $errno, $errstr, 5);
        if ($socket) {
            $request = "HEAD $path HTTP/1.1\r\nHost: $host\r\n"
                . "User-Agent: CMSimple_XH Link-Checker\r\n\r\n";
            fwrite($socket, $request);
            $response = fread($socket, 12);
            fclose($socket);
            $status = substr($response, 9);
            return (int) $status;
        } else {
            return false;
        }
    }

    /**
     * Returns the report of a single error.
     *
     * @param Link $link A link.
     *
     * @return string HTML
     */
    public function reportError(Link $link)
    {
        global $tx;

        $o = '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>'
            . '<a href="' . $link->getURL() . '">' . $link->getText() . '</a>'
            . '<br>' . "\n"
            . '<b>' . $tx['link']['linked_page'] . '</b>' . $link->getURL()
            . '<br>' . "\n"
            . '<b>' . $tx['link']['error'] . '</b>';
        switch ($link->getStatus()) {
            case Link::STATUS_INTERNALFAIL:
            case Link::STATUS_CONTENT_NOT_FOUND:
                $o .= $tx['link']['int_error'];
                break;
            case Link::STATUS_ANCHOR_MISSING:
                $o .= $tx['link']['int_error_fragment'];
                break;
            case Link::STATUS_EXTERNALFAIL:
                $o .= $tx['link']['ext_error_domain'];
                break;
            default:
                $o .= $tx['link']['ext_error_page'] . '<br>' . "\n"
                    . '<b>' . $tx['link']['returned_status'] . '</b>'
                    . $link->getStatus();
        }
        $o .= "\n" . '</li>' . "\n";
        return $o;
    }

    /**
     * Returns the report of a single notice.
     *
     * @param Link $link A link.
     *
     * @return string HTML
     */
    public function reportNotice(Link $link)
    {
        global $tx;

        $o = '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>'
            . '<a href="' . $link->getURL() . '">' . $link->getText() . '</a>'
            . '<br>' . "\n"
            . '<b>' . $tx['link']['linked_page'] . '</b>'
            . $link->getURL() . '<br>' . "\n";
        switch ($link->getStatus()) {
            case Link::STATUS_MAILTO:
                $o .= $tx['link']['email'] . "\n";
                break;
            case Link::STATUS_UNKNOWN:
                $o .= $tx['link'][Link::STATUS_UNKNOWN] . "\n";
                break;
            default:
                if ($link->getStatus() >= 300 && $link->getStatus() < 400) {
                    $o .= '<b>' . $tx['link']['error'] . '</b>'
                        . $tx['link']['redirect'] . '<br>' . "\n";
                }
                $o .= '<b>' . $tx['link']['returned_status'] . '</b>'
                    . $link->getStatus() . "\n";
        }
        return $o;
    }

    /**
     * Returns the linkcheck results.
     *
     * @param int   $checkedLinks The number of checked links.
     * @param array $hints        The errors and warnings.
     *
     * @return string HTML
     *
     * @global array The localization of the core.
     * @global array The page headings.
     * @global array The page URLs.
     */
    public function message($checkedLinks, array $hints)
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
            $o .= '<hr>' . "\n\n" . '<h4>' . $tx['link']['page']
                . '<a href="?' . $u[$page] . '">' . $h[$page] . '</a></h4>' . "\n";
            if (isset($problems['errors'])) {
                $o .= '<h5>' . $tx['link']['errors'] . '</h5>' . "\n"
                    . '<ul>' . "\n";
                foreach ($problems['errors'] as $link) {
                    $o .= $this->reportError($link);
                }
                $o .= '</ul>' . "\n" . "\n";
            }
            if (isset($problems['caveats'])) {
                $o .= '<h5>' . $tx['link']['hints'] . '</h5>' . "\n"
                    . '<ul>' . "\n";
                foreach ($problems['caveats'] as $link) {
                    $o .= $this->reportNotice($link);
                }
                $o .= '</ul>' . "\n";
            }
        }
        return $o;
    }
}
