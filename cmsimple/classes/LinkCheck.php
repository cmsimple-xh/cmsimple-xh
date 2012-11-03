<?php

/**
 * @version $Id$
 */

// FIXME: class needs cleanup

class XH_LinkCheck
{
    function XH_LinkCheck()
    {
	
    }
/**
 * collects the links
 * calls the appropriate fucntion to check each link
 * passes the results to
 *
 *
 * @global <array> $c - the cmsimple pages
 * @global <array> $u - the urls
 * @global <array> $h - the headings
 * @global <int> $cl  - the number of pages
 * @global <string> $o - the output string
 */
function check_links() {
    global $c, $u, $h, $cl, $o;
    $checkedLinks = 0;
    for ($i = 0; $i < $cl; $i++) {
        preg_match_all('/<a.*?href=["]?([^"]*)["]?.*?>(.*?)<\/a>/is', $c[$i], $pageLinks);
        if (count($pageLinks[1]) > 0) {


// First change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
            foreach ($pageLinks[1] as $link) {
                if (strpos($link, '#') === 0) {
                    $hrefs[$i][] = '?' . $u[$i] . $link;
                } else {
                    $hrefs[$i][] = $link;
                }
            }
// END first change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)


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
                    || $status == 'externalfail' || $status == 'content not found' || $status == 'file not found') {
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
 * checks internal link -  all languages
 * (requires the function read_content_file)
 *
 * @param <array> $test (parsed url)
 * @return <string> on success: '200' else 'internalfail'
 */
// Second change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
function check_internal_link($test) {
    global $c, $u, $cl, $sn, $pth, $sl, $cf, $pth;  // add $pth to globals
    if (isset($test['path']) && !isset($test['query']) // link to a file
            && file_exists(dirname($_SERVER['SCRIPT_FILENAME']).'/'.$test['path'])) {
        return 200;
    }
    $template = file_get_contents($pth['file']['template']); // read it
// END second change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)

    // consider using parse_str()

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

// Third change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
            if (preg_match('/<[^>]*[id|name]\s*=\s*"' . $test['fragment'] . '"/i', $template)) { // check for anchor in template
                return 200;
            }
// END third change for linkcheck page-internal anchors - by MD 2009-12 (CMSimple_XH 1.0)
        }
    }

    $parts = explode('=', $test['query']);

    if ($parts[0] == 'download' || $parts[0] == '&download' || $parts[0] == '&amp;download') {
        if (file_exists($pth['folder']['downloads'] . $parts[1])) {
            return 200;
        } else {
            return 'file not found';
        }
    }
    $parts = explode('/', $test['path']);
    if ($parts[1] == 'downloads' || $parts[1] == '&downloads' || $parts[1] == '&amp;downloads') {
        if (file_exists($pth['folder']['downloads'] . $parts[2])) {
            return 200;
        } else {
            return 'file not found';
        }
    }
    return 'internalfail';
}

/**
 * checks web links and returns the status code
 *
 * @param <array> $parts (parsed url)
 * @return <string> status code
 */
function check_external_link($parts) {
    set_time_limit(30);
    $host = $parts['host'];
    $fh = fsockopen($parts['host'], 80, $errno, $errstr, 5);
    if ($fh) {
        $path = isset($parts['path']) ? $parts['path'] : '/';  // LM CMSimple_XH 1.1
        //if (substr($path, -1) !== '/' && substr_count($path, '.') == 0) {
        //    $path .= '/';
        //}
        if (isset($parts['query'])) {
            $path .= "?" . $parts['query'];
        }
        fwrite($fh, "GET " . $path . " HTTP/1.1\r\nHost: " . $host . "\r\nUser-Agent: CMSimple_XH Link-Checker\r\n\r\n");
        $response = fread($fh, 12);
        $status = substr($response, 9);
        fclose($fh);
        return($status);
    }
    return 'externalfail';
}

// new linkcheck - by MD 2009-08 (CMSimple_XH beta3)
/**
 * prepares the html output for the linkcheck results
 *
 * @todo internalization
 *
 * @global <array> $tx
 * @global <array> $h
 * @global <array> $u
 * @param <int> $checkedLinks - number of checked links
 * @param <array> $hints - the errors an warnings
 * @return <string>
 */
function linkcheck_message($checkedLinks, $hints) {
    global $tx, $h, $u;
    $html = "\n" . '<p>' . $checkedLinks . $tx['link']['checked'] . '</p>' . "\n";  // LM CMSimple_XH 1.1
    if (count($hints) == 0) {
        $html .= '<p><b>' . $tx['link']['check_ok'] . '</b></p>' . "\n";
        return $html;
    }
    $html .= '<p><b>' . $tx['link']['check_errors'] . '</b></p>' . "\n";
    $html .= '<p>' . $tx['link']['check'] . '</p>' . "\n";
    foreach ($hints as $page => $problems) {
        $html .= tag('hr') . "\n\n" . '<h4>' . $tx['link']['page'] . '<a href="?' . $u[$page] . '">' . $h[$page] . '</a></h4>' . "\n";
        if (isset($problems['errors'])) {
            $html .= '<h4>' . $tx['link']['errors'] . '</h4>' . "\n" . '<ul>' . "\n";
            foreach ($problems['errors'] as $error) {
                $html .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b><a href="' . $error[1] . '">' . $error[2] . '</a>' . tag('br') . "\n";
                $html .= '<b>' . $tx['link']['linked_page'] . '</b>' . $error[1] . tag('br') . "\n";
                if ((int) $error[0]) {
                    $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['ext_error_page'] . tag('br') . "\n";
                    $html .= '<b>' . $tx['link']['returned_status'] . '</b>' . $error[0];
                }
                if ($error[0] == 'internalfail') {
                    $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['int_error'];
                }
                if ($error[0] == 'externalfail') {
                    $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['ext_error_domain'];
                }
                if ($error[0] == 'content not found') {
                    $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['int_error'];
                }
                $html .= "\n" . '</li>' . "\n";
            }
            $html .= '</ul>' . "\n" . "\n";
        }
        if (isset($problems['caveats'])) {
            $html .= '<h4>' . $tx['link']['hints'] . '</h4>' . "\n" . '<ul>' . "\n";
            foreach ($problems['caveats'] as $notice) {
                $html .= '<li>' . "\n" . '<b>' . $tx['link']['link'] . '</b>' . '<a href="' . $notice[1] . '">' . $notice[2] . '</a>' . tag('br') . "\n";
                $html .= '<b>' . $tx['link']['linked_page'] . '</b>' . $notice[1] . tag('br') . "\n";
                if ((int) $notice[0]) {
                    if ((int) $notice[0] >= 300 && (int) $notice[0] < 400) {
                        $html .= '<b>' . $tx['link']['error'] . '</b>' . $tx['link']['redirect'] . tag('br') . "\n";
                    }
                    $html .= '<b>' . $tx['link']['returned_status'] . '</b>' . $notice[0] . "\n";
                } else {
                    if ($notice[0] == 'mailto') {
                        $html .= $tx['link']['email'] . "\n";
                    } else {
                        $html .= $tx['link']['unknown'] . "\n";
                    }
                    $html .= '</li>' . "\n";
                }
            }
            $html .= '</ul>' . "\n";
        }
    }
    return $html;
}


// FIXME: this function should be merged with rfc()
/**
 *
 * @global <array> $cf
 * @param <string> $path
 * @return <array> - contains <array> $urls, <array> $pages, <array> $headings, <array> $levels
 */
function read_content_file($path) {

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
    preg_match_all($pattern, $content, $matches); // LM CMSimple_XH 1.1

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
        $url = uenc($heading); //in cms.php: handles $tx['urichar']
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
