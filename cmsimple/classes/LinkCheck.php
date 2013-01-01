<?php

/**
 * The link checker.
 *
 * @package     XH
 * @copyright   1999-2009 <http://cmsimple.org/>
 * @copyright   2009-2012 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version     $CMSIMPLE_XH_VERSION$, $CMSIMPLE_XH_BUILD$
 * @version     $Id$
 * @link        http://cmsimple-xh.org/
 */

/**
 * The link checker.
 *
 * @todo    needs fixing!
 *
 * @package XH
 * @access  public
 */
class XH_LinkCheck
{
    /**
     *
     */
    function XH_LinkCheck()
    {

    }

    /**
     * Checks all links and returns the result view.     *
     *
     * @global array  The page contents.
     * @global array  The page URLs.
     * @global int  The number of pages.
     * @return string  The (X)HTML.
     */
    function check_links()
    {
        global $c, $u, $cl;

        $checkedLinks = 0;
        for ($i = 0; $i < $cl; $i++) {
            preg_match_all('/<a.*?href=["]?([^"]*)["]?.*?>(.*?)<\/a>/is', $c[$i], $pageLinks);
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
                case 'http': $status = $this->check_external_link($parts);
                    break;
                case 'mailto': $status = 'mailto';
                    break;
                case '': $status = $this->check_internal_link($parts);
                    break;
                default: $status = 'unknown';
                }
                if ($status == '200') {
                    continue;
                }
                if ($status == '400' || $status == '404'
                    || $status == '500' || $status == 'internalfail'
                    || $status == 'externalfail' || $status == 'content not found'
                    || $status == 'file not found')
                {
                    $hints[$index]['errors'][] = array($status, $link, $texts[$index][$counter]);
                    continue;
                }
                $hints[$index]['caveats'][] = array($status, $link, $texts[$index][$counter]);
            }
            $i++;
        }
        return $this->linkcheck_message($checkedLinks, $hints);
    }

    /**
     * Checks an internal link.
     *
     * @param  array $test  The parsed(?) URL.
     * @return mixed  200 on success; otherwise an error string.
     */
    function check_internal_link($test)
    {
        global $c, $u, $cl, $sn, $pth, $sl, $cf, $pth;

         // link to a file
        if (isset($test['path']) && !isset($test['query'])
            && file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $test['path']))
        {
            return 200;
        }

        $template = file_get_contents($pth['file']['template']); // read it

        // TODO: consider using parse_str()

        list($query) = explode('&', $test['query']);
        $pageLinks = array();
        $pageContents = array();
        $contentLength = $cl;

        preg_match('/\/([A-z]{2})\/[^\/]*/', $test['path'], $lang);
        $lang = $lang[1];

        if (isset($test['path'])) {
            $query = str_replace('/' . $lang . '/?', '', $query);
            $content = $this->read_content_file($lang);
            if (!$content) {
                return 'content not found';
            }
            $urls = $content[0];
            $pages = $content[1];
            $contentLength = count($urls);
        } else {
            $urls = $u;
            $pages = $c;
        }
        for ($i = 0; $i < $contentLength; $i++) {
            if ($urls[$i] == $query) {
                if (!$test['fragment']) {
                    return 200;
                }
                if (preg_match('/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i', $pages[$i])) {
                    return 200;
                }
                // check for anchor in template
                if (preg_match('/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i', $template)) {
                    return 200;
                }
            }
        }

        $parts = explode('=', $test['query']);

        if ($parts[0] == 'download' || $parts[0] == '&download'
            || $parts[0] == '&amp;download')
        {
            if (file_exists($pth['folder']['downloads'] . $parts[1])) {
                return 200;
            } else {
                return 'file not found';
            }
        }
        $parts = explode('/', $test['path']);
        if ($parts[1] == 'downloads' || $parts[1] == '&downloads'
            || $parts[1] == '&amp;downloads')
        {
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
     * @param  array $parts  The parsed URL.
     * @return string  The status code
     */
    function check_external_link($parts) {
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
            fwrite($fh, "GET " . $path . " HTTP/1.1\r\nHost: " . $host . "\r\n"
                   . "User-Agent: CMSimple_XH Link-Checker\r\n\r\n");
            // TODO: can't we just read 9 characters in the first place?
            $response = fread($fh, 12);
            $status = substr($response, 9);
            fclose($fh);
            return $status;
        }
        return 'externalfail';
    }

    /**
     * Returns thethe linkcheck results.
     *
     * @todo internalization
     *
     * @global array  The localization.
     * @global array  The page headings
     * @global array  The page URLs.
     * @param  int $checkedLinks  The number of checked links.
     * @param  array $hints  The errors and warnings.
     * @return string  The (X)HTML.
     */
    function linkcheck_message($checkedLinks, $hints)
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
                $o .= '<h4>' . $tx['link']['errors'] . '</h4>' . "\n" . '<ul>' . "\n";
                foreach ($problems['errors'] as $error) {
                    $o .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>'
                        . '<a href="' . $error[1] . '">' . $error[2] . '</a>' . tag('br') . "\n";
                    $o .= '<b>' . $tx['link']['linked_page'] . '</b>' . $error[1] . tag('br') . "\n";
                    if ((int) $error[0]) {
                        $o .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['ext_error_page'] . tag('br') . "\n";
                        $o .= '<b>' . $tx['link']['returned_status'] . '</b>' . $error[0];
                    }
                    // TODO: use a switch or elseifs
                    if ($error[0] == 'internalfail') {
                        $o .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['int_error'];
                    }
                    if ($error[0] == 'externalfail') {
                        $o .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['ext_error_domain'];
                    }
                    if ($error[0] == 'content not found') {
                        $o .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['int_error'];
                    }
                    $o .= "\n" . '</li>' . "\n";
                }
                $o .= '</ul>' . "\n" . "\n";
            }
            if (isset($problems['caveats'])) {
                $o .= '<h4>' . $tx['link']['hints'] . '</h4>' . "\n" . '<ul>' . "\n";
                foreach ($problems['caveats'] as $notice) {
                    $o .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>'
                        . '<a href="' . $notice[1] . '">' . $notice[2] . '</a>' . tag('br') . "\n";
                    $o .= '<b>' . $tx['link']['linked_page'] . '</b>' . $notice[1] . tag('br') . "\n";
                    if ((int) $notice[0]) {
                        if ((int) $notice[0] >= 300 && (int) $notice[0] < 400) {
                            $o .= '<b>' . $tx['link']['error'] . '</b>'
                                . $tx['link']['redirect'] . tag('br') . "\n";
                        }
                        $o .= '<b>' . $tx['link']['returned_status'] . '</b>' . $notice[0] . "\n";
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

    /**
     * Read a content file and returns the essential information.
     *
     * @todo   Use the new rfc() instead.
     * @global array  The config options.
     * @global string  The current language.
     * @param  string $path  The file path.
     * @return array  The page URLs, contents, headings and levels.
     */
    function read_content_file($path)
    {
        global $cf, $sl;

        $path = basename($path);
        if ($sl == $cf['language']['default']) {
            $path = './' . $path;
        } else {
            $path = '../' . $path;
        }
        $sep = $cf['uri']['seperator'];
        $pattern = '/<h([1-' . $cf['menu']['levels'] . '])[^>]*>(.*)<\/h/i';

        $content = file_get_contents($path . '/content/content.htm');
        if (!$content) {
            return false;
        }
        preg_match_all($pattern, $content, $matches);

        $headings = array();
        $levels = array();
        $urls = array();

        if (count($matches[0]) == 0) {
            return;
        }
        $ancestors = array();
        foreach ($matches[1] as $level) {
            $levels[] = (int) $level;
        }
        $i = 0;
        foreach ($matches[2] as $chapter) {
            $heading = trim(strip_tags($chapter));
            $url = uenc($heading);
            $headings[] = $heading;
            $level = $levels[$i];
            $ancestors[$level] = $url;
            $myself = array_slice($ancestors, 0, $level);
            $urls[] = implode($sep, $myself);
            $i++;
        }
        $pages = preg_split($pattern, $content);
        $pages = array_slice($pages, 1); // $pages[0] is the header part - drop it!
        return array($urls, $pages, $headings, $levels);
    }
}

?>
