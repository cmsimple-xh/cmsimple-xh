<?php

namespace XH;

/**
 * The system info.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2025 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.8.1
 */
class SystemInfo
{
    public function render(): string
    {
        global $pth, $cf, $tx, $sn;

        $o = '<h2>' . $tx['sysinfo']['version'] . '</h2>' . "\n";
        $o .= '<ul>' . "\n" . '<li>' . CMSIMPLE_XH_VERSION . '&nbsp;&nbsp;Released: '
            . CMSIMPLE_XH_DATE . '</li>' . "\n" . '</ul>' . "\n";

        $o .= '<h2>' . $tx['sysinfo']['plugins'] . '</h2>' . "\n";

        $o .= '<ul>' . "\n";
        foreach ($this->plugins() as $temp) {
            $o .= '<li>' . ucfirst($temp) . ' ' . XH_pluginVersion($temp) . '</li>'
                . "\n";
        }
        $o .= '</ul>' . "\n";

        $serverSoftware = !empty($_SERVER['SERVER_SOFTWARE'])
            ? $_SERVER['SERVER_SOFTWARE']
            : $tx['sysinfo']['unknown'];
        $o .= '<h2>' . $tx['sysinfo']['webserver'] . '</h2>' . "\n"
            . '<ul>' . "\n" . '<li>' . $serverSoftware . '</li>' . "\n"
            . '</ul>' . "\n";
        $o .= '<h2>' . $tx['sysinfo']['php_version'] . '</h2>' . "\n"
            . '<ul>' . "\n" . '<li>' . phpversion() . '</li>' . "\n"
            . '<li><a href="' . $sn . '?&phpinfo" target="_blank"><b>'
            . $tx['sysinfo']['phpinfo_link'] . '</b></a> &nbsp; '
            . $tx['sysinfo']['phpinfo_hint'] . '</li>' . "\n" . '</ul>' . "\n";

        $o .= '<h2>' . $tx['sysinfo']['helplinks'] . '</h2>' . "\n";
        $o .= <<<HTML
    <ul>
    <li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimple-xh.org/">cmsimple-xh.org &raquo;</a></li>
    <li><a target="_blank" rel="noopener" rel="noreferrer" href="https://wiki.cmsimple-xh.org/">wiki.cmsimple-xh.org &raquo;</a></li>
    <li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimpleforum.com/">cmsimpleforum.com &raquo;</a></li>
    <li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimple-xh.org/?Important-Links">cmsimple-xh.org/?Important-Links &raquo;</a></li>
    <li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimple-xh.org/?Plugin-Repository">cmsimple-xh.org/?Plugin-Repository &raquo;</a></li>
    <li><a target="_blank" rel="noopener" rel="noreferrer" href="https://www.cmsimple-xh.org/?Template-Repository">cmsimple-xh.org/?Template-Repository &raquo;</a></li>
    </ul>

    HTML;

        $stx = $tx['syscheck'];
        $checks = array(
            'phpversion' => '7.4.0',
            'extensions' => array(
                array('intl', false),
                'json',
                'mbstring',
                array('openssl', false),
                'session',
                array('curl', false)
            ),
            'functions' => array(
                array('fsockopen', false)
            ),
            'writable' => array(),
            'other' => array()
        );
        $temp = array(
            'content', 'corestyle', 'images', 'downloads', 'userfiles', 'media'
        );
        foreach ($temp as $i) {
            $checks['writable'][] = $pth['folder'][$i];
        }
        $temp = array('config', 'log', 'debug-log', 'language', 'content', 'template', 'stylesheet');
        foreach ($temp as $i) {
            $checks['writable'][] = $pth['file'][$i];
        }
        $checks['writable'][] = "{$pth['folder']['cmsimple']}.sessionname";
        $checks['writable'] = array_unique($checks['writable']);
        sort($checks['writable']);
        $files = array(
            $pth['file']['config'],
            $pth['file']['content'],
            $pth['file']['template'],
            $pth['file']['log'],
            $pth['file']['debug-log']
        );
        foreach ($files as $file) {
            $checks['other'][] = array(
                $this->isAccessProtected($file), false,
                '<a target="_blank" href="' . $file . '">'
                . sprintf($stx['access_protected'], $file)
                . '</a>'
            );
        }
        if ($tx['locale']['all'] == '') {
            $checks['other'][] = array(true, false, $stx['locale_default']);
        } else {
            $checks['other'][] = array(
                $this->setLocale(LC_ALL, $tx['locale']['all']), false,
                sprintf($stx['locale_available'], $tx['locale']['all'])
            );
        }
        $checks['other'][] = array(
            in_array($temp = $this->defaultTimezone(), timezone_identifiers_list()) && $temp !== 'UTC',
            false, $stx['timezone']
        );
        $checks['other'][] = array(
            !$this->getIni('safe_mode'), false, $stx['safe_mode']
        );
        $checks['other'][] = array(
            !$this->getIni('session.use_trans_sid'), false, $stx['use_trans_sid']
        );
        $checks['other'][] = array(
            $this->getIni('session.use_only_cookies'), false, $stx['use_only_cookies']
        );
        $checks['other'][] = array(
            $this->getIni('session.cookie_lifetime') == 0, false, $stx['cookie_lifetime']
        );
        $checks['other'][] = array(
            strpos(ob_get_contents(), "\xEF\xBB\xBF") !== 0,
            false, $stx['bom']
        );
        $checks['other'][] = array(
            !password_verify('test', $cf['security']['password']),
            false, $stx['password']
        );
        $o .= $this->systemCheck($checks);
        return $o;
    }

    public function systemCheck(array $data): string
    {
        global $tx;

        $stx = $tx['syscheck'];

        $o = "<h2>$stx[title]</h2>\n<ul id=\"xh_system_check\">\n";

        if (key_exists('phpversion', $data)) {
            $ok = version_compare(PHP_VERSION, $data['phpversion']) >= 0;
            $o .=  $this->listItem('', $ok ? 'success' : 'fail', sprintf($stx['phpversion'], $data['phpversion']));
        }

        if (key_exists('extensions', $data)) {
            $cat = 'xh_system_check_cat_start';
            foreach ($data['extensions'] as $ext) {
                if (is_array($ext)) {
                    $notok = $ext[1] ? 'fail' : 'warning';
                    $ext = $ext[0];
                } else {
                    $notok = 'fail';
                }
                $o .=  $this->listItem(
                    $cat,
                    $this->isExtensionLoaded($ext) ? 'success' : $notok,
                    sprintf($stx['extension'], $ext)
                );
                $cat = '';
            }
        }

        if (key_exists('functions', $data)) {
            $cat = 'xh_system_check_cat_start';
            foreach ($data['functions'] as $func) {
                if (is_array($func)) {
                    $notok = $func[1] ? 'fail' : 'warning';
                    $func = $func[0];
                } else {
                    $notok = 'fail';
                }
                $o .=  $this->listItem(
                    $cat,
                    function_exists($func) ? 'success' : $notok,
                    sprintf($stx['function'], $func)
                );
                $cat = '';
            }
        }

        if (key_exists('writable', $data)) {
            $cat = 'xh_system_check_cat_start';
            foreach ($data['writable'] as $file) {
                if (is_array($file)) {
                    $notok = $file[1] ? 'fail' : 'warning';
                    $file = $file[0];
                } else {
                    $notok = 'warning';
                }
                $o .=  $this->listItem($cat, is_writable($file) ? 'success' : $notok, sprintf($stx['writable'], $file));
                $cat = '';
            }
        }

        if (key_exists('other', $data)) {
            $cat = 'xh_system_check_cat_start';
            foreach ($data['other'] as $check) {
                $notok = $check[1] ? 'fail' : 'warning';
                $o .= $this->listItem($cat, $check[0] ? 'success' : $notok, $check[2]);
                $cat = '';
            }
        }

        $o .= "</ul>\n";

        return $o;
    }

    public function listItem(string $class, string $state, string $text): string
    {
        global $tx;

        $class = "class=\"xh_$state $class\"";
        return "<li $class>"
            . sprintf($tx['syscheck']['message'], $text, $tx['syscheck'][$state])
            . "</li>\n";
    }

    /** @return list<string> */
    protected function plugins(): array
    {
        return XH_plugins();
    }

    protected function isAccessProtected(string $path): bool
    {
        return XH_isAccessProtected($path);
    }

    protected function isExtensionLoaded(string $extension): bool
    {
        return extension_loaded($extension);
    }

    protected function getIni(string $setting): string
    {
        return ini_get($setting);
    }

    protected function defaultTimezone(): string
    {
        return date_default_timezone_get();
    }

    /** @return string|false */
    protected function setLocale(int $category, string $locale)
    {
        return setlocale($category, $locale);
    }
}
