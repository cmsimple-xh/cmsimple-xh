<?php

namespace XH;

/**
 * The link checker.
 *
 * $hints[$pageIndex][$type][$n] = $link, where <var>$type</var> is "errors"
 * or "caveats".
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */
class LinkChecker
{
    /**
     * Prepares the link check.
     *
     * @return string HTML
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
            400, 403, 404, 405, 410, 500, Link::STATUS_INTERNALFAIL,
            Link::STATUS_EXTERNALFAIL, Link::STATUS_CONTENT_NOT_FOUND,
            Link::STATUS_FILE_NOT_FOUND, Link::STATUS_ANCHOR_MISSING
        );
        $hints = array();
        foreach ($links as $index => $currentLinks) {
            foreach ($currentLinks as $link) {
                $this->determineLinkStatus($link);
                if (($link->getStatus() !== 200)
                    && ($link->getStatus() !== Link::STATUS_NOT_CHECKED)
                ) {
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
        global $cf;

        $parts = parse_url($link->getURL());
        if (isset($parts['scheme'])) {
            switch ($parts['scheme']) {
                case 'http':
                case 'https':
                    $status = $this->checkExternalLink($parts);
                    break;
                case 'mailto':
                    if (!empty($cf['link']['mailto'])) {
                        $status = Link::STATUS_MAILTO;
                    } else {
                        $status = Link::STATUS_NOT_CHECKED;
                    }
                    break;
                case 'tel':
                    if (!empty($cf['link']['tel'])) {
                        $status = Link::STATUS_TEL;
                    } else {
                        $status = Link::STATUS_NOT_CHECKED;
                    }
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
     * @return int
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
            if ($query === '' || $urls[$i] === $query) {
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
     * @return int
     */
    private function checkExternalLink(array $parts)
    {
        set_time_limit(30);
        $path = isset($parts['path']) ? $parts['path'] : '/';
        if (isset($parts['query'])) {
            $path .= "?" . $parts['query'];
        }
        $status = $this->makeHeadRequest($parts['scheme'], $parts['host'], $path);
        return ($status !== false) ? $status : Link::STATUS_EXTERNALFAIL;
    }

    /**
     * Makes a head request and returns the response status code, FALSE if the
     * request failed.
     *
     * @param string $scheme http(s).
     * @param string $host A host name.
     * @param string $path An absolute path.
     *
     * @return int|false
     */
    protected function makeHeadRequest($scheme, $host, $path)
    {
        global $cf;

        $url = $scheme . '://' . $host . $path;
        $timeout = 6;
        $connect_timeout = 5;
        $maxredir = (int) $cf['link']['redir'];
        $agent = 'CMSimple_XH Link-Checker';

        if (extension_loaded('curl')) {
            $ch = curl_init();
            $options = array(
                CURLOPT_URL             => $url,
                CURLOPT_HEADER          => true,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_NOBODY          => true,
                CURLOPT_USERAGENT       => $agent,
                CURLOPT_TIMEOUT         => $timeout,
                CURLOPT_CONNECTTIMEOUT  => $connect_timeout,
                CURLOPT_FRESH_CONNECT   => true
            );
            if ($maxredir > 0) {
                $options[CURLOPT_FOLLOWLOCATION] = true;
                $options[CURLOPT_MAXREDIRS]      = $maxredir;
            }
            curl_setopt_array($ch, $options);
            if (curl_exec($ch) !== false) {
                $headers = curl_getinfo($ch);
            }
            curl_close($ch);
            if (!empty($headers['http_code'])) {
                return (int) $headers['http_code'];
            }
        }
        // alternative to cURL
        if (function_exists('get_headers')) {
            $context = stream_context_create(
                array(
                    'http' => array(
                        'method'        => 'HEAD',
                        'timeout'       => $timeout,
                        'max_redirects' => $maxredir + 1,
                        'user_agent'    => $agent
                    )
                )
            );
            $headers = get_headers($url, 1, $context);
            $status = array();
            for ($i = 0; $i <= $maxredir; $i++) {
                if (!empty($headers[$i])) {
                    $headers_tmp = $headers[$i];
                } else {
                    break;
                }
            }
            preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#i', $headers_tmp, $status);
            if (!empty($status[1])) {
                return (int) $status[1];
            }
        }
        return false;
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
            . '<a target="_blank" href="' . $link->getURL() . '">' . $link->getText() . '</a>'
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
            . '<a target="_blank" href="' . $link->getURL() . '">' . $link->getText() . '</a>'
            . '<br>' . "\n"
            . '<b>' . $tx['link']['linked_page'] . '</b>'
            . $link->getURL() . '<br>' . "\n";
        switch ($link->getStatus()) {
            case Link::STATUS_MAILTO:
                $o .= $tx['link']['email'] . "\n";
                break;
            case Link::STATUS_TEL:
                $o .= $tx['link']['tel'] . "\n";
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
            $o .= '<hr>' . "\n\n" . '<h2>' . $tx['link']['page']
                . '<a href="?' . $u[$page] . '">' . $h[$page] . '</a></h2>' . "\n";
            if (isset($problems['errors'])) {
                $o .= '<h3>' . $tx['link']['errors'] . '</h3>' . "\n"
                    . '<ul>' . "\n";
                foreach ($problems['errors'] as $link) {
                    $o .= $this->reportError($link);
                }
                $o .= '</ul>' . "\n" . "\n";
            }
            if (isset($problems['caveats'])) {
                $o .= '<h3>' . $tx['link']['hints'] . '</h3>' . "\n"
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
